<?php

namespace PNS\Admin\Extensions\Settings\Controllers;

use Illuminate\Http\Request;
use PNS\Admin\Layout\Content;
use PNS\Admin\Extensions\Settings\Models\Setting;
use PNS\Admin\Extensions\Settings\Models\SettingGroup;
use Illuminate\Routing\Controller;

class SettingUiController extends Controller
{
    public function index() {
        $groups = SettingGroup::with('settings')->get();
        return view('laravel-admin-settings::index', compact('groups'));
    }

    public function save(Request $request) {
        dd($request->all());
    }
}