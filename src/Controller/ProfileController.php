<?php

namespace App\Controller;

use App\Repository\RaceResultRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(RaceResultRepository $raceResultRepository): Response
    {
        $raceResult = $raceResultRepository->findOneBy([
            'user' => $this->getUser(),
            'finishedAt' => null,
        ]);

        if (!$raceResult) {
            return $this->redirectToRoute('home_index');
        }

        return $this->redirectToRoute('race_show', ['race' => $raceResult->getRace()->getId()]);
    }
}
