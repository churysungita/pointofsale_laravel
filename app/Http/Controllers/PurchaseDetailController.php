<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDetails;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseDetailController extends Controller
{
    public function index()
    {
        $purchase_id = session('purchase_id');
        $product = Product::orderBy('product_name')->get();
        $supplier = Supplier::find(session('id_supplier'));
        $discount = Purchase::find($purchase_id)->discount ?? 0;

        if (! $supplier) {
            abort(404);
        }

        return view('purchase_details.index', compact('purchase_id', 'product', 'supplier', 'discount'));
    }

    public function data($id)
    {
        $detail = PurchaseDetails::with('product')
            ->where('purchase_id', $id)
            ->get();
        $data = array();
        $total = 0;
        $total_item = 0;

        foreach ($detail as $item) {
            $row = array();
            $row['product_code'] = '<span class="label label-success">'. $item->product['product_code'] .'</span';
            $row['product_name'] = $item->product['product_name'];
            $row['purchase_price']  = 'TZs '. format_uang($item->purchase_price);
            $row['amount']      = '<input type="number" class="form-control input-sm quantity" data-id="'. $item->id_purchase_detail .'" value="'. $item->amount .'">';
            $row['subtotal']    = 'TZs '. format_uang($item->subtotal);
            $row['action']        = '<div class="btn-group">
                                    <button onclick="deleteData(`'. route('purchase_details.destroy', $item->id_purchase_detail) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                                </div>';
            $data[] = $row;

            $total += $item->purchase_price * $item->amount;
            $total_item += $item->amount;
        }
        $data[] = [
            'product_code' => '
                <div class="total hide">'. $total .'</div>
                <div class="total_item hide">'. $total_item .'</div>',
            'product_name' => '',
            'purchase_price'  => '',
            'amount'      => '',
            'subtotal'    => '',
            'action'        => '',
        ];

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->rawColumns(['action', 'product_code', 'amount'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $product = Product::where('product_id', $request->product_id)->first();
        if (! $product) {
            return response()->json('Data failed to save', 400);
        }

        $detail = new PurchaseDetails();
        $detail->purchase_id = $request->purchase_id;
        $detail->product_id = $product->product_id;
        $detail->purchase_price = $product->purchase_price;
        $detail->amount = 1;
        $detail->subtotal = $product->purchase_price;
        $detail->save();

        return response()->json('Data saved successfully', 200);
    }
    // visit "codeastro" for more projects!
    public function update(Request $request, $id)
    {
        $detail = PurchaseDetails::find($id);
        $detail->amount = $request->amount;
        $detail->subtotal = $detail->purchase_price * $request->amount;
        $detail->update();
    }

    public function destroy($id)
    {
        $detail = PurchaseDetails::find($id);
        $detail->delete();

        return response(null, 204);
    }

    public function loadForm($discount, $total)
    {
        $pay = $total - ($discount / 100 * $total);
        $data  = [
            'totalrp' => format_uang($total),
            'pay' => $pay,
            'payrp' => format_uang($pay),
            'terbilang' => ucwords(terbilang($pay). ' TZs')
        ];

        return response()->json($data);
    }
}
