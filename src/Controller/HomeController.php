<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="app_home")
     */
    public function index(BookRepository $bookRepository): Response
    {
        $temQuery = $bookRepository->countOfBook();
        $paginator = new Paginator($temQuery);
        $totalItems = count($paginator);
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'countOfBook' =>$totalItems
        ]);
    }
}
