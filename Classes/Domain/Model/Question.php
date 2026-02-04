<?php

declare(strict_types=1);

namespace Aistea\Elearning\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;

class Question extends AbstractEntity
{
    protected ?Lesson $lesson = null;

    protected string $questionText = '';

    protected int $sorting = 0;

    /**
     * @var ObjectStorage<Answer>
     */
    #[Cascade(['value' => 'remove'])]
    protected ObjectStorage $answers;

    public function __construct()
    {
        $this->answers = new ObjectStorage();
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): void
    {
        $this->lesson = $lesson;
    }

    public function getQuestionText(): string
    {
        return $this->questionText;
    }

    public function setQuestionText(string $questionText): void
    {
        $this->questionText = $questionText;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): void
    {
        $this->sorting = $sorting;
    }

    public function getAnswers(): ObjectStorage
    {
        return $this->answers;
    }

    public function setAnswers(ObjectStorage $answers): void
    {
        $this->answers = $answers;
    }

    public function addAnswer(Answer $answer): void
    {
        $this->answers->attach($answer);
    }

    public function removeAnswer(Answer $answer): void
    {
        $this->answers->detach($answer);
    }
}
