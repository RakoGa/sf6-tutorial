<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TodoController extends AbstractController
{
    #[Route('/todo', name: 'todo')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        // Afficher tableau de todo
        // si tableau dans ma session, je ne fais que l'afficher
        // sinon je l'initialise puis l'affiche 
        if (!$session->has('todos')) {
            $todos = [
                'achat' => 'acheter une clé usb',
                'cours' => 'finaliser mon cours',
                'correction' => 'corriger mes examens'
            ];
            $session->set('todos', $todos);
        }
        return $this->render('todo/index.html.twig');
    }

    #[Route('todo/{name}/{content}', name: 'todo.add')]
    public function addTodo(Request $request, $name, $content) {
        // Vérifier si le tableau de todo est dans la session
        
    }
}
