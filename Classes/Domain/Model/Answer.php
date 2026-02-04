<?php

declare(strict_types=1);

namespace Aistea\Elearning\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Answer extends AbstractEntity
{
    protected ?Question $question = null;

    protected string $title = '';

    protected bool $isCorrect = false;

    protected int $sorting = 0;

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): void
    {
        $this->question = $question;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }

    public function setIsCorrect(bool $isCorrect): void
    {
        $this->isCorrect = $isCorrect;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): void
    {
        $this->sorting = $sorting;
    }
}
