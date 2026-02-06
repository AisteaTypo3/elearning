<?php

declare(strict_types=1);

namespace Aistea\Elearning\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

final class CourseRatingService
{
    public function __construct(
        private readonly ConfigurationManagerInterface $configurationManager
    ) {
    }

    public function getUserRating(int $feUserId, int $courseId): int
    {
        if ($feUserId <= 0 || $courseId <= 0) {
            return 0;
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_elearning_domain_model_course_rating');

        $value = $queryBuilder
            ->select('rating')
            ->from('tx_elearning_domain_model_course_rating')
            ->where(
                $queryBuilder->expr()->eq('fe_user', $queryBuilder->createNamedParameter($feUserId)),
                $queryBuilder->expr()->eq('course', $queryBuilder->createNamedParameter($courseId)),
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('hidden', 0)
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        return (int)$value;
    }

    public function getCourseRatingSummary(int $courseId): array
    {
        if ($courseId <= 0) {
            return ['average' => 0.0, 'count' => 0];
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_elearning_domain_model_course_rating');

        $row = $queryBuilder
            ->select('rating')
            ->addSelectLiteral('COUNT(uid) AS rating_count')
            ->addSelectLiteral('AVG(rating) AS rating_avg')
            ->from('tx_elearning_domain_model_course_rating')
            ->where(
                $queryBuilder->expr()->eq('course', $queryBuilder->createNamedParameter($courseId)),
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('hidden', 0)
            )
            ->executeQuery()
            ->fetchAssociative();

        return [
            'average' => isset($row['rating_avg']) ? (float)$row['rating_avg'] : 0.0,
            'count' => isset($row['rating_count']) ? (int)$row['rating_count'] : 0,
        ];
    }

    public function setRating(int $feUserId, int $courseId, int $rating, int $fallbackPid): void
    {
        if ($feUserId <= 0 || $courseId <= 0) {
            return;
        }

        $rating = max(1, min(5, $rating));
        $storagePid = $this->resolveStoragePid($fallbackPid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_elearning_domain_model_course_rating');
        $queryBuilder = $connection->createQueryBuilder();

        $existing = $queryBuilder
            ->select('uid')
            ->from('tx_elearning_domain_model_course_rating')
            ->where(
                $queryBuilder->expr()->eq('fe_user', $queryBuilder->createNamedParameter($feUserId)),
                $queryBuilder->expr()->eq('course', $queryBuilder->createNamedParameter($courseId)),
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('hidden', 0)
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        $now = time();
        if ($existing) {
            $connection->update(
                'tx_elearning_domain_model_course_rating',
                ['rating' => $rating, 'tstamp' => $now],
                ['uid' => (int)$existing]
            );
            return;
        }

        $connection->insert('tx_elearning_domain_model_course_rating', [
            'pid' => $storagePid,
            'fe_user' => $feUserId,
            'course' => $courseId,
            'rating' => $rating,
            'crdate' => $now,
            'tstamp' => $now,
            'deleted' => 0,
            'hidden' => 0,
        ]);
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
