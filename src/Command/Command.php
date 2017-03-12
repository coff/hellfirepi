<?php

namespace Coff\Hellfire\Command;

use Coff\Hellfire\Application\HellfireApplication;
use Pimple\Container;

/**
 * Command
 *
 * Basic Hellfire Command with Pimple container attached.
 */
abstract class Command extends \Symfony\Component\Console\Command\Command
{
    protected $logFilename = 'hellfire-common.log';

    /**
     * Returns pimple DI container
     *
     * @return Container
     */
    public function getContainer() {
        /** @var HellfireApplication $app */
        $app = $this->getApplication();

        return $app->getContainer();
    }

    public function getLogFilename() {
        return $this->logFilename;
    }
}
