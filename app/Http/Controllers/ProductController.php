<?php

namespace App\Http\Controllers;

use App\Models\Category;

use App\Models\Product;
use Illuminate\Http\Request;

use PDF;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $category = Category::all()->pluck('category_name', 'category_id');

        return view('product.index', compact('category'));
    }

    public function data()
    {
        $product = Product::leftJoin('category', 'category.category_id', 'product.category_id')
            ->select('product.*', 'category_name')
            // ->orderBy('product_code', 'asc')
            ->get();

        return datatables()
            ->of($product)
            ->addIndexColumn()
            ->addColumn('select_all', function ($product) {
                return '
                    <input type="checkbox" name="product_id[]" value="'. $product->product_id .'">
                ';
            })
            ->addColumn('product_code', function ($product) {
                return '<span class="label label-success">'. $product->product_code .'</span>';
            })
            ->addColumn('purchase_price', function ($product) {
                return format_uang($product->purchase_price);
            })
            ->addColumn('selling_price', function ($product) {
                return format_uang($product->selling_price);
            })
            ->addColumn('stock', function ($product) {
                return format_uang($product->stock);
            })
            ->addColumn('action', function ($product) {
                return '
                <div class="btn-group">
                    <button type="button" onclick="editForm(`'. route('product.update', $product->product_id) .'`)" class="btn btn-xs btn-primary btn-flat"><i class="fa fa-pencil"></i></button>
                    <button type="button" onclick="deleteData(`'. route('product.destroy', $product->product_id) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['action', 'product_code', 'select_all'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $product = Product::latest()->first() ?? new Product();
        $request['product_code'] = 'P' . str_pad((int) $product->product_id + 1, 6, '0', STR_PAD_LEFT);
    
        $product = Product::create($request->all());
    
        return response()->json('Data saved successfully', 200);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::find($id);

        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        $product->update($request->all());

        return response()->json('Data saved successfully', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        $product->delete();

        return response(null, 204);
    }

    public function deleteSelected(Request $request)
    {
        foreach ($request->product_id as $id) {
            $product = Product::find($id);
            $product->delete();
        }

        return response(null, 204);
    }
    // visit "codeastro" for more projects!
    public function printBarcode(Request $request)
    {
        $dataproduct = array();
        foreach ($request->product_id as $id) {
            $product = Product::find($id);
            $dataproduct[] = $product;
        }

        $no  = 1;
        $pdf = PDF::loadView('product.barcode', compact('dataproduct', 'no'));
        $pdf->setPaper('a4', 'potrait');
        return $pdf->stream('product.pdf');
    }
}
