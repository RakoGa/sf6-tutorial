<?php

namespace App\Service;
use Psr\Log\LoggerInterface;

class Helpers {
    private $langue;

    public function __construct(private LoggerInterface $logger) {
    }

    public function sayCoucou() {
        $this->logger->info('alo');
        return "coucou";
    }
}