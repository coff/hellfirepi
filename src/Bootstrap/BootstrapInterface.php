<?php


namespace Coff\Hellfire\Bootstrap;


use Pimple\Container;

interface BootstrapInterface
{
    public function init();
    public function getContainer() : Container;
}