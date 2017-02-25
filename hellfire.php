<?php

namespace Coff\Hellfire;

use Coff\Hellfire\Command\CreateStorageCommand;
use Coff\Hellfire\Command\HellfireServerCommand;
use Coff\Hellfire\Command\W1ServerCommand;
use Symfony\Component\Console\Application;

include (__DIR__ . 'app/bootstrap.php');

$app = new Application();

$app
    ->add(new CreateStorageCommand())
    ->add(new HellfireServerCommand())
    ->add(new W1ServerCommand())
    ;


