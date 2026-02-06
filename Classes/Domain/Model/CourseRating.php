<?php

declare(strict_types=1);

namespace Aistea\Elearning\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class CourseRating extends AbstractEntity
{
    protected int $feUser = 0;

    protected ?Course $course = null;

    protected int $rating = 0;

    public function getFeUser(): int
    {
        return $this->feUser;
    }

    public function setFeUser(int $feUser): void
    {
        $this->feUser = $feUser;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): void
    {
        $this->course = $course;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): void
    {
        $this->rating = $rating;
    }
}
