<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/order')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'app_order_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('order/index.html.twig', [
            'orders' => $orderRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_order_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EquipmentRepository $equipmentRepository): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $totalPrice = 0;

            foreach ($order->getOrderItems() as $orderItem) {
                $orderItem->setOrder($order);

                $equipment = $orderItem->getEquipmentRelation();
                if ($equipment) {
                    $orderItem->setPrice($equipment->getRentalPrice() * $orderItem->getQuantity());
                    $totalPrice += $orderItem->getPrice();
                    
                    $newQuantityAvailable = $equipment->getQuantityAvailable() - $orderItem->getQuantity();
                    if ($newQuantityAvailable < 0) {
                        $this->addFlash('error', 'Not enough equipment available.');
                        return $this->render('order/new.html.twig', [
                            'form' => $form->createView(),
                        ]);
                    }
                    $equipment->setQuantityAvailable($newQuantityAvailable);
                }
            }

            $order->setTotalPrice($totalPrice);

            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('order/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $totalPrice = 0;

            foreach ($order->getOrderItems() as $orderItem) {
                $orderItem->setOrder($order);

                $equipment = $orderItem->getEquipmentRelation();
                if ($equipment) {
                    $orderItem->setPrice($equipment->getRentalPrice() * $orderItem->getQuantity());
                    $totalPrice += $orderItem->getPrice();
                }
            }

            $order->setTotalPrice($totalPrice);

            $entityManager->flush();

            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('order/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_order_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }
}
