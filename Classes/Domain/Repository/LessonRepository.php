<?php

declare(strict_types=1);

namespace Aistea\Elearning\Domain\Repository;

use Aistea\Elearning\Domain\Model\Course;
use Aistea\Elearning\Domain\Model\Lesson;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class LessonRepository extends Repository
{
    public function findPublishedByCourse(Course $course): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('course', $course->getUid()),
                $query->equals('published', 1)
            )
        );
        $query->setOrderings([
            'sorting' => QueryInterface::ORDER_ASCENDING,
            'uid' => QueryInterface::ORDER_ASCENDING,
        ]);
        return $query->execute();
    }

    public function findNextLesson(Course $course, Lesson $currentLesson): ?Lesson
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('course', $course->getUid()),
                $query->equals('published', 1),
                $query->greaterThan('sorting', $currentLesson->getSorting())
            )
        );
        $query->setOrderings([
            'sorting' => QueryInterface::ORDER_ASCENDING,
            'uid' => QueryInterface::ORDER_ASCENDING,
        ]);
        $query->setLimit(1);
        return $query->execute()->getFirst();
    }

    public function findPreviousLesson(Course $course, Lesson $currentLesson): ?Lesson
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('course', $course->getUid()),
                $query->equals('published', 1),
                $query->lessThan('sorting', $currentLesson->getSorting())
            )
        );
        $query->setOrderings([
            'sorting' => QueryInterface::ORDER_DESCENDING,
            'uid' => QueryInterface::ORDER_DESCENDING,
        ]);
        $query->setLimit(1);
        return $query->execute()->getFirst();
    }
}
