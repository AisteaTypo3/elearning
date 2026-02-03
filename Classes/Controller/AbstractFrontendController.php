<?php

declare(strict_types=1);

namespace Vendor\Elearning\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

abstract class AbstractFrontendController extends ActionController
{
    private const MAX_FE_USER_ID = 4294967295;

    public function __construct(
        private readonly Context $context
    ) {
    }

    protected function initializeAction(): void
    {
        parent::initializeAction();
        $this->requireFrontendUser();
    }

    protected function initializeView(ViewInterface $view): void
    {
        $view->assignMultiple([
            'coursesPid' => $this->getConfiguredPid('coursesPid'),
            'dashboardPid' => $this->getConfiguredPid('dashboardPid'),
            'logoutPid' => $this->getConfiguredPid('logoutPid'),
        ]);
    }

    protected function requireFrontendUser(): void
    {
        $aspect = $this->context->getAspect('frontend.user');
        if (!$aspect->isLoggedIn()) {
            $response = new HtmlResponse($this->translate('messages.access_denied'), 403);
            throw new ImmediateResponseException($response);
        }
    }

    protected function getFrontendUserId(): int
    {
        $aspect = $this->context->getAspect('frontend.user');
        if (!$aspect->isLoggedIn()) {
            return 0;
        }

        $userId = (int)$aspect->get('id');
        if ($userId <= 0 || $userId > self::MAX_FE_USER_ID) {
            return 0;
        }

        return $userId;
    }

    protected function getConfiguredPid(string $key): int
    {
        $pid = (int)($this->settings[$key] ?? 0);
        if ($pid > 0) {
            return $pid;
        }

        $pid = (int)$this->getSiteSetting('elearning.' . $key, 0);
        return $pid > 0 ? $pid : 0;
    }

    protected function getSiteSetting(string $path, mixed $default): mixed
    {
        $site = $this->request->getAttribute('site');
        if (!$site instanceof Site) {
            return $default;
        }

        $settings = $site->getConfiguration()['settings'] ?? [];
        $value = $settings;
        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    protected function getFrontendUserProfile(): array
    {
        $user = [];
        $feUser = $this->request->getAttribute('frontend.user');
        if ($feUser instanceof FrontendUserAuthentication) {
            $user = (array)($feUser->user ?? []);
        }
        if (isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE']) && isset($GLOBALS['TSFE']->fe_user)) {
            $user = $user ?: (array)($GLOBALS['TSFE']->fe_user->user ?? []);
        }

        $first = trim((string)($user['first_name'] ?? ''));
        $last = trim((string)($user['last_name'] ?? ''));
        $name = trim($first . ' ' . $last);
        if ($name === '') {
            $name = trim((string)($user['name'] ?? ''));
        }
        if ($name === '') {
            $name = trim((string)($user['username'] ?? ''));
        }

        return [
            'name' => $name,
            'username' => (string)($user['username'] ?? ''),
            'email' => (string)($user['email'] ?? ''),
        ];
    }

    protected function translate(string $key, array $arguments = []): string
    {
        return (string)(LocalizationUtility::translate($key, 'Elearning', $arguments) ?? $key);
    }
}
