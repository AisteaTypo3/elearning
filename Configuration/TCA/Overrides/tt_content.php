<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

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
