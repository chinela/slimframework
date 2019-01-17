<?php

namespace App\Controllers;

use Slim\Views\Twig as View;

class HomeController extends Controller {
    
    public function index($resquest, $response) {
        
        return $this->view->render($response, 'home.twig');
    }
}