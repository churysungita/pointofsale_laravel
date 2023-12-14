<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use PDF;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('member.index');
    }

    public function data()
    {
        $member = Member::orderBy('member_code')->get();

        return datatables()
            ->of($member)
            ->addIndexColumn()
            ->addColumn('select_all', function ($product) {
                return '
                    <input type="checkbox" name="member_id[]" value="' . $product->member_id . '">
                ';
            })
            ->addColumn('member_code', function ($member) {
                return '<span class="label label-success">' . $member->member_code . '<span>';
            })
            ->addColumn('action', function ($member) {
                return '
                <div class="btn-group">
                <button type="button" onclick="editForm(`' . route('member.update', $member->member_id) . '`)" class="btn btn-xs btn-primary btn-flat"><i class="fa fa-pencil"></i></button>
                <button type="button" onclick="deleteData(`' . route('member.destroy', $member->member_id) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })

            ->rawColumns(['action', 'select_all', 'member_code'])
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

     public function add_zero_in_front($number, $desired_length) {
        $number_length = strlen($number);
        if ($number_length < $desired_length) {
            $zeros_to_add = $desired_length - $number_length;
            return str_repeat('0', $zeros_to_add) . $number;
        }
        return $number; // Return the number as is if it's already longer than the desired length
    }
    



    public function store(Request $request)
    {
        $latestMember = Member::latest()->first();
        $memberCode = $latestMember ? (int)$latestMember->member_code + 1 : 1;
        $paddedMemberCode = $this->add_zero_in_front($memberCode, 5);
    
        $member = new Member();
        $member->member_code = $paddedMemberCode;
        $member->name = $request->name;
        $member->telephone = $request->telephone; // Corrected 'telepone' typo to 'telephone'
        $member->address = $request->address;
        $member->save();
    
        return response()->json('Data saved successfully', 200);
    }
    
    
    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $member = Member::find($id);

        return response()->json($member);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
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
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $member = Member::find($id)->update($request->all());

        return response()->json('Data saved successfully', 200);
    }

    // visit "codeastro" for more projects!
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $member = Member::find($id);
        $member->delete();

        return response(null, 204);
    }

    public function printMember(Request $request)
    {
        $datamember = collect(array());
        foreach ($request->member_id as $id) {
            $member = Member::find($id);
            $datamember[] = $member;
        }

        $datamember = $datamember->chunk(2);
        $setting    = Setting::first();

        $no  = 1;
        $pdf = PDF::loadView('member.print', compact('datamember', 'no', 'setting'));
        $pdf->setPaper(array(0, 0, 566.93, 850.39), 'potrait');
        return $pdf->stream('member.pdf');
    }
}
