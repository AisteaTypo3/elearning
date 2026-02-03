<?php

declare(strict_types=1);

namespace Vendor\Elearning\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;

class Lesson extends AbstractEntity
{
    protected ?Course $course = null;

    protected string $title = '';

    protected string $slug = '';

    protected string $content = '';

    protected string $type = 'content';

    protected string $videoUrl = '';

    protected ?\TYPO3\CMS\Extbase\Domain\Model\FileReference $file = null;

    protected string $linkUrl = '';

    protected int $durationMinutes = 0;

    protected bool $published = false;

    protected int $sorting = 0;

    /**
     * @var ObjectStorage<Question>
     */
    #[Cascade(['value' => 'remove'])]
    protected ObjectStorage $questions;

    public function __construct()
    {
        $this->questions = new ObjectStorage();
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): void
    {
        $this->course = $course;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getVideoUrl(): string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(string $videoUrl): void
    {
        $this->videoUrl = $videoUrl;
    }

    public function getFile(): ?\TYPO3\CMS\Extbase\Domain\Model\FileReference
    {
        return $this->file;
    }

    public function setFile(?\TYPO3\CMS\Extbase\Domain\Model\FileReference $file): void
    {
        $this->file = $file;
    }

    public function getLinkUrl(): string
    {
        return $this->linkUrl;
    }

    public function setLinkUrl(string $linkUrl): void
    {
        $this->linkUrl = $linkUrl;
    }

    public function getDurationMinutes(): int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(int $durationMinutes): void
    {
        $this->durationMinutes = $durationMinutes;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): void
    {
        $this->sorting = $sorting;
    }

    public function getQuestions(): ObjectStorage
    {
        return $this->questions;
    }

    public function setQuestions(ObjectStorage $questions): void
    {
        $this->questions = $questions;
    }

    public function addQuestion(Question $question): void
    {
        $this->questions->attach($question);
    }

    public function removeQuestion(Question $question): void
    {
        $this->questions->detach($question);
    }
}
