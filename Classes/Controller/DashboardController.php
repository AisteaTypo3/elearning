<?php

declare(strict_types=1);

namespace Aistea\Elearning\Controller;

use Aistea\Elearning\Domain\Repository\CourseRepository;
use Aistea\Elearning\Domain\Repository\LessonRepository;
use Aistea\Elearning\Service\FavoriteService;
use Aistea\Elearning\Service\ProgressService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DashboardController extends AbstractFrontendController
{
    private FavoriteService $favoriteService;

    public function __construct(
        private readonly CourseRepository $courseRepository,
        private readonly LessonRepository $lessonRepository,
        private readonly ProgressService $progressService,
        \TYPO3\CMS\Core\Context\Context $context,
        ?FavoriteService $favoriteService = null
    ) {
        parent::__construct($context);
        $this->favoriteService = $favoriteService ?? GeneralUtility::makeInstance(FavoriteService::class);
    }

    public function indexAction(): \Psr\Http\Message\ResponseInterface
    {
        $feUserId = $this->getFrontendUserId();
        $courses = $this->courseRepository->findPublished();
        $favoriteCourseUids = $this->favoriteService->getFavoriteCourseUids($feUserId);
        $favoriteMap = array_fill_keys($favoriteCourseUids, true);

        $favoriteItems = [];
        $progressItems = [];
        $finishedItems = [];
        foreach ($courses as $course) {
            $lessons = $this->lessonRepository->findPublishedByCourse($course);
            $lessonUids = [];
            $lessonList = [];
            foreach ($lessons as $lesson) {
                $lessonUids[] = $lesson->getUid();
                $lessonList[] = $lesson;
            }

            $completedLessonUids = $this->progressService->getCompletedLessonUids($feUserId, $lessonUids);
            $total = count($lessonUids);
            $completed = count($completedLessonUids);
            $percent = $total > 0 ? (int)round(($completed / $total) * 100) : 0;
            $nextLesson = null;
            foreach ($lessonList as $lesson) {
                if (!in_array($lesson->getUid(), $completedLessonUids, true)) {
                    $nextLesson = $lesson;
                    break;
                }
            }

            $isFavorite = isset($favoriteMap[$course->getUid()]);
            $hasProgress = $this->progressService->hasProgressForCourse($feUserId, $course->getUid());
            if (!$isFavorite && !$hasProgress) {
                continue;
            }

            $item = [
                'course' => $course,
                'total' => $total,
                'completed' => $completed,
                'percent' => $percent,
                'nextLesson' => $nextLesson,
                'isFavorite' => $isFavorite,
            ];
            $isFinished = $total > 0 && $completed >= $total;
            if ($isFavorite) {
                $favoriteItems[] = $item;
            }
            if ($hasProgress && !$isFinished) {
                $progressItems[] = $item;
            }
            if ($isFinished) {
                $finishedItems[] = $item;
            }
        }

        $this->view->assignMultiple([
            'favoriteItems' => $favoriteItems,
            'progressItems' => $progressItems,
            'finishedItems' => $finishedItems,
        ]);
        $this->view->assignMultiple([
            'courseDetailPid' => $this->getConfiguredPid('courseDetailPid'),
            'lessonPid' => $this->getConfiguredPid('lessonPid'),
            'profile' => $this->getFrontendUserProfile(),
        ]);

        return $this->htmlResponse();
    }
}
