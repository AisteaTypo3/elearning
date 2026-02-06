<?php

declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

ExtensionUtility::registerPlugin(
    'Elearning',
    'Courses',
    'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.courses.title',
    'elearning-plugin-courses'
);

ExtensionUtility::registerPlugin(
    'Elearning',
    'CourseDetail',
    'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.coursedetail.title',
    'elearning-plugin-coursedetail'
);

ExtensionUtility::registerPlugin(
    'Elearning',
    'Lesson',
    'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.lesson.title',
    'elearning-plugin-lesson'
);

ExtensionUtility::registerPlugin(
    'Elearning',
    'Dashboard',
    'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.dashboard.title',
    'elearning-plugin-dashboard'
);

ExtensionManagementUtility::addPiFlexFormValue(
    'elearning_courses',
    'FILE:EXT:elearning/Configuration/FlexForms/Courses.xml'
);

ExtensionManagementUtility::addPiFlexFormValue(
    'elearning_coursedetail',
    'FILE:EXT:elearning/Configuration/FlexForms/CourseDetail.xml'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['elearning_courses'] = 'pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['elearning_coursedetail'] = 'pi_flexform';

ExtensionManagementUtility::addPageTSConfig(
    "TCEFORM.tt_content.pi_flexform.elearning_courses.sDEF.settings\\.courseDetailPid.disabled = 0\n" .
    "TCEFORM.tt_content.pi_flexform.elearning_coursedetail.sDEF.settings\\.lessonPid.disabled = 0\n"
);
