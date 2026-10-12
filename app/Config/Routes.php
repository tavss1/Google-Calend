<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::tentar');
$routes->get('logout', 'Auth::logout');

$routes->get('calendario',          'Calendario::index',    ['filter' => 'auth']);
$routes->get('calendario/feed',     'Calendario::feed',     ['filter' => 'auth']);
$routes->get('calendario/usuarios', 'Calendario::usuarios', ['filter' => ['auth', 'adm']]);
$routes->post('calendario/salvar',  'Calendario::salvar',   ['filter' => ['auth', 'adm']]);
$routes->post('calendario/excluir/(:num)', 'Calendario::excluir/$1', ['filter' => ['auth', 'adm']]);

$routes->get('google-dev/login', 'GoogleAuthDev::login');
$routes->get('google-dev/callback', 'GoogleAuthDev::callback');