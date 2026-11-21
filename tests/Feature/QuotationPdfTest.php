<?php

namespace Tests\Feature;

use App\Http\Controllers\QuotationController;
use App\Models\Quotation;
use Barryvdh\DomPDF\PDF;
use DOMElement;
use Dompdf\Canvas;
use Dompdf\Frame;
use DOMText;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Tests\TestCase;

class QuotationPdfTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            '2025_08_13_132042_create_quotation_table.php',
            '2025_08_21_145636_create_quotation_term_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }

        Schema::table('quotation', function (Blueprint $table) {
            $table->text('approved_qr')->nullable();
            $table->timestamp('approved_at')->nullable();
        });

        Storage::fake('public');
    }

    public static function quotationCases(): array
    {
        return [
            'approval on a continuation page' => [5, false, true],
            'short unapproved quotation' => [1, false, false],
            'long address reference and terms' => [5, true, true],
            'many items' => [55, false, true],
        ];
    }

    #[DataProvider('quotationCases')]
    public function test_information_repeats_without_overlapping_content_or_approval(
        int $itemCount,
        bool $longDetails,
        bool $approved,
    ): void {
        $address = 'Komplek Citra Buana Centre Park 1, Jl. Yos Sudarso Blok A No.1-3, Kp. Pelita, Lubuk Baja, Batam, Kepulauan Riau 29432. ';
        $quotation = Quotation::create([
            'quo_no' => 'Q.070/GPT/11-26',
            'date' => '2026-11-21',
            'customer_name' => 'PT Customer',
            'customer_address' => ($longDetails ? str_repeat($address, 3) : $address).'ADDRESS-END',
            'attention' => '-',
            'your_reference' => str_repeat('RINA RULES Pt D, Ch 5, Sch 4 & AWS D1.1 ', $longDetails ? 3 : 1).'REFERENCE-END',
            'terms' => str_repeat('Payment before project starts and balance after completion. ', $longDetails ? 3 : 1).'TERMS-END',
            'total_amount' => $itemCount * 1000,
            'sub_total' => $itemCount * 1000,
        ]);
        $quotation->items()->createMany(array_map(fn ($index) => [
            'description' => 'Calibration inspection and testing service '.$index,
            'qty' => 1,
            'unit_price' => 1000,
            'total_price' => 1000,
        ], range(1, $itemCount)));
        $quotation->scopes()->createMany(array_map(fn ($index) => [
            'description' => 'Scope of work '.$index,
            'responsible_pt_gpt' => true,
            'responsible_client' => false,
        ], range(1, 6)));
        $quotation->terms()->createMany([
            ['description' => 'Payment 50% before the project starts.'],
            ['description' => 'Quotation excludes tax.'],
            ['description' => 'Quotation is valid for 10 days.'],
        ]);

        if ($approved) {
            Storage::disk('public')->put('approval.svg', (string) QrCode::format('svg')
                ->size(100)->generate('https://example.test/approval'));
            $quotation->forceFill(['approved_qr' => 'approval.svg', 'approved_at' => now()])->save();
        }

        $pages = [];
        $this->app->resolving('dompdf.wrapper', function (PDF $pdf) use (&$pages): void {
            $pdf->setCallbacks([[
                'event' => 'end_frame',
                'f' => function (Frame $frame, Canvas $canvas) use (&$pages): void {
                    $this->captureLayout($frame, $canvas, $pages);
                },
            ]]);
        });

        $response = app(QuotationController::class)->exportPdf($quotation->id);

        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertNotEmpty($pages);

        if ($itemCount > 1) {
            $this->assertGreaterThan(1, count($pages));
        }

        foreach ($pages as $number => $page) {
            $this->assertSame(1, $page['header_count'], 'Missing header on page '.$number);
            foreach (['PT Customer', 'Q.070/GPT/11-26', 'ADDRESS-END', 'REFERENCE-END', 'TERMS-END'] as $text) {
                $this->assertStringContainsString($text, $page['header_text'], 'Incomplete header on page '.$number);
            }
            $this->assertGreaterThan($page['header_bottom'], $page['body_top'], 'Content overlaps header on page '.$number);
            $this->assertLessThan($page['footer_top'], $page['body_bottom'], 'Content overlaps footer on page '.$number);
        }

        $this->assertSame(1, array_sum(array_column($pages, 'approval_count')));
        $this->assertSame($approved ? 1 : 0, array_sum(array_column($pages, 'qr_count')));
    }

    private function captureLayout(Frame $frame, Canvas $canvas, array &$pages): void
    {
        $number = $canvas->get_page_number();
        $pages[$number] ??= [
            'header_count' => 0, 'header_bottom' => 0, 'header_text' => '',
            'body_top' => INF, 'body_bottom' => 0, 'footer_top' => INF,
            'approval_count' => 0, 'qr_count' => 0,
        ];
        $page = &$pages[$number];
        $node = $frame->get_node();

        if ($node instanceof DOMElement) {
            switch ($node->getAttribute('class')) {
                case 'header-page':
                    $page['header_count']++;
                    $page['header_bottom'] = $frame->get_position('y') + $frame->get_margin_height();
                    break;
                case 'footer':
                    $page['footer_top'] = $frame->get_position('y');
                    break;
                case 'approval-block':
                    $page['approval_count']++;
                    break;
            }

            if ($node->nodeName === 'img' && $node->getAttribute('alt') === 'Approval QR Code') {
                $page['qr_count']++;
            }
        }

        if (! $node instanceof DOMText || trim($node->data) === '') {
            return;
        }

        for ($ancestor = $node->parentNode; $ancestor instanceof DOMElement; $ancestor = $ancestor->parentNode) {
            if ($ancestor->getAttribute('class') === 'header-page') {
                $page['header_text'] .= $node->data.' ';

                return;
            }
            if ($ancestor->getAttribute('class') === 'footer') {
                return;
            }
        }

        $page['body_top'] = min($page['body_top'], $frame->get_position('y'));
        $page['body_bottom'] = max($page['body_bottom'], $frame->get_position('y') + $frame->get_margin_height());
    }
}
