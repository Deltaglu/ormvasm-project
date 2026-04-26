<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $setting = Setting::current();

        return view('settings.edit', compact('setting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'monthly_penalty_rate' => ['required', 'numeric', 'min:0'],
            'one_time_penalty_rate' => ['required', 'numeric', 'min:0'],
        ]);

        Setting::current()->update($data);

        return redirect()->route('settings.edit')->with('status', 'Paramètres enregistrés.');
    }
}