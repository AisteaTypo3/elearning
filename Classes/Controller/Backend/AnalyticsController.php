<?php

declare(strict_types=1);

namespace Aistea\Elearning\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\StandaloneView;

final class AnalyticsController extends ActionController
{
    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory
    ) {
    }

    public function indexAction(): ResponseInterface
    {
        $queryParams = $GLOBALS['TYPO3_REQUEST']->getQueryParams();
        $fromParam = is_string($queryParams['from'] ?? null) ? trim((string)$queryParams['from']) : '';
        $toParam = is_string($queryParams['to'] ?? null) ? trim((string)$queryParams['to']) : '';

        $today = new \DateTimeImmutable('today');
        $defaultFrom = $today->modify('-29 days');
        $fromDate = $this->parseDate($fromParam) ?? $defaultFrom;
        $toDate = $this->parseDate($toParam) ?? $today;
        if ($fromDate > $toDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        $fromTs = $fromDate->setTime(0, 0, 0)->getTimestamp();
        $toTs = $toDate->setTime(23, 59, 59)->getTimestamp();

        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        $courseQuery = $connectionPool->getQueryBuilderForTable('tx_elearning_domain_model_course');
        $publishedCourses = (int)$courseQuery
            ->count('uid')
            ->from('tx_elearning_domain_model_course')
            ->where(
                $courseQuery->expr()->eq('published', 1),
                $courseQuery->expr()->eq('deleted', 0),
                $courseQuery->expr()->eq('hidden', 0)
            )
            ->executeQuery()
            ->fetchOne();

        $progressQuery = $connectionPool->getQueryBuilderForTable('tx_elearning_domain_model_progress');
        $startedLessons = (int)$progressQuery
            ->count('uid')
            ->from('tx_elearning_domain_model_progress')
            ->where(
                $progressQuery->expr()->gte('last_visited_at', $progressQuery->createNamedParameter($fromTs)),
                $progressQuery->expr()->lte('last_visited_at', $progressQuery->createNamedParameter($toTs)),
                $progressQuery->expr()->eq('deleted', 0),
                $progressQuery->expr()->eq('hidden', 0)
            )
            ->executeQuery()
            ->fetchOne();

        $completedQuery = $connectionPool->getQueryBuilderForTable('tx_elearning_domain_model_progress');
        $completedLessons = (int)$completedQuery
            ->count('uid')
            ->from('tx_elearning_domain_model_progress')
            ->where(
                $completedQuery->expr()->eq('completed', 1),
                $completedQuery->expr()->gt('completed_at', 0),
                $completedQuery->expr()->gte('completed_at', $completedQuery->createNamedParameter($fromTs)),
                $completedQuery->expr()->lte('completed_at', $completedQuery->createNamedParameter($toTs)),
                $completedQuery->expr()->eq('deleted', 0),
                $completedQuery->expr()->eq('hidden', 0)
            )
            ->executeQuery()
            ->fetchOne();

        $activeQuery = $connectionPool->getQueryBuilderForTable('tx_elearning_domain_model_progress');
        $activeLearners = (int)$activeQuery
            ->selectLiteral('COUNT(DISTINCT fe_user) AS cnt')
            ->from('tx_elearning_domain_model_progress')
            ->where(
                $activeQuery->expr()->gte('last_visited_at', $activeQuery->createNamedParameter($fromTs)),
                $activeQuery->expr()->lte('last_visited_at', $activeQuery->createNamedParameter($toTs)),
                $activeQuery->expr()->eq('deleted', 0),
                $activeQuery->expr()->eq('hidden', 0)
            )
            ->executeQuery()
            ->fetchOne();

        $completionRate = $startedLessons > 0 ? (int)round(($completedLessons / $startedLessons) * 100) : 0;

        $tableQuery = $connectionPool->getQueryBuilderForTable('tx_elearning_domain_model_course');
        $tableQuery
            ->select('c.uid', 'c.title')
            ->addSelectLiteral('COUNT(DISTINCT p.fe_user) AS started_users')
            ->addSelectLiteral('COUNT(DISTINCT CASE WHEN p.completed = 1 AND p.completed_at >= ' .
                $tableQuery->createNamedParameter($fromTs) . ' AND p.completed_at <= ' .
                $tableQuery->createNamedParameter($toTs) . ' THEN p.fe_user END) AS completed_users')
            ->addSelectLiteral('AVG(r.rating) AS rating_avg')
            ->from('tx_elearning_domain_model_course', 'c')
            ->leftJoin(
                'c',
                'tx_elearning_domain_model_lesson',
                'l',
                'l.course = c.uid AND l.deleted = 0 AND l.hidden = 0'
            )
            ->leftJoin(
                'l',
                'tx_elearning_domain_model_progress',
                'p',
                'p.lesson = l.uid AND p.deleted = 0 AND p.hidden = 0 AND p.last_visited_at >= ' .
                $tableQuery->createNamedParameter($fromTs) . ' AND p.last_visited_at <= ' .
                $tableQuery->createNamedParameter($toTs)
            )
            ->leftJoin(
                'c',
                'tx_elearning_domain_model_course_rating',
                'r',
                'r.course = c.uid AND r.deleted = 0 AND r.hidden = 0'
            )
            ->where(
                $tableQuery->expr()->eq('c.published', 1),
                $tableQuery->expr()->eq('c.deleted', 0),
                $tableQuery->expr()->eq('c.hidden', 0)
            )
            ->groupBy('c.uid', 'c.title')
            ->orderBy('started_users', 'DESC')
            ->setMaxResults(10);

        $rows = $tableQuery->executeQuery()->fetchAllAssociative();

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(
            GeneralUtility::getFileAbsFileName('EXT:elearning/Resources/Private/Backend/Templates/Analytics/Index.html')
        );
        $view->assignMultiple([
            'publishedCourses' => $publishedCourses,
            'activeLearners' => $activeLearners,
            'completionRate' => $completionRate,
            'courseRows' => $rows,
            'fromDate' => $fromDate->format('Y-m-d'),
            'toDate' => $toDate->format('Y-m-d'),
            'moduleToken' => is_string($queryParams['token'] ?? null) ? (string)$queryParams['token'] : '',
            'pageId' => (int)($queryParams['id'] ?? 0),
        ]);

        $moduleTemplate = $this->moduleTemplateFactory->create($GLOBALS['TYPO3_REQUEST']);
        if (method_exists($moduleTemplate, 'getPageRenderer')) {
            $pageRenderer = $moduleTemplate->getPageRenderer();
            $pageRenderer->addCssFile('EXT:elearning/Resources/Public/Css/elearning-backend.css');
        } elseif (isset($GLOBALS['TYPO3_REQUEST'])) {
            $pageRenderer = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.page.renderer');
            if ($pageRenderer instanceof \TYPO3\CMS\Core\Page\PageRenderer) {
                $pageRenderer->addCssFile('EXT:elearning/Resources/Public/Css/elearning-backend.css');
            }
        }
        $content = $view->render();
        if (method_exists($moduleTemplate, 'setContent')) {
            $moduleTemplate->setContent($content);
            return $moduleTemplate->renderResponse();
        }
        if (method_exists($moduleTemplate, 'setBodyContent')) {
            $moduleTemplate->setBodyContent($content);
            return $moduleTemplate->renderResponse();
        }

        return new \TYPO3\CMS\Core\Http\HtmlResponse($content);
    }

    private function parseDate(string $value): ?\DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if (!$date) {
            return null;
        }

        return $date;
    }
}
