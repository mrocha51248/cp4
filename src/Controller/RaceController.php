<?php

namespace App\Controller;

use App\Repository\RaceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/race', name: 'race_')]
class RaceController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(RaceRepository $raceRepository): Response
    {
        return $this->render('race/index.html.twig', [
            'races' => $raceRepository->findRecentFinished(20),
        ]);
    }
}
