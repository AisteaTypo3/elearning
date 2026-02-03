<?php

declare(strict_types=1);

namespace Vendor\Elearning\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class CourseRepository extends Repository
{
    public function findPublished(): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('published', 1));
        $query->setOrderings(['sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING]);
        return $query->execute();
    }

    public function findPublishedByCategoryId(int $categoryId): QueryResultInterface
    {
        $query = $this->createQuery();
        $constraints = [
            $query->equals('published', 1),
            $query->equals('categories.uid', $categoryId),
        ];
        $query->matching($query->logicalAnd(...$constraints));
        $query->setOrderings(['sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING]);
        return $query->execute();
    }
}
