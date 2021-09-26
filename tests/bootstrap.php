<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use app\Main;

$application = new Application('cleaner', '1.0.0');

$application->add(new Main());

return $application;
