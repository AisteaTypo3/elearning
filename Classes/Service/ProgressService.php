<?php

declare(strict_types=1);

namespace Aistea\Elearning\Service;

use Aistea\Elearning\Domain\Model\Lesson;
use Aistea\Elearning\Domain\Model\Progress;
use Aistea\Elearning\Domain\Repository\ProgressRepository;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

final class ProgressService
{
    public function __construct(
        private readonly ProgressRepository $progressRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly ConfigurationManagerInterface $configurationManager
    ) {
    }

    public function recordVisit(int $feUserId, Lesson $lesson): Progress
    {
        $now = new \DateTime();
        $storagePid = $this->resolveStoragePid($lesson->getPid());
        if ($feUserId <= 0) {
            $progress = new Progress();
            $progress->setFeUser(0);
            $progress->setLesson($lesson);
            $progress->setCompleted(false);
            $progress->setLastVisitedAt($now);
            $progress->setPid($storagePid);
            return $progress;
        }

        $progress = $this->progressRepository->findOneByFeUserAndLesson($feUserId, $lesson);

        if ($progress === null) {
            $progress = new Progress();
            $progress->setFeUser($feUserId);
            $progress->setLesson($lesson);
            $progress->setCompleted(false);
            $progress->setPid($storagePid);
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
        $storagePid = $this->resolveStoragePid($lesson->getPid());
        if ($feUserId <= 0) {
            $progress = new Progress();
            $progress->setFeUser(0);
            $progress->setLesson($lesson);
            $progress->setPid($storagePid);
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
            $progress->setPid($storagePid);
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
        $storagePid = $this->resolveStoragePid($lesson->getPid());
        if ($feUserId <= 0) {
            $progress = new Progress();
            $progress->setFeUser(0);
            $progress->setLesson($lesson);
            $progress->setPid($storagePid);
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
            $progress->setPid($storagePid);
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
        $storagePid = $this->resolveStoragePid($lesson->getPid());
        if ($feUserId <= 0) {
            $progress = new Progress();
            $progress->setFeUser(0);
            $progress->setLesson($lesson);
            $progress->setPid($storagePid);
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
            $progress->setPid($storagePid);
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

    public function hasProgressForCourse(int $feUserId, int $courseId): bool
    {
        if ($feUserId <= 0 || $courseId <= 0) {
            return false;
        }

        return $this->progressRepository->hasProgressForCourse($feUserId, $courseId);
    }

    private function resolveStoragePid(int $fallbackPid): int
    {
        $storagePid = 0;
        try {
            $framework = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'Elearning'
            );
            $persistence = is_array($framework['persistence'] ?? null) ? $framework['persistence'] : [];
            $storagePid = $this->parseStoragePid((string)($persistence['storagePid'] ?? '0'));
        } catch (\Throwable) {
            $storagePid = 0;
        }

        return $storagePid > 0 ? $storagePid : $fallbackPid;
    }

    private function parseStoragePid(string $value): int
    {
        if ($value === '') {
            return 0;
        }
        foreach (explode(',', $value) as $segment) {
            $pid = (int)trim($segment);
            if ($pid > 0) {
                return $pid;
            }
        }
        return 0;
    }
}
