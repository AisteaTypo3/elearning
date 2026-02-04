<?php

declare(strict_types=1);

namespace Aistea\Elearning\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Progress extends AbstractEntity
{
    protected int $feUser = 0;

    protected ?Lesson $lesson = null;

    protected bool $completed = false;

    protected ?\DateTime $completedAt = null;

    protected bool $quizPassed = false;

    protected ?\DateTime $quizPassedAt = null;

    protected ?\DateTime $lastQuizFailedAt = null;

    protected ?\DateTime $lastVisitedAt = null;

    public function getFeUser(): int
    {
        return $this->feUser;
    }

    public function setFeUser(int $feUser): void
    {
        $this->feUser = $feUser;
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): void
    {
        $this->lesson = $lesson;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): void
    {
        $this->completed = $completed;
    }

    public function getCompletedAt(): ?\DateTime
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTime $completedAt): void
    {
        $this->completedAt = $completedAt;
    }

    public function isQuizPassed(): bool
    {
        return $this->quizPassed;
    }

    public function setQuizPassed(bool $quizPassed): void
    {
        $this->quizPassed = $quizPassed;
    }

    public function getQuizPassedAt(): ?\DateTime
    {
        return $this->quizPassedAt;
    }

    public function setQuizPassedAt(?\DateTime $quizPassedAt): void
    {
        $this->quizPassedAt = $quizPassedAt;
    }

    public function getLastQuizFailedAt(): ?\DateTime
    {
        return $this->lastQuizFailedAt;
    }

    public function setLastQuizFailedAt(?\DateTime $lastQuizFailedAt): void
    {
        $this->lastQuizFailedAt = $lastQuizFailedAt;
    }

    public function getLastVisitedAt(): ?\DateTime
    {
        return $this->lastVisitedAt;
    }

    public function setLastVisitedAt(?\DateTime $lastVisitedAt): void
    {
        $this->lastVisitedAt = $lastVisitedAt;
    }
}
