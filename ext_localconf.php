<?php

declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use Aistea\Elearning\Controller\CourseController;
use Aistea\Elearning\Controller\LessonController;
use Aistea\Elearning\Controller\DashboardController;

defined('TYPO3') or die();

ExtensionUtility::configurePlugin(
    'Elearning',
    'Courses',
    [
        CourseController::class => 'list,toggleFavorite',
    ],
    [
        CourseController::class => 'list,toggleFavorite',
    ]
);

ExtensionUtility::configurePlugin(
    'Elearning',
    'CourseDetail',
    [
        CourseController::class => 'show,toggleFavorite,rate',
    ],
    [
        CourseController::class => 'show,toggleFavorite,rate',
    ]
);

ExtensionUtility::configurePlugin(
    'Elearning',
    'Lesson',
    [
        LessonController::class => 'show,markComplete,submitQuiz,markVideoCompleted',
    ],
    [
        LessonController::class => 'show,markComplete,submitQuiz,markVideoCompleted',
    ]
);

ExtensionUtility::configurePlugin(
    'Elearning',
    'Dashboard',
    [
        DashboardController::class => 'index',
    ],
    [
        DashboardController::class => 'index',
    ]
);

ExtensionManagementUtility::addTypoScriptSetup(
    '@import "EXT:elearning/Configuration/TypoScript/setup.typoscript"'
);

$respectStoragePage = true;
try {
    $extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('elearning');
    $value = $extConfig['respectStoragePage'] ?? '1';
    $respectStoragePage = !in_array((string)$value, ['0', 'false', 'no', 'off'], true);
} catch (\Throwable) {
    $respectStoragePage = true;
}

if (!$respectStoragePage) {
    ExtensionManagementUtility::addTypoScriptSetup(
        'plugin.tx_elearning.persistence.respectStoragePage = 0'
    );
}

$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
$iconRegistry->registerIcon(
    'elearning-plugin-courses',
    SvgIconProvider::class,
    ['source' => 'EXT:elearning/Resources/Public/Icons/plugin_courses.svg']
);
$iconRegistry->registerIcon(
    'elearning-plugin-coursedetail',
    SvgIconProvider::class,
    ['source' => 'EXT:elearning/Resources/Public/Icons/plugin_coursedetail.svg']
);
$iconRegistry->registerIcon(
    'elearning-plugin-lesson',
    SvgIconProvider::class,
    ['source' => 'EXT:elearning/Resources/Public/Icons/plugin_lesson.svg']
);
$iconRegistry->registerIcon(
    'elearning-plugin-dashboard',
    SvgIconProvider::class,
    ['source' => 'EXT:elearning/Resources/Public/Icons/plugin_dashboard.svg']
);
