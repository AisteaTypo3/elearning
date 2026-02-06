<?php

declare(strict_types=1);

return [
    'elearning_analytics' => [
        'parent' => 'web',
        'position' => ['after' => 'list'],
        'access' => 'user,group',
        'path' => '/module/elearning/analytics',
        'icon' => 'EXT:elearning/Resources/Public/Icons/plugin_dashboard.svg',
        'labels' => 'LLL:EXT:elearning/Resources/Private/Language/locallang_be.xlf:module.analytics',
        'extensionName' => 'Elearning',
        'controllerActions' => [
            \Aistea\Elearning\Controller\Backend\AnalyticsController::class => [
                'index',
            ],
        ],
    ],
];
