<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\PostTrait;

class ToolController extends Controller
{
    use PostTrait;

    public function index(Request $request)
    {
        $config = [
            'title' => __('Tools'),
            'nav' => 'tool',
        ];

        return view('admin.tool.index', compact('config'));
    }

    public function massImporter(Request $request)
    {
        $config = [
            'title' => __('TMDB Mass Importer'),
            'nav' => 'tool',
        ];

        if (!config('settings.tmdb_api') || !config('settings.tmdb_language')) {
            return redirect()->route('admin.tmdb.settings')->with('error', __('Please configure TMDB settings first'));
        }

        return view('admin.tool.mass-importer', compact('config'));
    }

}
