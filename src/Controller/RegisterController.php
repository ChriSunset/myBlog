<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegisterController extends AbstractController
{
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function index(Request $request): Response
    {
        $user = new User(); // Nouvelle instance de User
        $form = $this->createForm(RegisterType::class, $user); // Création du formulaire à partir d'une classe issu du fichier RegisterType.php
        $form->handleRequest($request); // Traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Si le formulaire est soumis et validé , alors...
            $this->manager->persist($user); // On persiste l'utilisateur
            $this->manager->flush(); // On flush
        }


        return $this->render('register/index.html.twig', [

            'myForm'  => $form->createView(), // On passe le formulaire à la vue
        ]);
    }
}
