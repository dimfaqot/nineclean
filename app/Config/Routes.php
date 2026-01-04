<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Landing::index');
$routes->post('/auth', 'Landing::auth');
$routes->get('/logout', 'Landing::logout');

$routes->get('/home', 'Home::index');
$routes->post('/home/check_db', 'Home::check_db');
$routes->post('/home/change_db', 'Home::change_db');
$routes->post('/home/encode_jwt', 'Home::encode_jwt');
$routes->get('/menu', 'Menu::index');
$routes->get('/settings', 'Settings::index');
$routes->get('/user', 'User::index');
$routes->get('/options', 'Options::index');
$routes->get('/barang', 'Barang::index');
$routes->get('/profile', 'Profile::index');
$routes->get('/pengeluaran', 'Pengeluaran::index');
$routes->get('/inv', 'Inv::index');
$routes->get('/transaksi', 'Transaksi::index');
$routes->get('/hutang', 'Hutang::index');
$routes->get('/games', 'Games::index');
