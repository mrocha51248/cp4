<?php

namespace App\Controller;

use App\Entity\Race;
use App\Repository\RaceRepository;
use App\Repository\RaceResultRepository;
use App\Service\RaceManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    #[Route('/admin', name: 'admin_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminList(RaceRepository $raceRepository): Response
    {
        return $this->render('race/admin_list.html.twig', [
            'races' => $raceRepository->findBy(['finishedAt' => null], ['createdAt' => 'ASC']),
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

    #[Route('/{race}/done', name: 'done', methods: 'POST', defaults: ['isForfeit' => false])]
    #[Route('/{race}/forfeit', name: 'forfeit', methods: 'POST', defaults: ['isForfeit' => true])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function done(
        Race $race,
        RaceResultRepository $raceResultRepository,
        RaceManager $raceManager,
        Request $request,
        bool $isForfeit
    ): Response {
        $submittedToken = $request->request->get('token');

        if ($this->isCsrfTokenValid("{$race->getId()}", $submittedToken)) {
            $raceResult = $raceResultRepository->findOneBy([
                'user' => $this->getUser(),
                'finishedAt' => null,
            ]);
            if (!$raceResult || $raceResult->getRace() !== $race) {
                throw new AccessDeniedException('Invalid race');
            }

            if ($isForfeit) {
                $raceManager->userForfeit($raceResult);
            } else {
                $raceManager->userDone($raceResult);
            }
            return $this->redirectToRoute('race_show', ['race' => $race->getId()]);
        }

        throw new AccessDeniedException();
    }
}
