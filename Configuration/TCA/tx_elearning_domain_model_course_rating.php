<?php

declare(strict_types=1);

defined('TYPO3') or die();

return [
    'ctrl' => [
        'title' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_course_rating',
        'label' => 'uid',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'hideTable' => true,
        'iconfile' => 'EXT:elearning/Resources/Public/Icons/tx_elearning_domain_model_course.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'fe_user, course, rating, tstamp',
        ],
    ],
    'columns' => [
        'fe_user' => [
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_course_rating.fe_user',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
            ],
        ],
        'course' => [
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_course_rating.course',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_elearning_domain_model_course',
                'readOnly' => true,
            ],
        ],
        'rating' => [
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_course_rating.rating',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
            ],
        ],
    ],
];
