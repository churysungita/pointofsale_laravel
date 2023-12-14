<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        return view('setting.index');
    }

    public function show()
    {
        return Setting::first();
    }

    public function update(Request $request)
    {
        $setting = Setting::first();
        $setting->company_name = $request->company_name;
        $setting->telephone = $request->telephone;
        $setting->address = $request->address;
        $setting->discount = $request->discount;
        $setting->note_type = $request->note_type;

        if ($request->hasFile('path_logo')) {
            $file = $request->file('path_logo');
            $name = 'logo-' . date('YmdHis') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('/img'), $name);

            $setting->path_logo = "/img/$name";
        }

        if ($request->hasFile('member_card_path')) {
            $file = $request->file('member_card_path');
            $name = 'logo-' . date('Y-m-dHis') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('/img'), $name);

            $setting->member_card_path = "/img/$name";
        }

        $setting->update();

        return response()->json('Data saved successfully', 200);
    }
}
