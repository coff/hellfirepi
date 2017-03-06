<?php
namespace Coff\Hellfire\CommonTrait;

use Symfony\Component\EventDispatcher\EventDispatcher;

trait EventDispatcherTrait
{
    protected $eventDispatcher;

    /**
     * @param EventDispatcher $eventDispatcher
     * @return $this
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher) {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher() {
        return $this->eventDispatcher;
    }
}
