<?php

declare(strict_types=1);

namespace Vendor\Elearning\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Vendor\Elearning\Service\NotificationService;

#[AsCommand(
    name: 'elearning:send-reminders',
    description: 'Send reminder emails to frontend users with unfinished lessons.'
)]
final class SendRemindersCommand extends Command
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('days', null, InputOption::VALUE_REQUIRED, 'Inactive days threshold', '7')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Max users to notify (0 = no limit)', '0')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not send, only print counts');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $days = max(1, (int)$input->getOption('days'));
        $limit = max(0, (int)$input->getOption('limit'));
        $dryRun = (bool)$input->getOption('dry-run');
        $threshold = (new \DateTimeImmutable(sprintf('-%d days', $days)))->getTimestamp();

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_elearning_domain_model_progress');
        $queryBuilder
            ->select(
                'u.uid',
                'u.email',
                'u.first_name',
                'u.last_name',
                'u.name',
                'u.username'
            )
            ->addSelectLiteral('COUNT(DISTINCT p.uid) AS pending_count')
            ->from('tx_elearning_domain_model_progress', 'p')
            ->innerJoin('p', 'fe_users', 'u', 'u.uid = p.fe_user')
            ->innerJoin('p', 'tx_elearning_domain_model_lesson', 'l', 'l.uid = p.lesson')
            ->innerJoin('l', 'tx_elearning_domain_model_course', 'c', 'c.uid = l.course')
            ->where(
                $queryBuilder->expr()->eq('p.completed', 0),
                $queryBuilder->expr()->eq('p.deleted', 0),
                $queryBuilder->expr()->eq('p.hidden', 0),
                $queryBuilder->expr()->gt('p.last_visited_at', 0),
                $queryBuilder->expr()->lt('p.last_visited_at', $queryBuilder->createNamedParameter($threshold)),
                $queryBuilder->expr()->neq('u.email', $queryBuilder->createNamedParameter('')),
                $queryBuilder->expr()->eq('u.disable', 0),
                $queryBuilder->expr()->eq('u.deleted', 0),
                $queryBuilder->expr()->eq('l.published', 1),
                $queryBuilder->expr()->eq('l.deleted', 0),
                $queryBuilder->expr()->eq('l.hidden', 0),
                $queryBuilder->expr()->eq('c.published', 1),
                $queryBuilder->expr()->eq('c.deleted', 0),
                $queryBuilder->expr()->eq('c.hidden', 0)
            )
            ->groupBy('u.uid', 'u.email', 'u.first_name', 'u.last_name', 'u.name', 'u.username')
            ->orderBy('pending_count', 'DESC');

        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        $rows = $queryBuilder->executeQuery()->fetchAllAssociative();
        $settings = [
            'enabled' => true,
            'reminders' => [
                'enabled' => true,
            ],
        ];

        $sent = 0;
        foreach ($rows as $row) {
            $pendingCount = (int)($row['pending_count'] ?? 0);
            if ($pendingCount <= 0) {
                continue;
            }
            if ($dryRun) {
                $sent++;
                continue;
            }

            if ($this->notificationService->sendReminder($row, $pendingCount, $settings)) {
                $sent++;
            }
        }

        $output->writeln(sprintf('Reminder candidates: %d, sent: %d', count($rows), $sent));
        return Command::SUCCESS;
    }
}
