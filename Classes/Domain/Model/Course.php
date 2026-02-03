<?php

declare(strict_types=1);

namespace Vendor\Elearning\Domain\Model;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Domain\Model\Category;

class Course extends AbstractEntity
{
    protected string $title = '';

    protected string $slug = '';

    protected string $teaser = '';

    protected string $description = '';

    protected bool $published = false;

    protected int $sorting = 0;

    /**
     * @var ObjectStorage<Lesson>
     */
    #[Cascade(['value' => 'remove'])]
    protected ObjectStorage $lessons;

    protected ?\TYPO3\CMS\Extbase\Domain\Model\FileReference $image = null;

    /**
     * @var ObjectStorage<Category>
     */
    protected ObjectStorage $categories;

    public function __construct()
    {
        $this->lessons = new ObjectStorage();
        $this->categories = new ObjectStorage();
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

    public function getTeaser(): string
    {
        return $this->teaser;
    }

    public function setTeaser(string $teaser): void
    {
        $this->teaser = $teaser;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
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

    public function getLessons(): ObjectStorage
    {
        return $this->lessons;
    }

    public function setLessons(ObjectStorage $lessons): void
    {
        $this->lessons = $lessons;
    }

    public function addLesson(Lesson $lesson): void
    {
        $this->lessons->attach($lesson);
    }

    public function removeLesson(Lesson $lesson): void
    {
        $this->lessons->detach($lesson);
    }

    public function getImage(): ?\TYPO3\CMS\Extbase\Domain\Model\FileReference
    {
        return $this->image;
    }

    public function setImage(?\TYPO3\CMS\Extbase\Domain\Model\FileReference $image): void
    {
        $this->image = $image;
    }

    /**
     * @return ObjectStorage<Category>
     */
    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    /**
     * @param ObjectStorage<Category> $categories
     */
    public function setCategories(ObjectStorage $categories): void
    {
        $this->categories = $categories;
    }

    public function addCategory(Category $category): void
    {
        $this->categories->attach($category);
    }

    public function removeCategory(Category $category): void
    {
        $this->categories->detach($category);
    }
}
