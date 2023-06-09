<?php

namespace App\Controller;

use App\Entity\Personne;
use App\Event\AddPersonneEvent;
use App\Event\ListAllPersonnesEvent;
use Doctrine\Persistence\ManagerRegistry;
use Faker\Provider\ar_JO\Person;
use PhpParser\Comment\Doc;
use App\Form\PersonneType;
use App\Service\Helpers;
use App\Service\MailerService;
use App\Service\PdfService;
use App\Service\UploaderService;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// préfixer toutes les routes avec /personne
#[
    Route('personne'),
    IsGranted('ROLE_USER')
]
class PersonneController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger, 
        private Helpers $helper,
        private EventDispatcherInterface $dispatcher) {
    }

    #[Route('/', name: 'personne.list')]
    public function index(ManagerRegistry $doctrine): Response {
        $repository = $doctrine->getRepository(Personne::class);
        $personnes = $repository->findAll();
        return $this->render('personne/index.html.twig', [
            'personnes' => $personnes
        ]);
    }

    #[Route('/pdf/{id<\d+>}', name: 'personne.pdf')]
    public function generatePdfPersonne(Personne $personne = null, PdfService $pdf) {
        $html = $this->render('personne/detail.html.twig', ['personne' => $personne]);
        $pdf->showPdfFile($html);
    }

    #[Route('/all/age/{ageMin<\d+>}/{ageMax<\d+>}', name: 'personne.list.age')]
    public function personnesByAge(ManagerRegistry $doctrine, $ageMin, $ageMax): Response {
        $repository = $doctrine->getRepository(Personne::class);
        $personnes = $repository->findPersonnesByAgeInterval($ageMin, $ageMax);
        return $this->render('personne/index.html.twig', [
            'personnes' => $personnes
        ]);
    }

    #[Route('/stats/age/{ageMin<\d+>}/{ageMax<\d+>}', name: 'personne.list.stats')]
    public function statsPersonnesByAge(ManagerRegistry $doctrine, $ageMin, $ageMax): Response {
        $repository = $doctrine->getRepository(Personne::class);
        $stats = $repository->statsPersonnesByAgeInterval($ageMin, $ageMax);
        return $this->render('personne/stats.html.twig', [
            'stats' => $stats[0],
            'ageMin' => $ageMin,
            'ageMax' => $ageMax
        ]);
    }

    #[
        Route('/all/{page<\d+>?1}/{nbElem<\d+>?12}', name: 'personne.list.all'),
        IsGranted("ROLE_USER")
    ]
    public function indexAll(ManagerRegistry $doctrine, $page, $nbElem): Response {
        // echo ($this->helper->sayCoucou());
        $repository = $doctrine->getRepository(Personne::class);
        $nbPersonne = $repository->count([]);
        $nbPage = ceil($nbPersonne / $nbElem);
        $personnes = $repository->findBy([], [], limit: $nbElem, offset: $nbElem * ($page - 1));
        $listAllPersonneEvent = new ListAllPersonnesEvent(count($personnes));
        $this->dispatcher->dispatch($listAllPersonneEvent, ListAllPersonnesEvent::LIST_ALL_PERSONNES_EVENT);
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

    #[Route('/edit/{id<\d+>?0}', name: 'personne.edit')]
    public function addPersonne(
        Personne $personne = null,
        ManagerRegistry $doctrine,
        Request $request, 
        UploaderService $uploaderService,
        MailerService $mailer): Response
    {
        // $entityManager = $doctrine->getManager();
        // $personne = new Personne();
        // $personne->setFirstname('camille');
        // $personne->setName('fort');
        // $personne->setAge('24');

        // $personne2 = new Personne();
        // $personne2->setFirstname('leelah');
        // $personne2->setName('fort');
        // $personne2->setAge('24');

        // Ajouter l'opération d'insertion de la personne dans la transaction
        // $entityManager->persist($personne);
        // $entityManager->persist($personne2);

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $new = false;
        if (!$personne) {
            $new = true;
            $personne = new Personne();
        }
        // $personne est l'image de notre formulaire
        $form = $this->createForm(PersonneType::class, $personne);

        // Exécute la transaction
        // $entityManager->flush();

        $form->remove('createdAt');
        $form->remove('updatedAt');

        // Le formulaire va aller traiter la requête
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // https://symfony.com/doc/current/controller/upload_file.html
            $photo = $form->get('photo')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($photo) {
                $dir = $this->getParameter('personne_directory');
                $personne->setImage($uploaderService->uploadFile($photo, $dir));
            }

            if ($new) {
                $message = " a été ajouté avec succès.";
                $personne->setCreatedBy($this->getUser());
            } else {
                $message = " a été mis à jour avec succès.";
            }

            $manager = $doctrine->getManager();
            $manager->persist($personne);
            $manager->flush();

            if ($new) {
                // Création de l'évènement
                $addPersonneEvent = new AddPersonneEvent($personne);
                $this->dispatcher->dispatch($addPersonneEvent, AddPersonneEvent::ADD_PERSONNE_EVENT);
            }
            $this->addFlash('success', $personne->getFirstname(). " " .$personne->getName(). $message);
            return $this->redirectToRoute('personne.list');
        } else {
            return $this->render('personne/add-personne.html.twig', [
                'form' => $form->createView()
            ]);
        }
    }

    #[
        Route('/delete/{id<\d+>}', name: 'personne.delete'),
        IsGranted('ROLE_ADMIN')
    ]
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

    #[Route('/update/{id<\d+>}/{name}/{firstname}/{age}', name: 'personne.update')]
    public function updatePersonne(Personne $personne = null, ManagerRegistry $doctrine, $name, $firstname, $age): Response {
        if ($personne) {
            $personne->setName($name);
            $personne->setFirstname($firstname);
            $personne->setAge($age);
            $manager = $doctrine->getManager();
            $manager->persist($personne);

            $manager->flush();
            $this->addFlash('success', 'La personne a été mise à jour avec succès.');
        } else {
            $this->addFlash('error', 'Personne inexistante.');
        }

        return $this->redirectToRoute('personne.list.all');

    }
}
