<?php

declare(strict_types=1);

defined('TYPO3') or die();

return [
    'ctrl' => [
        'title' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_lesson',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'title,content,video_url,link_url',
        'iconfile' => 'EXT:elearning/Resources/Public/Icons/tx_elearning_domain_model_lesson.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => '
                course, title, slug, type, duration_minutes, published,
                --div--;Content,
                content, video_url, file, link_url,
                --div--;Quiz,
                questions,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                hidden, starttime, endtime
            '
        ],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_elearning_domain_model_lesson',
                'foreign_table_where' => 'AND {#tx_elearning_domain_model_lesson}.{#pid}=###CURRENT_PID### AND {#tx_elearning_domain_model_lesson}.{#sys_language_uid} IN (-1,0)',
                'default' => 0,
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
            ],
        ],
        'course' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_lesson.course',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_elearning_domain_model_course',
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_lesson.title',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim,required',
            ],
        ],
        'slug' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_lesson.slug',
            'config' => [
                'type' => 'slug',
                'size' => 50,
                'generatorOptions' => [
                    'fields' => ['title'],
                    'fieldSeparator' => '-',
                    'replacements' => [
                        '/' => '-',
                    ],
                ],
                'fallbackCharacter' => '-',
                'eval' => 'uniqueInSite',
            ],
        ],
        'content' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_lesson.content',
            'displayCond' => 'FIELD:type:=:content',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
            ],
        ],
        'type' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_lesson.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'onChange' => 'reload',
                'items' => [
                    ['Content', 'content'],
                    ['Video', 'video'],
                    ['File', 'file'],
                    ['Link', 'link'],
                ],
                'default' => 'content',
            ],
        ],
        'video_url' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_lesson.video_url',
            'displayCond' => 'FIELD:type:=:video',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
        'file' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_lesson.file',
            'displayCond' => 'FIELD:type:IN:video,file',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'sys_file_reference',
                'foreign_field' => 'uid_foreign',
                'foreign_table_field' => 'tablenames',
                'foreign_match_fields' => [
                    'fieldname' => 'file',
                    'tablenames' => 'tx_elearning_domain_model_lesson',
                ],
                'foreign_sortby' => 'sorting_foreign',
                'appearance' => [
                    'createNewRelationLinkTitle' => 'LLL:EXT:core/Resources/Private/Language/locallang_ttc.xlf:media.addFileReference',
                    'collapseAll' => true,
                    'showPossibleLocalizationRecords' => true,
                ],
                'overrideChildTca' => [
                    'types' => [
                        '0' => [
                            'showitem' => '--palette--;;filePalette',
                        ],
                    ],
                ],
                'maxitems' => 1,
            ],
        ],
        'link_url' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_lesson.link_url',
            'displayCond' => 'FIELD:type:=:link',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
        'duration_minutes' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_lesson.duration_minutes',
            'config' => [
                'type' => 'number',
                'size' => 4,
                'default' => 0,
                'range' => [
                    'lower' => 0,
                ],
            ],
        ],
        'published' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_lesson.published',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                    ],
                ],
            ],
        ],
        'questions' => [
            'exclude' => true,
            'label' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_db.xlf:tx_elearning_domain_model_lesson.questions',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_elearning_domain_model_question',
                'foreign_field' => 'lesson',
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
