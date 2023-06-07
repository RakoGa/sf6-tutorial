<?php

namespace App\Service;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;

class Helpers {
    private $langue;

    public function __construct(private LoggerInterface $logger, private Security $security) {
    }

    public function sayCoucou() {
        $this->logger->info('alo');
        return "coucou";
    }

    public function getUser(): User {
        return $this->security->getUser();
    }
}