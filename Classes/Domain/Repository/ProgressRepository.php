<?php

declare(strict_types=1);

namespace Aistea\Elearning\Domain\Repository;

use Aistea\Elearning\Domain\Model\Lesson;
use Aistea\Elearning\Domain\Model\Progress;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

class ProgressRepository extends Repository
{
    public function findOneByFeUserAndLesson(int $feUserId, Lesson $lesson): ?Progress
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('feUser', $feUserId),
                $query->equals('lesson', $lesson->getUid())
            )
        );
        $query->setLimit(1);
        return $query->execute()->getFirst();
    }

    /**
     * @param int[] $lessonUids
     * @return int[]
     */
    public function findCompletedLessonUidsForUser(array $lessonUids, int $feUserId): array
    {
        if ($lessonUids === []) {
            return [];
        }

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('feUser', $feUserId),
                $query->equals('completed', 1),
                $query->in('lesson', $lessonUids)
            )
        );

        $result = [];
        foreach ($query->execute() as $progress) {
            if ($progress instanceof Progress && $progress->getLesson() !== null) {
                $result[] = $progress->getLesson()->getUid();
            }
        }

        return array_values(array_unique($result));
    }

    public function hasProgressForCourse(int $feUserId, int $courseId): bool
    {
        if ($feUserId <= 0 || $courseId <= 0) {
            return false;
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_elearning_domain_model_progress');
        $count = $queryBuilder
            ->count('p.uid')
            ->from('tx_elearning_domain_model_progress', 'p')
            ->innerJoin('p', 'tx_elearning_domain_model_lesson', 'l', 'l.uid = p.lesson')
            ->where(
                $queryBuilder->expr()->eq('p.fe_user', $queryBuilder->createNamedParameter($feUserId)),
                $queryBuilder->expr()->eq('p.deleted', 0),
                $queryBuilder->expr()->eq('p.hidden', 0),
                $queryBuilder->expr()->eq('l.course', $queryBuilder->createNamedParameter($courseId)),
                $queryBuilder->expr()->eq('l.deleted', 0),
                $queryBuilder->expr()->eq('l.hidden', 0)
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        return (int)$count > 0;
    }
}
