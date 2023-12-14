<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseDetails;
use App\Models\Product;
use App\Models\Supplier;

class PurchaseController extends Controller
{
    public function index()
    {
        $supplier = Supplier::orderBy('name')->get();

        return view('purchase.index', compact('supplier'));
    }
  
    public function data()
    {
        $purchase = Purchase::orderBy('purchase_id', 'desc')->get();

        return datatables()
            ->of($purchase)
            ->addIndexColumn()
            ->addColumn('total_item', function ($purchase) {
                return format_uang($purchase->total_item);
            })
            ->addColumn('total_price', function ($purchase) {
                return 'TZs '. format_uang($purchase->total_price);
            })
            ->addColumn('pay', function ($purchase) {
                return 'TZs '. format_uang($purchase->pay);
            })
            ->addColumn('date', function ($purchase) {
                return tanggal_indonesia($purchase->created_at, false);
            })
            ->addColumn('supplier', function ($purchase) {
                return $purchase->supplier->name;
            })
            ->editColumn('discount', function ($purchase) {
                return $purchase->discount . '%';
            })
            ->addColumn('action', function ($purchase) {
                return '
                <div class="btn-group">
                    <button onclick="showDetail(`'. route('purchase.show', $purchase->purchase_id) .'`)" class="btn btn-xs btn-primary btn-flat"><i class="fa fa-eye"></i></button>
                    <button onclick="deleteData(`'. route('purchase.destroy', $purchase->purchase_id) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create($id)
    {
        $purchase = new Purchase();
        $purchase->id_supplier = $id;
        $purchase->total_item  = 0;
        $purchase->total_price = 0;
        $purchase->discount      = 0;
        $purchase->pay       = 0;
        $purchase->save();

        session(['purchase_id' => $purchase->purchase_id]);
        session(['id_supplier' => $purchase->id_supplier]);

        return redirect()->route('purchase_details.index');
    }

    public function store(Request $request)
    {
        $purchase = Purchase::findOrFail($request->purchase_id);
        $purchase->total_item = $request->total_item;
        $purchase->total_price = $request->total;
        $purchase->discount = $request->discount;
        $purchase->pay = $request->pay;
        $purchase->update();

        $detail = PurchaseDetails::where('purchase_id', $purchase->purchase_id)->get();
        foreach ($detail as $item) {
            $product = Product::find($item->product_id);
            $product->stock += $item->amount;
            $product->update();
        }

        return redirect()->route('purchase.index');
    }

    public function show($id)
    {
        $detail = PurchaseDetails::with('product')->where('purchase_id', $id)->get();

        return datatables()
            ->of($detail)
            ->addIndexColumn()
            ->addColumn('product_code', function ($detail) {
                return '<span class="label label-success">'. $detail->product->product_code .'</span>';
            })
            ->addColumn('product_name', function ($detail) {
                return $detail->product->product_name;
            })
            ->addColumn('purchase_price', function ($detail) {
                return 'TZs '. format_uang($detail->purchase_price);
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

    public function destroy($id)
    {
        $purchase = Purchase::find($id);
        $detail    = PurchaseDetails::where('purchase_id', $purchase->purchase_id)->get();
        foreach ($detail as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->stock -= $item->amount;
                $product->update();
            }
            $item->delete();
        }

        $purchase->delete();

        return response(null, 204);
    }
}
