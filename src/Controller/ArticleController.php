<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ArticleController extends AbstractController
{
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("/article", name="app_article")
     */
    public function index(Request $request, SluggerInterface $sluger): Response
    {
        $article = new Article(); // Nouvelle instance de Article
        $form = $this->createForm(ArticleType::class, $article); // Création du formulaire à partir d'une classe issu du fichier ArticleType.php
        $form->handleRequest($request); // Traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Si le formulaire est soumis et validé , alors...
            // On recupere dans une variable $photoArticle le contenu de l'image stocké 
            $photoArticle = $form->get('photo')->getData();

            if ($photoArticle) {
                $originalFilename = pathinfo($photoArticle->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $sluger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoArticle->guessExtension();
                try {
                    $photoArticle->move(
                        $this->getParameter('photo'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $article->setPhoto($newFilename);
            } else {
                dd('Aucune photo disponible');
            }
            $article->setPublication(new \datetime);
            $article->setAuteur($this->getUser()->getNomComplet());
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

    /**
     * @Route("/article/edit/{id}", name="app_article_edit")
     */
    public function articleEdit(Article $articleModif, SluggerInterface $sluger, Request $request): Response
    {
        $formModif = $this->createForm(ArticleType::class, $articleModif);
        $formModif->handleRequest($request); // Traitement du formulaire
        if ($formModif->isSubmitted() && $formModif->isValid()) {
            // Si le formulaire est soumis et validé , alors...

            $photoArticle = $formModif->get('photo')->getData();

            if ($photoArticle) {
                $originalFilename = pathinfo($photoArticle->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $sluger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoArticle->guessExtension();
                try {
                    $photoArticle->move(
                        $this->getParameter('photo'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $articleModif->setPhoto($newFilename);
            } else {
                dd('Aucune photo disponible');
            }
            $articleModif->setPublication(new \datetime);
            $this->manager->persist($articleModif); // On persiste l'article
            $this->manager->flush(); // On flush
            return $this->redirectToRoute('app_home');
        }
        return $this->render('article/editArticle.html.twig', [
            // Matérialise l'affichage du formulaire
            'myEditArticle' => $formModif->createView(),
        ]);
    }

    // Recherche et trouve tous les articles

    /**
     * @Route("/all/article", name="app_all_article")
     */
    public function allArticle(): Response
    {
        $articles = $this->manager->getRepository(Article::class)->findAll();
        // Verifier que la variable a bien recu les données cherchés dans la BDD
        // dd($articles);
        return $this->render('article/allArticles.html.twig', [
            // Matérialise l'affichage du formulaire
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/single/article/{id}", name="app_view_article")
     */
    public function singleArticle(Article $article, Request $requestCom): Response
    {
        // La ligne de code ci-dessous n'est pas nécessaire
        // $article = $this->manager->getRepository(Article::class)->find($id);

        // Créer le formulaire de commentaire et sa logique
        // Afficher le formulaire dans la MODAL
        // Lors de la soumission , envoyer en base de donnée le commentaire avec les infos suivants:
        // -> Commentaire
        // -> Auteur
        // -> Article
        // -> Date

        $comment = new Comment(); // Nouvelle instance de Commentaire
        $formComment = $this->createForm(CommentType::class, $comment); // Création du formulaire à partir d'une classe issu du fichier CommentType.php
        $formComment->handleRequest($requestCom); // Traitement du formulaire
        if ($formComment->isSubmitted() && $formComment->isValid()) {
            // Si le formulaire est soumis et validé , alors...
            $comment->setAuteur($this->getUser());
            $comment->setArticle($article);
            $comment->setDate(new \DateTime());
            $this->manager->persist($comment); // On persiste le commentaire
            $this->manager->flush(); // On flush
            return $this->redirectToRoute('app_view_article', ['id' => $article->getId()]);
        }

        return $this->render('article/singleArticle.html.twig', [
            'article' => $article,
            'commentaire' => $formComment->createView(),
        ]);
    }
}
