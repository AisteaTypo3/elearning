<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'E-Learning',
    'description' => 'Simple e-learning extension for TYPO3 v13',
    'category' => 'plugin',
    'author' => 'Aistea',
    'author_email' => '',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.99.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
