<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }
    /**
     * @Route("/article", name="app_article")
     */
    public function index(Request $request): Response
    {
        $article = new Article(); // Nouvelle instance de Article
        $form = $this->createForm(ArticleType::class, $article); // Création du formulaire à partir d'une classe issu du fichier ArticleType.php
        $form->handleRequest($request); // Traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Si le formulaire est soumis et validé , alors...
            $this->manager->persist($article); // On persiste l'article
            $this->manager->flush(); // On flush
        }
        return $this->render('article/index.html.twig', [
            // Matérialise l'affichage du formulaire
            'myArticle' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/delete/{id}", name="app_article_delete")
     */
    public function articleDelete(Article $article, Request $request): Response
    {
        $this->manager->remove($article); // On efface l'article
        $this->manager->flush(); // On flush

        return $this->redirectToRoute('app_home');
    }
}
