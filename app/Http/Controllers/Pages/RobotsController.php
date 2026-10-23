<?php

namespace App\Http\Controllers\Pages;
use App\Http\Controllers\Controller;

class RobotsController extends Controller
{
    public function index()
    {
        $content = view('robots')->render();

        return response($content)
            ->header('Content-Type', 'text/plain');
    }
}
