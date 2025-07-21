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

    public function generateRemitoComanda($id)
    {


        $order = Order::with([
            'customer',
            'items',
            'payment'
        ])->findOrFail($id);

        // Configuración de mPDF
        $defaultConfig = (new ConfigVariables())->getDefaults();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [120, 297], // 80mm de ancho, alto automático
            'margin_left' => 2,
            'margin_right' => 2,
            'margin_top' => 5,
            'margin_bottom' => 5,
            'margin_header' => 2,
            'margin_footer' => 2,
            'default_font_size' => 8,
            'default_font' => 'Arial',
            'orientation' => 'P'
        ]);

        // Para maximizar compatibilidad con impresoras térmicas
        $mpdf->showImageErrors = true;
        $mpdf->simpleTables = true;
        $mpdf->packTableData = true;

        // Vista de la factura
        $html = view('invoices.remitoComanda', [
            'order' => $order,
            'date' => now()->format('d/m/Y'),
            'logo' => public_path('logo.png')
        ])->render();


        $mpdf->WriteHTML($html);

        // Generar PDF
        $mpdf->Output("remito_{$order->order_number}.pdf", \Mpdf\Output\Destination::INLINE);
    }

    public function generateRemito($id)
    {
        $order = Order::with([
            'customer',
            'items',
            'payment'
        ])->findOrFail($id);

        // Configuración de mPDF
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            //'format' => [150, 297], // Ancho 150mm, alto automático (como A4)
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 2,
            'margin_footer' => 5,
            'default_font_size' => 8,
            'default_font' => 'dejavusans',
            'orientation' => 'P'
        ]);

        // Vista de la factura
        $html = view('invoices.remito', [
            'order' => $order,
            'date' => now()->format('d/m/Y'),
            'logo' => public_path('logo.png')
        ])->render();


        $mpdf->WriteHTML($html);

        // Generar PDF
        $mpdf->Output("remito_{$order->order_number}.pdf", \Mpdf\Output\Destination::INLINE);
    }

    public function generateDeliveryReport(Request $request)
    {
        return $this->repository->generateDeliveryReport($request);
    }

    public function generateCustomerDeliveryReport(Request $request)
    {
        return $this->repository->generateCustomerDeliveryReport($request);
    }

    public function search(Request $request)
    {
        return $this->repository->search($request);
    }
    
    public function latest(Request $request)
    {
        return $this->repository->latest($request);
    }
}
