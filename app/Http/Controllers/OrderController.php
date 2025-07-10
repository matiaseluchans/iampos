<?php

namespace App\Http\Controllers;

use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Order;


use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;


class OrderController extends Controller
{
    private $repository;

    public function __construct(OrderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        return $this->repository->all();
    }

    public function show($id)
    {
        return $this->repository->get($id);
    }

    public function store(Request $request)
    {
        return $this->repository->save($request);
    }

    public function update(Request $request, $id)
    {
        return $this->repository->update($request, $id);
    }

    public function destroy($id)
    {
        return $this->repository->delete($id);
    }

    public function changestatus(Request $request, $id)
    {
        return $this->repository->changestatus($request, $id);
    }

    public function generateInvoice($id)
    {

        $order = Order::with([
            'customer',
            'items',
            //'paymentMethod',
            //'shippingStatus'
        ])->findOrFail($id);

        $pdf = PDF::loadView('invoices.order', [
            'order' => $order,
            'date' => now()->format('d/m/Y'),
            'logo' => public_path('logo.png')
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        // Agregar headers explícitos
        return $pdf->stream("factura_{$order->order_number}.pdf", [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="factura_' . $order->order_number . '.pdf"'
        ]);
    }

    public function generateInvoice2($id)
    {
        $order = Order::with([
            'customer',
            'items',
            //'shippingStatus'
        ])->findOrFail($id);

        // Configuración de mPDF
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            //'format' => [100, 148], // Tamaño pequeño (como media hoja A5)
            'margin_left' => 8,
            'margin_right' => 100,
            'margin_top' => 8,
            'margin_bottom' => 8,
            /*'fontDir' => array_merge($fontDirs, [
                base_path('public/fonts'), // Si necesitas fuentes adicionales
            ]),
            'fontdata' => $fontData + [
                'inter' => [
                    'R' => 'Inter-Regular.ttf',
                    'B' => 'Inter-Bold.ttf',
                ]
            ],
            'default_font' => 'inter',*/
            'tempDir' => storage_path('app/mpdf/temp'), // Directorio temporal
        ]);

        // Vista de la factura
        $html = view('invoices.order2', [
            'order' => $order,
            'date' => now()->format('d/m/Y'),
            'logo' => public_path('logo.png')
        ])->render();

        // Añadir CSS directamente (opcional)
        //$stylesheet = file_get_contents(public_path('css/invoice.css'));
        //$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

        $mpdf->WriteHTML($html);

        // Generar PDF
        $mpdf->Output("factura_{$order->order_number}.pdf", \Mpdf\Output\Destination::INLINE);
    }
}
