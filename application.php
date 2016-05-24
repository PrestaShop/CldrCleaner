<?php

require __DIR__.'/vendor/autoload.php';

use PrestaShop\CldrCleaner\Command\GenerateIsoArrayCommand;
use PrestaShop\CldrCleaner\Command\CleanZipCommand;
use Symfony\Component\Console\Application;

// TODO: Add a name to the application
// TODO: Add a version to the application

$application = new Application();
$application->add(new GenerateIsoArrayCommand());
$application->add(new CleanZipCommand());
$application->run();
