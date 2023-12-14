<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Member;
use App\Models\Purchase;
use App\Models\Expenditure;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $category = Category::count();
        $product = Product::count();
        $supplier = Supplier::count();
        $member = Member::count();
        $sales = Sale::sum('accepted');
        $expenditure = Expenditure::sum('nominal');
        $purchase = Purchase::sum('pay');

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-d');

        $date_data = array();
        $income_data = array();

        while (strtotime($start_date) <= strtotime($end_date)) {
            $date_data[] = (int) substr($start_date, 8, 2);

            $total_sales = Sale::where('created_at', 'LIKE', "%$start_date%")->sum('pay');
            $total_purchases = Purchase::where('created_at', 'LIKE', "%$start_date%")->sum('pay');
            $total_expenses = Expenditure::where('created_at', 'LIKE', "%$start_date%")->sum('nominal');

            $income = $total_sales - $total_purchases - $total_expenses;
            $income_data[] += $income;

            $start_date = date('Y-m-d', strtotime("+1 day", strtotime($start_date)));
        }

        $start_date = date('Y-m-01');

        if (auth()->user()->level == 1) {
            return view('admin.dashboard', compact('category', 'product', 'supplier', 'member', 'sales', 'expenditure', 'purchase', 'start_date', 'end_date', 'date_data', 'income_data'));
        } else {
            return view('cashier.dashboard');
        }
    }
}
// visit "codeastro" for more projects!