<?php

declare(strict_types=1);

defined('TYPO3') or die();

return [
    'ctrl' => [
        'title' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_question',
        'label' => 'question_text',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'hideTable' => true,
        'iconfile' => 'EXT:elearning/Resources/Public/Icons/tx_elearning_domain_model_question.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'lesson, question_text, answers',
        ],
    ],
    'columns' => [
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
            ],
        ],
        'lesson' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_question.lesson',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_elearning_domain_model_lesson',
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'question_text' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_question.question_text',
            'config' => [
                'type' => 'text',
                'rows' => 3,
                'eval' => 'trim,required',
            ],
        ],
        'answers' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_question.answers',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_elearning_domain_model_answer',
                'foreign_field' => 'question',
                'appearance' => [
                    'collapseAll' => true,
                    'levelLinksPosition' => 'top',
                    'newRecordLinkAddTitle' => true,
                    'useSortable' => true,
                ],
            ],
        ],
    ],
];
