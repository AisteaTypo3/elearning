<?php

declare(strict_types=1);

namespace Vendor\Elearning\Controller;

use Vendor\Elearning\Domain\Model\Course;
use Vendor\Elearning\Domain\Repository\CourseRepository;
use Vendor\Elearning\Domain\Repository\LessonRepository;
use Vendor\Elearning\Service\ProgressService;
use TYPO3\CMS\Core\Error\Http\PageNotFoundException;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;

class CourseController extends AbstractFrontendController
{
    public function __construct(
        private readonly CourseRepository $courseRepository,
        private readonly LessonRepository $lessonRepository,
        private readonly ProgressService $progressService,
        \TYPO3\CMS\Core\Context\Context $context
    ) {
        parent::__construct($context);
    }

    public function listAction(): \Psr\Http\Message\ResponseInterface
    {
        $feUserId = $this->getFrontendUserId();
        $selectedCategoryId = 0;
        if ($this->request->hasArgument('category')) {
            $selectedCategoryId = max(0, (int)$this->request->getArgument('category'));
        }
        $courses = $selectedCategoryId > 0
            ? $this->courseRepository->findPublishedByCategoryId($selectedCategoryId)
            : $this->courseRepository->findPublished();
        $currentPage = 1;
        if ($this->request->hasArgument('page')) {
            $currentPage = max(1, (int)$this->request->getArgument('page'));
        }
        $paginator = new QueryResultPaginator($courses, $currentPage, 6);
        $pagination = new SimplePagination($paginator);
        $numberOfPages = $paginator->getNumberOfPages();
        $prevPage = $currentPage > 1 ? $currentPage - 1 : null;
        $nextPage = $currentPage < $numberOfPages ? $currentPage + 1 : null;
        $courseProgress = [];
        foreach ($paginator->getPaginatedItems() as $course) {
            $lessons = $this->lessonRepository->findPublishedByCourse($course);
            $lessonUids = [];
            foreach ($lessons as $lesson) {
                $lessonUids[] = $lesson->getUid();
            }
            $completedLessonUids = $this->progressService->getCompletedLessonUids($feUserId, $lessonUids);
            $total = count($lessonUids);
            $completed = count($completedLessonUids);
            $percent = $total > 0 ? (int)round(($completed / $total) * 100) : 0;
            $courseProgress[$course->getUid()] = [
                'total' => $total,
                'completed' => $completed,
                'percent' => $percent,
            ];
        }

        $this->view->assignMultiple([
            'courses' => $paginator->getPaginatedItems(),
            'pagination' => $pagination,
            'paginator' => $paginator,
            'currentPage' => $currentPage,
            'prevPage' => $prevPage,
            'nextPage' => $nextPage,
            'categories' => $this->fetchCategories($selectedCategoryId),
            'selectedCategoryId' => $selectedCategoryId,
            'courseProgress' => $courseProgress,
            'courseDetailPid' => $this->getConfiguredPid('courseDetailPid'),
        ]);
        return $this->htmlResponse();
    }

    private function fetchCategories(int $selectedCategoryId): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_category');
        $queryBuilder->select('c.uid', 'c.title')
            ->from('sys_category', 'c')
            ->innerJoin('c', 'sys_category_record_mm', 'mm', 'mm.uid_local = c.uid')
            ->innerJoin('mm', 'tx_elearning_domain_model_course', 'course', 'course.uid = mm.uid_foreign')
            ->where(
                $queryBuilder->expr()->eq('mm.tablenames', $queryBuilder->createNamedParameter('tx_elearning_domain_model_course')),
                $queryBuilder->expr()->eq('mm.fieldname', $queryBuilder->createNamedParameter('categories')),
                $queryBuilder->expr()->eq('course.published', $queryBuilder->createNamedParameter(1)),
                $queryBuilder->expr()->eq('course.deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('course.hidden', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('c.deleted', 0),
                $queryBuilder->expr()->eq('c.hidden', 0)
            )
            ->groupBy('c.uid', 'c.title')
            ->orderBy('c.title', 'ASC');

        $rows = $queryBuilder->executeQuery()->fetchAllAssociative();

        if ($selectedCategoryId > 0) {
            $found = false;
            foreach ($rows as $row) {
                if ((int)$row['uid'] === $selectedCategoryId) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $selected = $this->fetchCategoryById($selectedCategoryId);
                if ($selected !== null) {
                    $rows[] = $selected;
                }
            }
        }

        return is_array($rows) ? $rows : [];
    }

    private function fetchCategoryById(int $categoryId): ?array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_category');
        $row = $queryBuilder
            ->select('uid', 'title')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($categoryId)),
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('hidden', 0)
            )
            ->executeQuery()
            ->fetchAssociative();

        return $row ?: null;
    }

    public function showAction(Course $course): \Psr\Http\Message\ResponseInterface
    {
        if (!$course->isPublished()) {
            throw new PageNotFoundException($this->translate('errors.course_not_published'), 1738563935);
        }

        $feUserId = $this->getFrontendUserId();
        $lessons = $this->lessonRepository->findPublishedByCourse($course);
        $lessonUids = [];
        $firstLesson = null;
        foreach ($lessons as $lesson) {
            $lessonUids[] = $lesson->getUid();
            if ($firstLesson === null) {
                $firstLesson = $lesson;
            }
        }
        $completedLessonUids = $this->progressService->getCompletedLessonUids($feUserId, $lessonUids);
        $total = count($lessonUids);
        $completed = count($completedLessonUids);
        $percent = $total > 0 ? (int)round(($completed / $total) * 100) : 0;
        $this->view->assignMultiple([
            'course' => $course,
            'lessons' => $lessons,
            'lessonPid' => $this->getConfiguredPid('lessonPid'),
            'firstLesson' => $firstLesson,
            'courseProgress' => [
                'total' => $total,
                'completed' => $completed,
                'percent' => $percent,
            ],
        ]);

        return $this->htmlResponse();
    }
}
