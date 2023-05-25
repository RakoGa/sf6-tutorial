<?php

namespace App\Controller;

use App\Entity\Personne;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Comment\Doc;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// préfixer toutes les routes avec /personne
#[Route('personne')]
class PersonneController extends AbstractController
{
    #[Route('/', name: 'personne.list')]
    public function index(ManagerRegistry $doctrine): Response {
        $repository = $doctrine->getRepository(Personne::class);
        $personnes = $repository->findAll();
        return $this->render('personne/index.html.twig', [
            'personnes' => $personnes
        ]);
    }

    #[Route('/all/{page<\d+>?1}/{nbElem<\d+>?12}', name: 'personne.list.all')]
    public function indexAll(ManagerRegistry $doctrine, $page, $nbElem): Response {
        $repository = $doctrine->getRepository(Personne::class);
        $nbPersonne = $repository->count([]);
        $nbPage = ceil($nbPersonne / $nbElem);
        $personnes = $repository->findBy([], [], limit: $nbElem, offset: $nbElem * ($page - 1));
        return $this->render('personne/index.html.twig', [
            'personnes' => $personnes,
            'isPaginated' => true,
            'nbPage' => $nbPage,
            'page' => $page,
            'nbElem' => $nbElem
        ]);
    }

    #[Route('/{id<\d+>}', name: 'personne.detail')]
    // public function detail(ManagerRegistry $doctrine, $id): Response {
    //     $repository = $doctrine->getRepository(Personne::class);
    //     $personne = $repository->find($id);
    //  ou sinon
    public function detail(Personne $personne = null): Response {
        if (!$personne) {
            $this->addFlash('error', "La personne n'existe pas.");
            return $this->redirectToRoute('personne.list');
        }
        return $this->render('personne/detail.html.twig', [
            'personne' => $personne
        ]);
    }

    #[Route('/add', name: 'personne.add')]
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
        // $entityManager->persist($personne);
        // $entityManager->persist($personne2);

        // Exécute la transaction
        $entityManager->flush();
        return $this->render('personne/detail.html.twig', [
            'personne' => $personne
        ]);
    }

    #[Route('/delete/{id<\d+>}', name: 'personne.delete')]
    public function deletePersonne(Personne $personne = null, ManagerRegistry $doctrine): RedirectResponse {
        if ($personne) {
            $manager = $doctrine->getManager();
            // Ajoute la fonction de suppression dans la transaction
            $manager->remove($personne);
            // Exécuter la suppression
            $manager->flush();
            $this->addFlash('success', 'La personne a été supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Personne inexistante.');
        }

        return $this->redirectToRoute('personne.list.all');
    }
}
