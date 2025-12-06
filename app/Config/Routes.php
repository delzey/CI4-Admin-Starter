<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index', ['filter' => 'session']);

$routes->group('dashboard', ['filter' => 'session'], static function ($routes) {
    $routes->get('/', 'Dashboard::index', ['as' => 'dashboard.index']);
    $routes->get('widgets', 'Dashboard::widgets');
});

$routes->group('category', ['filter' => 'session'], static function ($routes) {
    $routes->post('create', 'MenuManagement::createCategory');
    $routes->get('list', 'MenuManagement::listCategories');
    $routes->get('get/(:num)', 'MenuManagement::getCategory/$1');
    $routes->post('update', 'MenuManagement::updateCategory');
    $routes->post('delete', 'MenuManagement::deleteCategory');
    $routes->post('reorder', 'MenuManagement::reorderCategory');
});

$routes->group('menu-management', ['filter' => 'session'], static function ($routes) {
    $routes->get('/', 'MenuManagement::index');
    $routes->get('view', 'MenuManagement::view');
    $routes->post('create', 'MenuManagement::create');
    $routes->post('update', 'MenuManagement::update');
    $routes->post('delete', 'MenuManagement::delete');
    $routes->get('debug', 'MenuManagement::debug');
    $routes->post('reorder', 'MenuManagement::reorder');
    $routes->get('tree', 'MenuManagement::tree');
    $routes->get('get/(:num)', 'MenuManagement::get/$1');
});

$routes->group('settings', ['filter' => 'session'], static function ($routes) {
    $routes->get('/', 'Settings::index');
    $routes->get('get', 'Settings::get');
    $routes->post('save', 'Settings::save');
});

$routes->group('auth-permissions', ['filter' => 'session'], static function ($routes) {
    $routes->get('/', 'Permissions::index');
    $routes->get('table/(:num)', 'Permissions::table/$1');
    $routes->post('chgPerm', 'Permissions::chgPerm');
});

$routes->group('users', ['filter' => 'session'], static function ($routes) {
    $routes->get('/',                'Users::index', ['as' => 'users.index']);

    // AJAX – Users
    $routes->get('list',             'Users::listUsers');
    $routes->post('create',          'Users::createUser');
    $routes->post('update',          'Users::updateUser');
    $routes->post('delete',          'Users::deleteUser');

    // AJAX – Groups (from config)
    $routes->get('groups/list',      'Users::listConfigGroups');

    // AJAX – User↔Groups assignment
    $routes->get('user-groups',      'Users::getUserGroups');     // ?id=USER_ID
    $routes->post('user-groups/set', 'Users::setUserGroups');     // id, groups[]=alias
});

$routes->group('auth-groups', ['filter' => 'permission:admin.settings'], static function($routes) {
    $routes->get('/', 'GroupPermissions::index');           // page
    $routes->get('groups', 'GroupPermissions::groups');     // list config groups + merged perms
    $routes->get('permissions', 'GroupPermissions::perms'); // list all defined permissions (grouped)
    $routes->post('save', 'GroupPermissions::save');        // save overrides for a group
});

$routes->group('profile', ['filter' => 'session'], static function ($routes) {
    $routes->get('/', 'ProfileController::index', ['as' => 'profile']);
    $routes->post('update', 'ProfileController::updateProfile');
    $routes->post('change-password', 'ProfileController::changePassword');
});

$routes->group('messages', ['filter' => 'session'], static function($routes) {
    $routes->get('/',           'Messages::index',   ['as' => 'messages.index']);
    $routes->get('inbox',       'Messages::inbox',   ['as' => 'messages.inbox']);
    $routes->get('outbox',      'Messages::outbox',  ['as' => 'messages.outbox']);
    $routes->get('compose',     'Messages::compose', ['as' => 'messages.compose']);
    $routes->post('send',       'Messages::send',    ['as' => 'messages.send']);
    $routes->get('show/(:num)', 'Messages::show/$1', ['as' => 'messages.show']);
    $routes->post('delete/(:num)', 'Messages::delete/$1', ['as' => 'messages.delete']);
    $routes->get('unread-count', 'Messages::unreadCount', ['as' => 'messages.unreadCount']);
    $routes->get('inbox-preview', 'Messages::inboxPreview', ['as' => 'messages.inboxPreview']);

});

$routes->group('bolt', ['filter' => 'session'], static function ($routes) {
    $routes->get('/', 'Bolt::index', ['as' => 'bolt.index']);
    $routes->post('encrypt/(:segment)', 'Bolt::encrypt/$1', ['as' => 'bolt.encrypt']);
});

service('auth')->routes($routes);
