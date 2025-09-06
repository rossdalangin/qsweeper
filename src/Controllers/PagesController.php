<?php

namespace App\Controllers;

/**
 * Handles basic, static pages like the home page and about page.
 */
class PagesController extends Controller {
    /**
     * Displays the home page.
     * @return mixed
     */
    public function home() {
        return view('index', ['title' => 'Welcome']);
    }

    /**
     * Displays the about page.
     * @return mixed
     */
    public function about() {
        return view('about', ['title' => 'About Us']);
    }

    /**
     * Displays the instructions page.
     * @return mixed
     */
    public function instructions() {
        return view('instructions', ['title' => 'How to Play']);
    }
}
