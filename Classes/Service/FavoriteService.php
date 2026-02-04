<?php

declare(strict_types=1);

namespace Aistea\Elearning\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class FavoriteService
{
    public function isFavorite(int $feUserId, int $courseId): bool
    {
        if ($feUserId <= 0 || $courseId <= 0) {
            return false;
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_elearning_course_favorite');
        $count = $queryBuilder
            ->count('uid')
            ->from('tx_elearning_course_favorite')
            ->where(
                $queryBuilder->expr()->eq('fe_user', $queryBuilder->createNamedParameter($feUserId)),
                $queryBuilder->expr()->eq('course', $queryBuilder->createNamedParameter($courseId)),
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('hidden', 0)
            )
            ->executeQuery()
            ->fetchOne();

        return (int)$count > 0;
    }

    /**
     * @return int[]
     */
    public function getFavoriteCourseUids(int $feUserId): array
    {
        if ($feUserId <= 0) {
            return [];
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_elearning_course_favorite');
        $rows = $queryBuilder
            ->select('course')
            ->from('tx_elearning_course_favorite')
            ->where(
                $queryBuilder->expr()->eq('fe_user', $queryBuilder->createNamedParameter($feUserId)),
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('hidden', 0)
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int)$row['course'];
        }

        return array_values(array_unique($ids));
    }

    public function toggleFavorite(int $feUserId, int $courseId): bool
    {
        if ($feUserId <= 0 || $courseId <= 0) {
            return false;
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_elearning_course_favorite');
        $queryBuilder = $connection->createQueryBuilder();

        $existing = $queryBuilder
            ->select('uid')
            ->from('tx_elearning_course_favorite')
            ->where(
                $queryBuilder->expr()->eq('fe_user', $queryBuilder->createNamedParameter($feUserId)),
                $queryBuilder->expr()->eq('course', $queryBuilder->createNamedParameter($courseId)),
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('hidden', 0)
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        if ($existing) {
            $connection->delete('tx_elearning_course_favorite', ['uid' => (int)$existing]);
            return false;
        }

        $now = time();
        $connection->insert('tx_elearning_course_favorite', [
            'pid' => 0,
            'fe_user' => $feUserId,
            'course' => $courseId,
            'crdate' => $now,
            'tstamp' => $now,
            'deleted' => 0,
            'hidden' => 0,
        ]);

        return true;
    }
}
