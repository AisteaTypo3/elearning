<?php

declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

ExtensionUtility::registerPlugin(
    'Elearning',
    'Courses',
    'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.courses.title'
);

ExtensionUtility::registerPlugin(
    'Elearning',
    'CourseDetail',
    'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.coursedetail.title'
);

ExtensionUtility::registerPlugin(
    'Elearning',
    'Lesson',
    'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.lesson.title'
);

ExtensionUtility::registerPlugin(
    'Elearning',
    'Dashboard',
    'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.dashboard.title'
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
    "mod.wizards.newContentElement.wizardItems.plugins {\n" .
    "  elements {\n" .
    "    elearning_courses {\n" .
    "      iconIdentifier = elearning-plugin-courses\n" .
    "      title = LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.courses.title\n" .
    "      description = LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.courses.description\n" .
    "      tt_content_defValues {\n" .
    "        CType = list\n" .
    "        list_type = elearning_courses\n" .
    "      }\n" .
    "    }\n" .
    "    elearning_coursedetail {\n" .
    "      iconIdentifier = elearning-plugin-coursedetail\n" .
    "      title = LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.coursedetail.title\n" .
    "      description = LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.coursedetail.description\n" .
    "      tt_content_defValues {\n" .
    "        CType = list\n" .
    "        list_type = elearning_coursedetail\n" .
    "      }\n" .
    "    }\n" .
    "    elearning_lesson {\n" .
    "      iconIdentifier = elearning-plugin-lesson\n" .
    "      title = LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.lesson.title\n" .
    "      description = LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.lesson.description\n" .
    "      tt_content_defValues {\n" .
    "        CType = list\n" .
    "        list_type = elearning_lesson\n" .
    "      }\n" .
    "    }\n" .
    "    elearning_dashboard {\n" .
    "      iconIdentifier = elearning-plugin-dashboard\n" .
    "      title = LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.dashboard.title\n" .
    "      description = LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:plugin.dashboard.description\n" .
    "      tt_content_defValues {\n" .
    "        CType = list\n" .
    "        list_type = elearning_dashboard\n" .
    "      }\n" .
    "    }\n" .
    "  }\n" .
    "  show = *\n" .
    "}\n"
);

ExtensionManagementUtility::addPageTSConfig(
    "TCEFORM.tt_content.pi_flexform.elearning_courses.sDEF.settings\\.courseDetailPid.disabled = 0\n" .
    "TCEFORM.tt_content.pi_flexform.elearning_coursedetail.sDEF.settings\\.lessonPid.disabled = 0\n"
);
