<?php

namespace App\Event;

use App\Entity\Personne;
use Symfony\Contracts\EventDispatcher\Event;

class ListAllPersonnesEvent extends Event {
    const LIST_ALL_PERSONNES_EVENT = 'personne.list.all';

    public function __construct(private int $nbPers) {}
    public function getNbPersonne(): int {
        return $this->nbPers;
    }
    
}