<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Governorate;
use Illuminate\Http\Request;

class GovernorateController extends Controller
{
    public function index()
    {
        $governorates = Governorate::orderBy('name')->paginate(15);
        return view('admin.governorates.index', compact('governorates'));
    }

    public function create()
    {
        return view('admin.governorates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        Governorate::create($data);
        return redirect()->route('admin.governorates.index')->with('success', __('global.admin_governorate_created'));
    }

    public function edit(Governorate $governorate)
    {
        return view('admin.governorates.edit', compact('governorate'));
    }

    public function update(Request $request, Governorate $governorate)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $governorate->update($data);
        return redirect()->route('admin.governorates.index')->with('success', __('global.admin_governorate_updated'));
    }

    public function destroy(Governorate $governorate)
    {
        $governorate->delete();
        return redirect()->route('admin.governorates.index')->with('success', __('global.admin_governorate_deleted'));
    }
}
