<?php

namespace App\Http\Controllers\Sales;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController
{
    /**
     * Sample invoice rows (UI-only data for now).
     */
    private function invoiceRows(): \Illuminate\Support\Collection
    {
        return collect([
            [
                'date' => '2026-01-30',
                'number' => 'INV-00105',
                'customer' => 'Kumudzi Centre',
                'status' => 'Unpaid',
                'amount' => 980000,
                'due' => '2026-02-15',
            ],
            [
                'date' => '2026-01-10',
                'number' => 'INV-00104',
                'customer' => 'Nyasa Tech',
                'status' => 'Paid',
                'amount' => 250000,
                'due' => '2026-01-25',
            ],
        ]);
    }

    /**
     * Show list of invoices
     */
    public function index(Request $request)
    {
        $rows = $this->invoiceRows();

        return view('components.sales.invoices.index', compact('rows'));
    }

    /**
     * Export currently listed invoices to PDF.
     */
    public function exportPdf(Request $request)
    {
        $rows = $this->invoiceRows();

        $pdf = Pdf::loadView('components.sales.invoices.pdf', [
            'rows' => $rows,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $filename = 'invoices-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Show invoice creation form
     */
    public function create(Request $request)
    {
        // Sample customers for dropdown
        $customers = ['Kumudzi Centre', 'Nyasa Tech', 'ABC Company', 'XYZ Solutions'];

        // Sample items for quick pick
        $items = [
            ['name' => 'Professional Services', 'rate' => 0],
            ['name' => 'Consulting', 'rate' => 0],
            ['name' => 'Development', 'rate' => 0],
        ];

        return view('components.sales.invoices.create', compact('customers', 'items'));
    }

    /**
     * Store invoice
     */
    public function store(Request $request)
    {
        // For now, just validate and redirect back
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'currency' => 'required|string|in:MWK,USD,ZAR,EUR,GBP',
            'vat_rate' => 'required|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
        ]);

        return redirect()
            ->route('sales.invoices.index')
            ->with('success', 'Invoice created successfully!');
    }

    /**
     * Download invoice as PDF
     */
    public function downloadPdf($id)
    {
        // For now, just redirect back
        return redirect()
            ->route('sales.invoices.index')
            ->with('info', 'PDF download coming soon');
    }

    /**
     * Send invoice via email
     */
    public function send(Request $request)
    {
        // For now, just return success
        return response()->json([
            'success' => true,
            'message' => 'Invoice sent successfully (frontend only)',
        ]);
    }
}
