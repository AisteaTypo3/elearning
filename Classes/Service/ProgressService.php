<?php

declare(strict_types=1);

namespace Vendor\Elearning\Service;

use Vendor\Elearning\Domain\Model\Lesson;
use Vendor\Elearning\Domain\Model\Progress;
use Vendor\Elearning\Domain\Repository\ProgressRepository;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

final class ProgressService
{
    public function __construct(
        private readonly ProgressRepository $progressRepository,
        private readonly PersistenceManagerInterface $persistenceManager
    ) {
    }

    public function recordVisit(int $feUserId, Lesson $lesson): Progress
    {
        $now = new \DateTime();
        if ($feUserId <= 0) {
            $progress = new Progress();
            $progress->setFeUser(0);
            $progress->setLesson($lesson);
            $progress->setCompleted(false);
            $progress->setLastVisitedAt($now);
            return $progress;
        }

        $progress = $this->progressRepository->findOneByFeUserAndLesson($feUserId, $lesson);

        if ($progress === null) {
            $progress = new Progress();
            $progress->setFeUser($feUserId);
            $progress->setLesson($lesson);
            $progress->setCompleted(false);
            $progress->setPid($lesson->getPid());
            $progress->setLastVisitedAt($now);
            $this->progressRepository->add($progress);
            $this->persistenceManager->persistAll();
            return $progress;
        }

        $progress->setLastVisitedAt($now);
        $this->progressRepository->update($progress);
        $this->persistenceManager->persistAll();

        return $progress;
    }

    public function markCompleted(int $feUserId, Lesson $lesson): Progress
    {
        $now = new \DateTime();
        if ($feUserId <= 0) {
            $progress = new Progress();
            $progress->setFeUser(0);
            $progress->setLesson($lesson);
            $progress->setPid($lesson->getPid());
            $progress->setCompleted(true);
            $progress->setCompletedAt($now);
            $progress->setLastVisitedAt($now);
            return $progress;
        }

        $progress = $this->progressRepository->findOneByFeUserAndLesson($feUserId, $lesson);

        if ($progress === null) {
            $progress = new Progress();
            $progress->setFeUser($feUserId);
            $progress->setLesson($lesson);
            $progress->setPid($lesson->getPid());
            $progress->setCompleted(true);
            $progress->setCompletedAt($now);
            $progress->setLastVisitedAt($now);
            $this->progressRepository->add($progress);
            $this->persistenceManager->persistAll();
            return $progress;
        }

        $progress->setCompleted(true);
        $progress->setCompletedAt($now);
        $progress->setLastVisitedAt($now);
        $this->progressRepository->update($progress);
        $this->persistenceManager->persistAll();

        return $progress;
    }

    public function markQuizPassed(int $feUserId, Lesson $lesson): Progress
    {
        $now = new \DateTime();
        if ($feUserId <= 0) {
            $progress = new Progress();
            $progress->setFeUser(0);
            $progress->setLesson($lesson);
            $progress->setPid($lesson->getPid());
            $progress->setQuizPassed(true);
            $progress->setQuizPassedAt($now);
            $progress->setCompleted(true);
            $progress->setCompletedAt($now);
            $progress->setLastVisitedAt($now);
            return $progress;
        }

        $progress = $this->progressRepository->findOneByFeUserAndLesson($feUserId, $lesson);

        if ($progress === null) {
            $progress = new Progress();
            $progress->setFeUser($feUserId);
            $progress->setLesson($lesson);
            $progress->setPid($lesson->getPid());
            $this->progressRepository->add($progress);
        }

        $progress->setQuizPassed(true);
        $progress->setQuizPassedAt($now);
        $progress->setCompleted(true);
        $progress->setCompletedAt($now);
        $progress->setLastVisitedAt($now);
        $this->progressRepository->update($progress);
        $this->persistenceManager->persistAll();

        return $progress;
    }

    public function markQuizFailed(int $feUserId, Lesson $lesson): Progress
    {
        $now = new \DateTime();
        if ($feUserId <= 0) {
            $progress = new Progress();
            $progress->setFeUser(0);
            $progress->setLesson($lesson);
            $progress->setPid($lesson->getPid());
            $progress->setQuizPassed(false);
            $progress->setLastQuizFailedAt($now);
            $progress->setLastVisitedAt($now);
            return $progress;
        }

        $progress = $this->progressRepository->findOneByFeUserAndLesson($feUserId, $lesson);
        if ($progress === null) {
            $progress = new Progress();
            $progress->setFeUser($feUserId);
            $progress->setLesson($lesson);
            $progress->setPid($lesson->getPid());
            $progress->setQuizPassed(false);
            $progress->setLastQuizFailedAt($now);
            $progress->setLastVisitedAt($now);
            $this->progressRepository->add($progress);
            $this->persistenceManager->persistAll();
            return $progress;
        }

        if ($progress->isQuizPassed()) {
            return $progress;
        }

        $progress->setQuizPassed(false);
        $progress->setLastQuizFailedAt($now);
        $progress->setLastVisitedAt($now);
        $this->progressRepository->update($progress);
        $this->persistenceManager->persistAll();

        return $progress;
    }

    public function getProgressForLesson(int $feUserId, Lesson $lesson): ?Progress
    {
        return $this->progressRepository->findOneByFeUserAndLesson($feUserId, $lesson);
    }

    /**
     * @param int[] $lessonUids
     * @return int[]
     */
    public function getCompletedLessonUids(int $feUserId, array $lessonUids): array
    {
        if ($feUserId <= 0) {
            return [];
        }

        return $this->progressRepository->findCompletedLessonUidsForUser($lessonUids, $feUserId);
    }
}
