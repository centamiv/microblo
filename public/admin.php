<?php

define('MICROBLO_ADMIN', true);

require __DIR__ . '/microblo/Parsedown.php';
require __DIR__ . '/microblo/PostParser.php';
require __DIR__ . '/microblo/Cache.php';
require __DIR__ . '/microblo/Router.php';
require __DIR__ . '/microblo/Microblo.php';
require __DIR__ . '/microblo/helpers.php';
require __DIR__ . '/microblo/Admin.php';

session_start();

$config = require 'config.php';

if (empty($config['admin_user']) || empty($config['admin_pass'])) {
    http_response_code(403);
    die('Admin access is disabled.');
}

$controller = new AdminController($config);
$controller->handleRequest();
