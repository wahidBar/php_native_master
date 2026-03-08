<?php

class HomeController extends BaseController
{
    public function index()
    {
        $this->render('home/index', [], 'home');
    }

    public function about()
    {
        $this->render('home/about', [], 'home');
    }
}
