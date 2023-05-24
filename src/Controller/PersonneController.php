<?php

namespace App\Controller;

use App\Entity\Personne;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonneController extends AbstractController
{
    #[Route('/personne/add', name: 'personne')]
    public function addPersonne(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $personne = new Personne();
        $personne->setFirstname('ga');
        $personne->setName('rako');
        $personne->setAge('24');

        $personne2 = new Personne();
        $personne2->setFirstname('leelah');
        $personne2->setName('fort');
        $personne2->setAge('24');

        // Ajouter l'opéraation d'insertion de la personne dans la transaction
        $entityManager->persist($personne);
        $entityManager->persist($personne2);

        // Exécute la transaction
        $entityManager->flush();
        return $this->render('personne/detail.html.twig', [
            'personne' => $personne
        ]);
    }
}
