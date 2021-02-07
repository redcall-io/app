<?php

namespace App\Controller;

use App\Base\BaseController;
use App\Entity\Badge;
use App\Entity\Structure;
use App\Entity\Volunteer;
use App\Form\Type\AudienceType;
use App\Manager\AudienceManager;
use App\Manager\BadgeManager;
use App\Manager\ExpirableManager;
use App\Manager\VolunteerManager;
use App\Model\Classification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="audience", name="audience_")
 */
class AudienceController extends BaseController
{
    /**
     * @var VolunteerManager
     */
    private $volunteerManager;

    /**
     * @var BadgeManager
     */
    private $badgeManager;

    /**
     * @var AudienceManager
     */
    private $audienceManager;

    /**
     * @var ExpirableManager
     */
    private $expirableManager;

    public function __construct(VolunteerManager $volunteerManager,
        BadgeManager $badgeManager,
        AudienceManager $audienceManager,
        ExpirableManager $expirableManager)
    {
        $this->volunteerManager = $volunteerManager;
        $this->badgeManager     = $badgeManager;
        $this->audienceManager  = $audienceManager;
        $this->expirableManager = $expirableManager;
    }

    /**
     * @Route(path="/search-volunteer", name="search_volunteer")
     */
    public function searchVolunteer(Request $request)
    {
        $criteria = $request->get('keyword');

        if ($this->isGranted('ROLE_ADMIN')) {
            $volunteers = $this->volunteerManager->searchAll($criteria, 20);
        } else {
            $volunteers = $this->volunteerManager->searchForCurrentUser($criteria, 20);
        }

        $results = [];
        foreach ($volunteers as $volunteer) {
            /* @var Volunteer $volunteer */
            $results[] = $volunteer->toSearchResults();
        }

        return $this->json($results);
    }

    /**
     * @Route(path="/search-badge", name="search_badge")
     */
    public function searchBadge(Request $request)
    {
        $badges = $this->badgeManager->searchNonVisibleUsableBadge(
            $request->get('keyword'),
            20
        );

        $results = [];
        foreach ($badges as $badge) {
            /* @var Badge $badge */
            $results[] = $badge->toSearchResults();
        }

        return $this->json($results);
    }

    /**
     * @Route(path="/numbers", name="numbers")
     */
    public function numbers(Request $request)
    {
        $data = AudienceType::getAudienceFormData($request);

        $response = [];

        if ('false' !== $request->get('badge_counts', true)) {
            $badgeCounts = $this->audienceManager->extractBadgeCounts(
                $data,
                $this->badgeManager->getPublicBadges()
            );

            $response['badge_counts'] = $badgeCounts;
        }

        $classification = $this->audienceManager->classifyAudience($data);

        $response['classification'] = $this->renderView('audience/classification.html.twig', [
            'classification' => $classification,
        ]);

        $response['triggered_count'] = count($classification->getReachable());

        return $this->json($response);
    }

    /**
     * @Route(path="/problems", name="problems")
     */
    public function problems(Request $request)
    {
        $data = AudienceType::getAudienceFormData($request);

        $classification = $this->audienceManager->classifyAudience($data);
        $classification->setReachable([]);

        $volunteers = $this->volunteerManager->getVolunteerList(
            call_user_func_array('array_merge', array_values($classification->toArray())),
            false
        );

        return $this->render('audience/problems.html.twig', [
            'classification' => $classification,
            'volunteers'     => $volunteers,
        ]);
    }

    /**
     * @Route(path="/large-selection", name="large_selection")
     */
    public function largeSelection(Request $request)
    {
        $data = AudienceType::getAudienceFormData($request);

        $classification = $this->audienceManager->classifyAudience($data);

        $volunteers = $this->volunteerManager->getVolunteerList(
            array_merge($classification->getExcluded(), $classification->getReachable())
        );

        return $this->render('audience/large_selection.html.twig', [
            'classification' => $classification,
            'volunteers'     => $volunteers,
        ]);
    }

    /**
     * @Route("/home", name="home")
     */
    public function home()
    {
        return $this->render('audience/home.html.twig', [
            'classification' => $this->getGlboalClassification(),
        ]);
    }

    /**
     * @Route("/resolve", name="resolve")
     */
    public function resolve()
    {
        $classification = $this->getGlboalClassification();
        $classification->setReachable([]);

        $volunteers = $this->volunteerManager->getVolunteerList(
            call_user_func_array('array_merge', $classification->toArray())
        );

        return $this->render('audience/resolve.html.twig', [
            'classification' => $classification,
            'volunteers'     => $volunteers,
            'gaia'           => getenv('GAIA_URL'),
        ]);
    }

    private function getGlboalClassification() : Classification
    {
        // Get all user's structure ids
        $structures = array_map(function (Structure $structure) {
            return $structure->getId();
        }, $this->getUser()->getStructures()->toArray());

        // Simulate audience selection
        $data = AudienceType::createEmptyData([
            'structures_local' => $structures,
            'badges_all'       => true,
        ]);

        return $this->audienceManager->classifyAudience($data);
    }
}