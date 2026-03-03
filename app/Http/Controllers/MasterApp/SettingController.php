<?php

namespace App\Http\Controllers\MasterApp;

use App\Core\User\Services\SettingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function settings(SettingService $service)
    {
        $settings = $service->all();
        return view('masterapp.settings.index', compact('settings'));
    }

    public function store(Request $request, SettingService $service)
    {
        $data = $request->validate([
            'key' => 'required|string|unique:settings,key',
            'value' => 'nullable|string',
        ]);

        $service->create($data);

        return redirect()->back()->with('success', 'Setting created successfully.');
    }

    public function update(Request $request, int $id, SettingService $service)
    {
        $data = $request->validate([
            'key' => 'required|string|unique:settings,key,' . $id,
            'value' => 'nullable|string',
        ]);

        $service->update($id, $data);

        return redirect()->back()->with('success', 'Setting updated successfully.');
    }

    public function destroy(int $id, SettingService $service)
    {
        $service->delete($id);
        return redirect()->back()->with('success', 'Setting deleted successfully.');
    }
}
