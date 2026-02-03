<?php

declare(strict_types=1);

defined('TYPO3') or die();

return [
    'ctrl' => [
        'title' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_answer',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'hideTable' => true,
        'iconfile' => 'EXT:elearning/Resources/Public/Icons/tx_elearning_domain_model_answer.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'question, title, is_correct',
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
        'question' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_answer.question',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_elearning_domain_model_question',
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_answer.title',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'eval' => 'trim,required',
            ],
        ],
        'is_correct' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_answer.is_correct',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
            ],
        ],
    ],
];
