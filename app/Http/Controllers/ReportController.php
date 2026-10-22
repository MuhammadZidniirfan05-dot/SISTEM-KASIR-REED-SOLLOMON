<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index()
    {
        return view('report.index');
    }

    public function filter(Request $request): View
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date'
        ]);

        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        // Ambil data transaksi dengan relasi bertingkat
        $transactions = Transaction::with(['customer', 'details.product'])
            ->whereBetween('date', [$startDate, $endDate])
            ->oldest()
            ->get();

        return view('report.index', compact('transactions', 'startDate', 'endDate'));
    }

    public function print($startDate, $endDate)
    {   
        $transactions = Transaction::with(['customer', 'details.product'])
            ->whereBetween('date', [$startDate, $endDate])
            ->oldest()
            ->get();

        $pdf = Pdf::loadview('report.print', compact('transactions', 'startDate', 'endDate'))
                  ->setPaper('a4', 'landscape');

        return $pdf->stream("Laporan_TokoDuaPutra_{$startDate}.pdf");
    }
}