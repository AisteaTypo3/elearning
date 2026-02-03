<?php

declare(strict_types=1);

defined('TYPO3') or die();

return [
    'ctrl' => [
        'title' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_progress',
        'label' => 'uid',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'hideTable' => true,
        'iconfile' => 'EXT:elearning/Resources/Public/Icons/tx_elearning_domain_model_progress.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'fe_user, lesson, completed, completed_at, quiz_passed, quiz_passed_at, last_quiz_failed_at, last_visited_at',
        ],
    ],
    'columns' => [
        'fe_user' => [
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_progress.fe_user',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
            ],
        ],
        'lesson' => [
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_progress.lesson',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_elearning_domain_model_lesson',
                'readOnly' => true,
            ],
        ],
        'completed' => [
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_progress.completed',
            'config' => [
                'type' => 'check',
                'readOnly' => true,
            ],
        ],
        'completed_at' => [
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_progress.completed_at',
            'config' => [
                'type' => 'datetime',
                'readOnly' => true,
            ],
        ],
        'quiz_passed' => [
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_progress.quiz_passed',
            'config' => [
                'type' => 'check',
                'readOnly' => true,
            ],
        ],
        'quiz_passed_at' => [
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_progress.quiz_passed_at',
            'config' => [
                'type' => 'datetime',
                'readOnly' => true,
            ],
        ],
        'last_quiz_failed_at' => [
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_progress.last_quiz_failed_at',
            'config' => [
                'type' => 'datetime',
                'readOnly' => true,
            ],
        ],
        'last_visited_at' => [
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_progress.last_visited_at',
            'config' => [
                'type' => 'datetime',
                'readOnly' => true,
            ],
        ],
    ],
];
