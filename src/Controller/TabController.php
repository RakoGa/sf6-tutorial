<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TabController extends AbstractController
{
    #[Route('/tab/{nbNotes<\d+>?5}', name: 'tab')]
    public function index($nbNotes): Response {
        $notes = [];
        for ($i = 0; $i < $nbNotes; $i++) {
            $notes[] = rand(0, 20);
        }
        return $this->render('tab/index.html.twig', [
            'notes' => $notes,
        ]);
    }

    #[Route('/tab/users', name: 'tab.users')]
    public function users(): Response {
        $users = [
            ['firstname' => 'ga', 'name' => 'rako', 'age' => '24'],
            ['firstname' => 'leelah', 'name' => 'fort', 'age' => '23'],
            ['firstname' => 'may', 'name' => 'slay', 'age' => '24'],
        ];
        
        return $this->render('tab/users.html.twig', [
            'users' => $users
        ]);
    }

}
