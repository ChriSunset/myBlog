<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    public function __construct(EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->manager = $manager;
        $this->passwordHasher = $passwordHasher;
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
            $emptyPassword = $form->get('password')->getData();

            if ($emptyPassword == null) {
                // recuperer le mdp de l'user en bdd et le renvoyer
                $user->setPassword($user->getPassword());
            } else {
                $passwordEncod = $this->passwordHash->hashPassword($user, $emptyPassword);
                $user->setPassword($passwordEncod);
            }
            // Si le formulaire est soumis et validé , alors...
            // On va hacher le mdp de l'utilisateur 
            // $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
            // On persiste l'utilisateur
            $this->manager->persist($user);
            // On flush
            $this->manager->flush();
            // On retourne sur la page d'accueil
            return $this->redirectToRoute('app_home');
        }


        return $this->render('register/index.html.twig', [
            // On passe le formulaire à la vue
            'myForm'  => $form->createView(),
        ]);
    }
}
