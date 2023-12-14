<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SalesDetails;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use PDF;

class SalesController extends Controller
{
    public function index()
    {
        return view('sales.index');
    }

    public function data()
    {
        $sales = Sale::with('member')->orderBy('sales_id', 'desc')->get();

        return datatables()
            ->of($sales)
            ->addIndexColumn()
            ->addColumn('total_item', function ($sales) {
                return format_uang($sales->total_item);
            })
            ->addColumn('total_price', function ($sales) {
                return 'TZs '. format_uang($sales->total_price);
            })
            ->addColumn('pay', function ($sales) {
                return 'TZs '. format_uang($sales->pay);
            })
            ->addColumn('date', function ($sales) {
                return tanggal_indonesia($sales->created_at, false);
            })
            ->addColumn('member_code', function ($sales) {
                $member = $sales->member->member_code ?? '';
                return '<span class="label label-success">'. $member .'</spa>';
            })
            ->editColumn('discount', function ($sales) {
                return $sales->discount . '%';
            })
            ->editColumn('cashier', function ($sales) {
                return $sales->user->name ?? '';
            })
            ->addColumn('action', function ($sales) {
                return '
                <div class="btn-group">
                    <button onclick="showDetail(`'. route('sales.show', $sales->sales_id) .'`)" class="btn btn-xs btn-primary btn-flat"><i class="fa fa-eye"></i></button>
                    <button onclick="deleteData(`'. route('sales.destroy', $sales->sales_id) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['action', 'member_code'])
            ->make(true);
    }
    // visit "codeastro" for more projects!
    public function create()
    {
        $sales = new Sale();
        $sales->member_id = null;
        $sales->total_item = 0;
        $sales->total_price = 0;
        $sales->discount = 0;
        $sales->pay = 0;
        $sales->accepted = 0;
        $sales->user_id = auth()->id();
        $sales->save();

        session(['sales_id' => $sales->sales_id]);
        return redirect()->route('transaction.index');
    }

    public function store(Request $request)
    {
        $sales::findOrFail($request->sales_id);
        $sales->member_id = $request->member_id;
        $sales->total_item = $request->total_item;
        $sales->total_price = $request->total;
        $sales->discount = $request->discount;
        $sales->pay = $request->pay;
        $sales->accepted = $request->accepted;
        $sales->update();

        $detail = SalesDetails::where('sales_id', $sales->sales_id)->get();
        foreach ($detail as $item) {
            $item->discount = $request->discount;
            $item->update();

            $product = Product::find($item->product_id);
            $product->stock -= $item->amount;
            $product->update();
        }

        return redirect()->route('transaction.finished');
    }

    public function show($id)
    {
        $detail = SalesDetails::with('product')->where('sales_id', $id)->get();

        return datatables()
            ->of($detail)
            ->addIndexColumn()
            ->addColumn('product_code', function ($detail) {
                return '<span class="label label-success">'. $detail->product->product_code .'</span>';
            })
            ->addColumn('product_name', function ($detail) {
                return $detail->product->product_name;
            })
            ->addColumn('selling_price', function ($detail) {
                return 'TZs '. format_uang($detail->selling_price);
            })
            ->addColumn('amount', function ($detail) {
                return format_uang($detail->amount);
            })
            ->addColumn('subtotal', function ($detail) {
                return 'TZs '. format_uang($detail->subtotal);
            })
            ->rawColumns(['product_code'])
            ->make(true);
    }
    // visit "codeastro" for more projects!
    public function destroy($id)
    {
        $sales = Sale::find($id); // Fetch the sale by ID
        if (!$sales) {
            return response()->json(['message' => 'Sale not found'], 404);
        }
    
        $detail = SalesDetails::where('sales_id', $sales->sales_id)->get();
    
        foreach ($detail as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->stock += $item->amount;
                $product->save(); // Use save() to update the product
            }
    
            $item->delete();
        }
    
        $sales->delete();
    

        return response(null, 204);
    }

    public function finished()
    {
        $setting = Setting::first();

        return view('sales.finished', compact('setting'));
    }

    public function smallnote()
    {
        $setting = Setting::first();
        $sales = Sale::find(session('sales_id'));
        if (! $sales) {
            abort(404);
        }
        $detail = SalesDetails::with('product')
            ->where('sales_id', session('sales_id'))
            ->get();
        
        return view('sales.small_note', compact('setting', 'sales', 'detail'));
    }

    public function noteBig()
    {
        $setting = Setting::first();
        $sales = Sale::find(session('sales_id'));
        if (! $sales) {
            abort(404);
        }
        $detail = SalesDetails::with('product')
            ->where('sales_id', session('sales_id'))
            ->get();

        $pdf = PDF::loadView('sales.big_note', compact('setting', 'sales', 'detail'));
        $pdf->setPaper(0,0,609,440, 'potrait');
        return $pdf->stream('Transaction-'. date('Y-m-d-his') .'.pdf');
    }
}
// visit "codeastro" for more projects!