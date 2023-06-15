<?php

namespace App\EventListener;

use App\Event\AddPersonneEvent;
use App\Event\ListAllPersonnesEvent;
use Psr\Log\LoggerInterface;

class PersonneListener {
    public function __construct(private LoggerInterface $logger) {}
    public function onPersonneAdd(AddPersonneEvent $event) {
        $this->logger->debug("event personne.add et une personne ajoutée: ". $event->getPersonne()->getName());
    }

    public function onListAllPersonnes(ListAllPersonnesEvent $event) {
        $this->logger->debug($event->getNbPersonne() ." personnes dans la base");
    }
    public function onListAllPersonnes2(ListAllPersonnesEvent $event) {
        $this->logger->debug("second listener");
    }
}