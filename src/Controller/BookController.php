<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\User;
use App\Form\BookType;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/book")
 */
class BookController extends AbstractController
{
    /**
     * @Route("/list/{page}", name="app_book_index", methods={"GET"})
     */
    public function index( Request $request, BookRepository $bookRepository,
                           AuthorRepository $authorRepository,
                           LoggerInterface $logger,
                           int $page = 1 ): Response
    {
        $quantity = (int)$request->query->get('_token');
        $minPrice = $request->query->get('minPrice');
        $maxPrice = $request->query->get('maxPrice');
        $cat = $request->query->get('category');
        $author = $request->query->get('authorN');

        $user=$this->getUser();
        if(!(is_null($cat)||empty($cat))){
            $selectedCat=$cat;
        }else
            $selectedCat='';

        $user = $this->getUser();

        $temQuery = $bookRepository->findAllPriceRange($minPrice, $maxPrice, $cat,$author);
        $pageSize = 4;
        $paginator = new Paginator($temQuery);
        $totalItems = count($paginator);
        $numOfPages = ceil($totalItems/$pageSize);

        $tempQuery = $paginator
            ->getQuery()
            ->setFirstResult($pageSize * ($page-1)) // set the offset
            ->setMaxResults($pageSize);
        $temQuery1 = $bookRepository->selectDataBookAdmin($user);

        $hasAccess = $this->isGranted('ROLE_SELLER');
        $hasAccessCus = $this->isGranted('ROLE_CUSTOMER');
        $hasAccessAdmin = $this->isGranted('ROLE_ADMIN');

        $logger->info("id :".$author);
        $findAuthor = $authorRepository->findBookByAuthor($author);
//        $findAuthor->getResult();

        if ($hasAccessAdmin){
            return $this->render('book/index.html.twig', [
                'books'=>$bookRepository->findAll(),
            ]);

        }
        if ($hasAccess) {
            return $this->render('book/index.html.twig', [
                'books' => $temQuery1->getResult(),
            ]);
        }
        if ($hasAccessCus){
            return $this->render('book/bookCus.html.twig',[
                'books' => $tempQuery->getResult(),
                'selectedCat' => $selectedCat,
                'numOfPages' => $numOfPages,
                'totalItem' => $totalItems,
                'authors'=>$authorRepository->findAll()
            ]);

        }
        else {
            return $this->render('book/bookCus.html.twig',[
                'books' => $tempQuery->getResult(),
                'selectedCat' => $selectedCat,
                'numOfPages' => $numOfPages,
                'totalItem' => $totalItems,
                'authors'=>$authorRepository->findAll()
            ]);
        }
    }


    /**
     * @Route("/new", name="app_book_new", methods={"GET", "POST"})
     */
    public function new(Request $request, BookRepository $bookRepository, LoggerInterface $logger, SluggerInterface $slugger): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        $user = $this->getUser();
        $bookRepository->add($book->setUser($user));
        $hasAccess = $this->isGranted('ROLE_SELLER');
        $hasAccessAdmin = $this->isGranted('ROLE_ADMIN');
        if (is_null($user)){
            return $this->redirectToRoute('app_login',[],Response::HTTP_SEE_OTHER);
        }
        elseif($form->isSubmitted() && $form->isValid() ) {
            $bookImage = $form->get('Image')->getData();
            if ($bookImage){
                $originExt = pathinfo($bookImage->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename  = $slugger->slug($originExt);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$bookImage->guessExtension();
                try {
                    $bookImage->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $book->setImage($newFilename);
            }
            $bookRepository->add($book, true);

            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }
        if($hasAccess||$hasAccessAdmin){
            return $this->renderForm('book/new.html.twig', [
                'book' => $book,
                'form' => $form,
            ]);
        }else{
            return new Response("No Access");
        }
    }

    /**
     * @Route("/{id}/show", name="app_book_show", methods={"GET"})
     */
    public function show(Book $book): Response
    {
        $user = $this->getUser();
        if (is_null($user)){
            return $this->redirectToRoute('app_login',[],Response::HTTP_SEE_OTHER);
        }
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_book_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Book $book, BookRepository $bookRepository): Response
    {
        $hasAccessAdmin = $this->isGranted('ROLE_ADMIN');
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        $user = $this->getUser();
        $hasAccess = $this->isGranted('ROLE_SELLER');
        $hasAccessAdmin = $this->isGranted('ROLE_ADMIN');
        if (is_null($user)){
            return $this->redirectToRoute('app_login',[],Response::HTTP_SEE_OTHER);
        }
//        if ($user !== $book->getUser()){
//            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
//        }
        elseif ($form->isSubmitted() && $form->isValid()) {
            $bookRepository->add($book, true);
            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        if ( $hasAccess||$hasAccessAdmin){
            return $this->renderForm('book/edit.html.twig', [
                'book' => $book,
                'form' => $form,
            ]);
        }else{
            return new Response("No Access");
        }

    }
    /**
     * @Route("/{id}", name="app_book_delete", methods={"POST"})
     */
    public function delete(Request $request, Book $book, BookRepository $bookRepository): Response
    {
        $user = $this->getUser();
        if (is_null($user)){
            return $this->redirectToRoute('app_login',[],Response::HTTP_SEE_OTHER);
        }
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->request->get('_token'))) {
            $bookRepository->remove($book, true);
        }
        return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
    }
}
