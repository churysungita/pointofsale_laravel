<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Sale;
use App\Models\SalesDetails;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class SalesDetailController extends Controller
{
    public function index()
    {
        $product = Product::orderBy('product_name')->get();
        $member = Member::orderBy('name')->get();
        $discount = Setting::first()->discount ?? 0;

        // Check whether there are any transactions in progress
        if ($sales_id = session('sales_id')) {
            $sales = Sale::find($sales_id);
            $memberSelected = $sales->member ?? new Member();

            return view('sales_detail.index', compact('product', 'member', 'discount', 'sales_id', 'sales', 'memberSelected'));
        } else {
            if (auth()->user()->level == 1) {
                return redirect()->route('transaction.new');
            } else {
                return redirect()->route('home');
            }
        }
    }

    public function data($id)
    {
        $detail = SalesDetails::with('product')
            ->where('sales_id', $id)
            ->get();

        $data = array();
        $total = 0;
        $total_item = 0;

        foreach ($detail as $item) {
            $row = array();
            $row['product_code'] = '<span class="label label-success">'. $item->product['product_code'] .'</span';
            $row['product_name'] = $item->product['product_name'];
            $row['selling_price']  = '$ '. format_uang($item->selling_price);
            $row['amount']      = '<input type="number" class="form-control input-sm quantity" data-id="'. $item->sales_detail_id .'" value="'. $item->amount .'">';
            $row['discount']      = $item->discount . '%';
            $row['subtotal']    = '$ '. format_uang($item->subtotal);
            $row['action']        = '<div class="btn-group">
                                    <button onclick="deleteData(`'. route('transaction.destroy', $item->sales_detail_id) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                                </div>';
            $data[] = $row;

            $total += $item->selling_price * $item->amount - (($item->discount * $item->amount) / 100 * $item->selling_price);;
            $total_item += $item->amount;
        }
        $data[] = [
            'product_code' => '
                <div class="total hide">'. $total .'</div>
                <div class="total_item hide">'. $total_item .'</div>',
            'product_name' => '',
            'selling_price'  => '',
            'amount'      => '',
            'discount'      => '',
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

        $detail = new SalesDetails();
        $detail->sales_id = $request->sales_id;
        $detail->product_id = $product->product_id;
        $detail->selling_price = $product->selling_price;
        $detail->amount = 1;
        $detail->discount = $product->discount;
        $detail->subtotal = $product->selling_price - ($product->discount / 100 * $product->selling_price);;
        $detail->save();

        return response()->json('Data saved successfully', 200);
    }
    // visit "codeastro" for more projects!
    public function update(Request $request, $id)
    {
        $detail = SalesDetails::find($id);
        $detail->amount = $request->amount;
        $detail->subtotal = $detail->selling_price * $request->amount - (($detail->discount * $request->amount) / 100 * $detail->selling_price);;
        $detail->update();
    }

    public function destroy($id)
    {
        $detail = SalesDetails::find($id);
        $detail->delete();

        return response(null, 204);
    }

    public function loadForm($discount = 0, $total = 0, $accepted = 0)
    {
        $pay   = $total - ($discount / 100 * $total);
        $return = ($accepted != 0) ? $accepted - $pay : 0;
        $data    = [
            'totalrp' => format_uang($total),
            'pay' => $pay,
            'payrp' => format_uang($pay),
            'spelled_out' => ucwords(spelled_out($pay). ' TZs'),
            'returnrp' => format_uang($return),
            'return_spelled_out' => ucwords(spelled_out($return). ' TZs'),
        ];

        return response()->json($data);
    }
}
// visit "codeastro" for more projects!