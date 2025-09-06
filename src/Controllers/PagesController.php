<?php

namespace App\Controllers;

class PagesController {
    public function home() {
        return view('index', ['title' => 'Welcome']);
    }

    public function about() {
        return view('about', ['title' => 'About Us']);
    }
}
