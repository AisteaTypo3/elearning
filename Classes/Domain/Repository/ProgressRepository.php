<?php

declare(strict_types=1);

namespace Vendor\Elearning\Domain\Repository;

use Vendor\Elearning\Domain\Model\Lesson;
use Vendor\Elearning\Domain\Model\Progress;
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
}
