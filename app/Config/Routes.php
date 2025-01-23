<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// HOMEPAGE
$routes->get('/', 'Homepage::index');
$routes->post('/auth/sales/signin', 'Auth::signin_sales');
   
