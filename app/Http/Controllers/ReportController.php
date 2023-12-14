<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Expenditure;
use App\Models\Sale;
use Illuminate\Http\Request;
use PDF;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $endDate = date('Y-m-d');

        if ($request->has('start_date') && $request->start_date != "" && $request->has('end_date') && $request->end_date) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
        }

        return view('report.index', compact('startDate', 'endDate'));
    }

    public function getData($start, $end)
    {
        $no = 1;
        $data = array();
        $income = 0;
        $total_income = 0;

        while (strtotime($start) <= strtotime($end)) {
            $date = $start;
            $start = date('Y-m-d', strtotime("+1 day", strtotime($start)));

            $total_sales = Sale::where('created_at', 'LIKE', "%$date%")->sum('bayar');
            $total_purchases = Purchase::where('created_at', 'LIKE', "%$date%")->sum('bayar');
            $total_expenses = Expenditure::where('created_at', 'LIKE', "%$date%")->sum('nominal');

            $income = $total_sales - $total_purchases - $total_expenses;
            $total_income += $income;

            $row = array();
            $row['DT_RowIndex'] = $no++;
            $row['date'] = tanzanian_date($date, false);
            $row['sales'] = format_uang($total_sales);
            $row['purchase'] = format_uang($total_purchases);
            $row['expenditure'] = format_uang($total_expenses);
            $row['income'] = format_uang($income);

            $data[] = $row;
        }
        // visit "codeastro" for more projects!
        $data[] = [
            'DT_RowIndex' => '',
            'date' => '',
            'sales' => '',
            'purchase' => '',
            'expenditure' => 'Total Income',
            'income' => format_uang($total_income),
        ];

        return $data;
    }

    public function data($start, $end)
    {
        $data = $this->getData($start, $end);

        return datatables()
            ->of($data)
            ->make(true);
    }

    public function exportPDF($start, $end)
    {
        $data = $this->getData($start, $end);
        $pdf  = PDF::loadView('report.pdf', compact('start', 'end', 'data'));
        $pdf->setPaper('a4', 'potrait');
        
        return $pdf->stream('Income-reports-'. date('Y-m-d-his') .'.pdf');
    }
}
