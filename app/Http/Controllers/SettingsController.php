<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = Setting::query()->pluck('value', 'key');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:120'],
            'company_logo_url' => ['nullable', 'url', 'max:2048'],
            'company_logo_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'theme_color' => ['required', 'in:emerald,blue,violet'],
        ]);

        if ($request->hasFile('company_logo_file')) {
            $path = $request->file('company_logo_file')->store('logos', 'public');
            $data['company_logo_url'] = asset('storage/'.$path);
        }

        unset($data['company_logo_file']);

        foreach ($data as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        AuditLog::log('settings_updated', 'settings', null, null, [
            'company_name' => $data['company_name'],
            'has_logo' => ! empty($data['company_logo_url']),
        ]);

        return back()->with('status', 'Configuración guardada correctamente.');
    }
}
