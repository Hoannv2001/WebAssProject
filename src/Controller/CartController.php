<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Order;
use App\Entity\OrderItems;
use App\Repository\BookRepository;
use App\Repository\OrderItemsRepository;
use App\Repository\OrderRepository;
use Exception;
use phpDocumentor\Reflection\Types\This;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @Route("/addCart/{id}", name="app_add_cart", methods={"GET"})
     */
    public function addCart(Book $book,BookRepository $bookRepository, Request $request, LoggerInterface $logger)
    {
        $session = $request->getSession();
        $quantity = (int)$request->query->get('quantity');
        $cat = $request->query->get('category');
        $idBook = (int)$request->query->get('idBook');
        $page = (int)$request->query->get('pageWeb');
        $temQuery = $bookRepository->findAll();
        $pageSize = 4;
        $totalItems = count($temQuery);
        $logger->info("id: ".$idBook);
        $numOfPages = ceil($totalItems/$pageSize);
        if (!$session->has('cartElements')) {
            $cartElements = array($book->getId() => $quantity);
            $session->set('cartElements', $cartElements);
        } else {
            $cartElements = $session->get('cartElements');
            $cartElements = array($book->getId() => $quantity) + $cartElements;
        }
        $session->set('cartElements', $cartElements);
        $c = count($cartElements);
        $session->set('count', $c);
        return $this->redirectToRoute('app_book_index', [
            'count'=>$c
        ]);
    }
    /**
     * @Route("/reviewCart", name="app_review_cart", methods={"GET"})
     */
    public function reviewCart(Request $request, BookRepository $bookRepository, LoggerInterface $logger): Response
    {
        $idBook= (int)$request->query->get('idBook');
        $quantity = (int)$request->query->get('quantity');
        $logger->info($quantity);

        $logger->info("id:".$idBook);
        $temQuery=$bookRepository->findAll();
        $session = $request->getSession();
        if ($session->has('cartElements')) {
            $cartElements = $session->get('cartElements');
        } else
            $cartElements = [];
        return $this->render('cart/index.html.twig', [
            'bookInfos'=>$temQuery,
            'quantity'=>$cartElements,
        ]);
    }
    /**
     * @Route("/removeCart", name="app_remove_cart", methods={"GET"})
     */
    public function removeCar(Request $request, BookRepository $bookRepository, LoggerInterface $logger)
    {
        $session = $request->getSession();
        $idBook= $request->query->get('id');
        $logger->info($idBook);
        if ($session->has('cartElements')) {
            $cartElements = $session->get('cartElements');

            unset($cartElements[$idBook]);
            $cartElements = $session->set('cartElements', $cartElements);
        }
        return $this->redirectToRoute('app_review_cart',[],Response::HTTP_SEE_OTHER);
    }
    /**
     * @Route("/checkoutCart", name="app_checkout_cart", methods={"GET"})
     */
    public function checkoutCart(Request               $request,
                                 OrderItemsRepository $orderItemsRepository,
                                 OrderRepository       $orderRepository,
                                 BookRepository     $bookRepository,
                                 LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $session = $request->getSession(); //get a session
        if ($session->has('cartElements') && !empty($session->get('cartElements'))) {
            try {
                $cartElements = $session->get('cartElements');

                $order = new Order();
                date_default_timezone_set('Asia/Ho_Chi_Minh');
                $order->setDateOrder(new \DateTime());
                $user = $this->getUser();
                $order->setCustomer($user);
                $total = 0;
                foreach ($cartElements as $book_id => $quantity) {
                    $book = $bookRepository->find($book_id);
                    //create each Order Detail
                    $orderItem = new OrderItems();
                    $orderItem->setOrderB($order);
                    $orderItem->setBook($book);
                    $orderItem->setQuantity($quantity);
                    $orderItemsRepository->add($orderItem);

                    $total += $book->getPrice() * $quantity;
                }
                $order->setTotalPaymet($total);
                $orderRepository->add($order,true);
                $session->remove('cartElements');
//                $logger->info($user);
            } catch (Exception $e) {

            }
            return $this->redirectToRoute('app_review_cart',[],Response::HTTP_SEE_OTHER);
        } else
            return $this->redirectToRoute('app_book_index',[],Response::HTTP_SEE_OTHER);
    }

}
