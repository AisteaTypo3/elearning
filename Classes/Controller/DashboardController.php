<?php

declare(strict_types=1);

namespace Vendor\Elearning\Controller;

use Vendor\Elearning\Domain\Repository\CourseRepository;
use Vendor\Elearning\Domain\Repository\LessonRepository;
use Vendor\Elearning\Service\ProgressService;

class DashboardController extends AbstractFrontendController
{
    public function __construct(
        private readonly CourseRepository $courseRepository,
        private readonly LessonRepository $lessonRepository,
        private readonly ProgressService $progressService,
        \TYPO3\CMS\Core\Context\Context $context
    ) {
        parent::__construct($context);
    }

    public function indexAction(): \Psr\Http\Message\ResponseInterface
    {
        $feUserId = $this->getFrontendUserId();
        $courses = $this->courseRepository->findPublished();

        $items = [];
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

            $items[] = [
                'course' => $course,
                'total' => $total,
                'completed' => $completed,
                'percent' => $percent,
                'nextLesson' => $nextLesson,
            ];
        }

        $this->view->assign('items', $items);
        $this->view->assignMultiple([
            'courseDetailPid' => $this->getConfiguredPid('courseDetailPid'),
            'lessonPid' => $this->getConfiguredPid('lessonPid'),
            'profile' => $this->getFrontendUserProfile(),
        ]);

        return $this->htmlResponse();
    }
}
