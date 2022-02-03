<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Game;
use App\Repository\GameRepository;
use App\Service\RaceManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/game', name: 'game_')]
class GameController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(GameRepository $gameRepository): Response
    {
        return $this->render('game/index.html.twig', [
            'games' => $gameRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/{slug}', name: 'show')]
    public function show(Game $game): Response
    {
        return $this->render('game/show.html.twig', [
            'game' => $game,
        ]);
    }

    #[Route('/{gameSlug}/{categorySlug}/play', name: 'play', methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[ParamConverter('game', class: 'App\Entity\Game', options: ['mapping' => ['gameSlug' => 'slug']])]
    #[ParamConverter('category', class: 'App\Entity\Category', options: ['mapping' => ['categorySlug' => 'slug', 'game' => 'game']])]
    public function play(Game $game, Category $category, RaceManager $raceManager, Request $request): Response
    {
        $submittedToken = $request->request->get('token');

        if (!$this->isCsrfTokenValid("{$category->getId()}", $submittedToken)) {
            throw new AccessDeniedException();
        }

        $raceResult = $raceManager->play($this->getUser(), $category);
        return $this->redirectToRoute('race_show', ['race' => $raceResult->getRace()->getId()]);
    }
}
