<?php

namespace App\Http\Controllers\MasterApp;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function index()
    {
        return view('masterapp.settings'); // Blade that includes Livewire
    }
}
