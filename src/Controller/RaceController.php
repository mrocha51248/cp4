<?php

namespace App\Controller;

use App\Entity\Race;
use App\Repository\RaceRepository;
use App\Repository\RaceResultRepository;
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

    #[Route('/{race}', name: 'show')]
    public function show(Race $race, RaceResultRepository $raceResultRepository): Response
    {
        $raceResult = $raceResultRepository->findOneBy([
            'race' => $race,
            'user' => $this->getUser(),
        ]);

        if (!$raceResult && !$race->getFinishedAt()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        return $this->render('race/show.html.twig', [
            'race' => $race,
            'user_result' => $raceResult,
        ]);
    }
}
