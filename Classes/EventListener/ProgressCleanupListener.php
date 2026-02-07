<?php

declare(strict_types=1);

namespace Aistea\Elearning\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\DataHandling\Event\AfterRecordDeletedEvent;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ProgressCleanupListener
{
    #[AsEventListener(
        identifier: 'elearning/progress-cleanup',
        event: AfterRecordDeletedEvent::class
    )]
    public function __invoke(AfterRecordDeletedEvent $event): void
    {
        if ($event->getTable() !== 'tx_elearning_domain_model_lesson') {
            return;
        }

        $lessonUid = (int)$event->getUid();
        if ($lessonUid <= 0) {
            return;
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_elearning_domain_model_progress');
        $connection->delete('tx_elearning_domain_model_progress', ['lesson' => $lessonUid]);
    }
}
