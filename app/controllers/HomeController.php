<?php

namespace App\Controllers;

use App\Models\Job;
use Core\Auth;
use Core\Router;
use Core\View;

class HomeController
{
    public function index(): void
    {
        if (Auth::check()) {
            Router::redirect(Auth::dashboardPath());
        }
        $jobs = array_slice(Job::browse(), 0, 6);
        View::render('home', ['jobs' => $jobs]);
    }
}
