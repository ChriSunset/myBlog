<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    // Methode avec le constructeur
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    // Enlever le home apres le slash '/'
    /**
     * @package App\Controller
     * @Route("/", name="app_home")
     */
    public function index(): Response
    {
        // logique stocker dans une variable avec tous les articles

        $articles = null;
        // Méthode findBy qui permet de récupérer les données avec des critères de filtre et de tri
        $articles = $this->manager->getRepository(Article::class)->findAll([], ['publication' => 'desc']);
        // dd($articles);
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'articles' => $articles,
        ]);
    }
}
