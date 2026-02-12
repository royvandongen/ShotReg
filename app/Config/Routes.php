<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Public routes
$routes->get('/', 'AuthController::login');
$routes->get('auth/login', 'AuthController::login');
$routes->post('auth/login', 'AuthController::login');
$routes->get('auth/register', 'AuthController::register');
$routes->post('auth/register', 'AuthController::register');
$routes->get('auth/verify2fa', 'AuthController::verify2fa');
$routes->post('auth/verify2fa', 'AuthController::verify2fa');
$routes->get('auth/logout', 'AuthController::logout');
$routes->post('locale/switch', 'LocaleController::switch');

// Protected routes
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // 2FA setup
    $routes->get('auth/setup2fa', 'AuthController::setup2fa');
    $routes->post('auth/setup2fa', 'AuthController::setup2fa');

    // Weapons catalog
    $routes->get('weapons', 'WeaponController::index');
    $routes->get('weapons/create', 'WeaponController::create');
    $routes->post('weapons/create', 'WeaponController::create');
    $routes->get('weapons/edit/(:num)', 'WeaponController::edit/$1');
    $routes->post('weapons/edit/(:num)', 'WeaponController::edit/$1');
    $routes->post('weapons/delete/(:num)', 'WeaponController::delete/$1');
    $routes->post('weapons/ajax-create', 'WeaponController::ajaxCreate');

    // Shooting sessions
    $routes->get('sessions', 'SessionController::index');
    $routes->get('sessions/create', 'SessionController::create');
    $routes->post('sessions/create', 'SessionController::create');
    $routes->get('sessions/(:num)', 'SessionController::show/$1');
    $routes->get('sessions/edit/(:num)', 'SessionController::edit/$1');
    $routes->post('sessions/edit/(:num)', 'SessionController::edit/$1');
    $routes->post('sessions/delete/(:num)', 'SessionController::delete/$1');
    $routes->post('sessions/delete-photo/(:num)', 'SessionController::deletePhoto/$1');
    $routes->post('sessions/reorder-photos', 'SessionController::reorderPhotos');
    $routes->post('sessions/ajax-create-location', 'SessionController::ajaxCreateLocation');

    // Photo serving
    $routes->get('photos/(:segment)', 'PhotoController::show/$1');
    $routes->get('photos/thumb/(:segment)', 'PhotoController::thumbnail/$1');

    // Profile
    $routes->get('profile', 'ProfileController::index');
    $routes->post('profile/update', 'ProfileController::update');
    $routes->post('profile/change-password', 'ProfileController::changePassword');

    // Settings
    $routes->get('settings', 'SettingsController::index');
    $routes->post('settings/add-location', 'SettingsController::addLocation');
    $routes->post('settings/edit-location/(:num)', 'SettingsController::editLocation/$1');
    $routes->post('settings/delete-location/(:num)', 'SettingsController::deleteLocation/$1');
    $routes->post('settings/add-option', 'SettingsController::addOption');
    $routes->post('settings/delete-option/(:num)', 'SettingsController::deleteOption/$1');
    $routes->post('settings/save-defaults', 'SettingsController::saveDefaults');
});

// Admin routes
$routes->group('admin', ['filter' => 'admin'], static function ($routes) {
    $routes->get('settings', 'AdminController::settings');
    $routes->post('settings', 'AdminController::saveSettings');
    $routes->get('users', 'AdminController::users');
    $routes->post('users/toggle-admin/(:num)', 'AdminController::toggleAdmin/$1');
    $routes->post('defaults/add', 'AdminController::addDefault');
    $routes->post('defaults/delete', 'AdminController::deleteDefault');
});
