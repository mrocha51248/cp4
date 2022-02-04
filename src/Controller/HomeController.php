<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'home_')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategoryRepository $categoryRepository, GameRepository $gameRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'categories' => $categoryRepository->findMostRaced(4),
            'games' => $gameRepository->findMostRaced(10),
        ]);
    }
}
