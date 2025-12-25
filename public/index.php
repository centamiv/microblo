<?php

define('MICROBLO_APP', true);

require __DIR__ . '/microblo/Parsedown.php';
require __DIR__ . '/microblo/PostParser.php';
require __DIR__ . '/microblo/Cache.php';
require __DIR__ . '/microblo/Router.php';
require __DIR__ . '/microblo/Microblo.php';
require __DIR__ . '/microblo/helpers.php';

$config = require 'config.php';

$app = Microblo::instance($config);
$app->run();
