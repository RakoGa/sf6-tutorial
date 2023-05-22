<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FirstController extends AbstractController
{

    
    // #[Route('{maVar}', name: 'test.order.route')]
    // public function testOrderRoute($maVar): Response {
    //     return new Response($maVar);
    // }

    // route générique qui apparaît en priorité
    #[Route('/order/{maVar}', name: 'test.order.route')]
    public function testOrderRoute($maVar): Response {
        return new Response("<html><body>$maVar</body></html>");
    }

    #[Route('/first', name: 'first')]
    public function index(): Response
    {
        return $this->render('first/index.html.twig', [
            'name' => 'rako',
            'firstname' => 'ga'
        ]);
    }

    // #[Route('/sayHello/{name}/{firstname}', name: 'say.hello')]
    public function sayHello(Request $request, $name, $firstname): Response
    {
        return $this->render('first/hello.html.twig', [
            'name' => $name,
            'firstname' => $firstname
        ]);
    }

    // ou 'multi/{entier1<\d+>}/{entier2<\d+>}'
    #[Route(
        'multi/{entier1}/{entier2}',
        name: 'multiplication',
        requirements: ['entier1' => '\d+', 'entier2' => '\d+']
    )]
    public function multiplication($entier1, $entier2): Response {
        $resultat = $entier1 * $entier2;
        return new Response("<h1>$resultat</h1>");
    }
}
