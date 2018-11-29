<?php


namespace Coff\Hellfire\Bootstrap;


use Pimple\Container;

abstract class Bootstrap implements BootstrapInterface
{
    /** @var Container  */
    protected $container;

    public function __construct(Container $container = null)
    {
        if (null === $container) {
            $this->container = new Container();
        }
    }

    public function getContainer() : Container {
        return $this->container;
    }
}