<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    // Methode avec le constructeur
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("/user", name="app_user")
     */
    public function index(): Response
    {

        $users = null;
        // Méthode findBy qui permet de récupérer les données avec des critères de filtre et de tri
        $users = $this->manager->getRepository(User::class)->findAll();
        // dd($users);

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'users' => $users,
        ]);
    }

    /**
     * @Route("/user/{id}", name="app_user_myself")
     */

    public function voirSonProfil(): Response
    {
        // Méthode find qui permet de récupérer les données de l'id
        $user = $this->manager->getRepository(User::class)->findAll();
        dd($user);

        return $this->render('user/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/user/delete/{id}", name="app_user_delete")
     */
    public function deleteUser(User $user): Response
    {
        $this->manager->remove($user); // Effacer l'utilisateur
        $this->manager->flush();

        return $this->redirectToRoute('app_user');
    }

    /**
     * @Route("/user/update/{id}", name="app_user_update")
     */
    public function updateUser(User $userUpdate, Request $request): Response
    {
        $formUpdate = $this->createForm(RegisterType::class, $userUpdate);
        $formUpdate->handleRequest($request);

        if ($formUpdate->isSubmitted() && $formUpdate->isValid()) {

            // Récuperer le champ du password
            $emptyPassword = $formUpdate->get('password')->getData();

            // Quand le port lunaire est soumis, vérifier que le champ password, 
            // S'il est vide, alors on recupère le mot de passe à l'utilisateur à modifier et on le renvoi
            if ($emptyPassword == null) {
                // recuperer le mdp de l'user en bdd et le renvoyer
                $userUpdate->setPassword($userUpdate->getPassword());
            }

            $this->manager->persist($userUpdate);
            $this->manager->flush();
            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/updateUser.html.twig', ['updateUser' => $formUpdate->createView(),]);
    }
}
