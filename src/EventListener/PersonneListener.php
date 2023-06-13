<?php

namespace App\EventListener;

use App\Event\AddPersonneEvent;
use Psr\Log\LoggerInterface;

class PersonneListener {
    public function __construct(private LoggerInterface $logger) {}
    public function onPersonneAdd(AddPersonneEvent $event) {
        $this->logger->debug("event personne.add et une personne ajoutée: ". $event->getPersonne()->getName());
    }
}