<?php

namespace Coff\Hellfire\Command;

use Coff\Hellfire\Application\HellfireApplication;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;

class HellfireCommand extends Command
{
    public function getContainer() {
        /** @var HellfireApplication $app */
        $app = $this->getApplication();

        return $app->getContainer();
    }
}
