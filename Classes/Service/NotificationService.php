<?php

declare(strict_types=1);

namespace Vendor\Elearning\Service;

use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use Vendor\Elearning\Domain\Model\Lesson;

final class NotificationService
{
    public function sendLessonCompleted(int $feUserId, Lesson $lesson, array $courseProgress, array $settings = []): void
    {
        if (!$this->isEnabled($settings, 'enabled') || !$this->isEnabled($settings, 'progressOnLessonComplete')) {
            return;
        }

        $user = $this->fetchFrontendUser($feUserId);
        if ($user === null) {
            return;
        }

        $courseTitle = $lesson->getCourse()?->getTitle() ?? '';
        $subject = $this->translate('email.subject.lesson_completed', [$lesson->getTitle()]);
        $textBody = $this->translate(
            'email.body.lesson_completed',
            [$user['name'], $lesson->getTitle(), $courseTitle, (string)($courseProgress['percent'] ?? 0)]
        );
        $htmlBody = $this->renderTemplate('LessonCompleted', [
            'subject' => $subject,
            'userName' => $user['name'],
            'lessonTitle' => $lesson->getTitle(),
            'courseTitle' => $courseTitle,
            'progressPercent' => (int)($courseProgress['percent'] ?? 0),
        ]);
        if ($this->sendMail($user, $subject, $textBody, $settings, $htmlBody)) {
            if (($courseProgress['percent'] ?? 0) >= 100 && $this->isEnabled($settings, 'courseComplete')) {
                $courseSubject = $this->translate('email.subject.course_completed', [$courseTitle]);
                $courseText = $this->translate('email.body.course_completed', [$user['name'], $courseTitle]);
                $courseHtml = $this->renderTemplate('CourseCompleted', [
                    'subject' => $courseSubject,
                    'userName' => $user['name'],
                    'courseTitle' => $courseTitle,
                ]);
                $this->sendMail($user, $courseSubject, $courseText, $settings, $courseHtml);
            }
        }
    }

    public function sendReminder(array $user, int $pendingCount, array $settings = []): bool
    {
        if (!$this->isEnabled($settings, 'enabled') || !$this->isEnabled($settings, 'reminders.enabled')) {
            return false;
        }

        $email = trim((string)($user['email'] ?? ''));
        if ($email === '') {
            return false;
        }

        $name = $this->buildUserName($user);
        $subject = $this->translate('email.subject.reminder');
        $textBody = $this->translate('email.body.reminder', [$name, (string)$pendingCount]);
        $htmlBody = $this->renderTemplate('Reminder', [
            'subject' => $subject,
            'userName' => $name,
            'pendingCount' => $pendingCount,
        ]);

        return $this->sendMail(['email' => $email, 'name' => $name], $subject, $textBody, $settings, $htmlBody);
    }

    private function sendMail(array $user, string $subject, string $textBody, array $settings = [], ?string $htmlBody = null): bool
    {
        $from = $this->getFromAddress($settings);
        if ($from === null) {
            return false;
        }

        $email = trim((string)($user['email'] ?? ''));
        if ($email === '') {
            return false;
        }

        $name = trim((string)($user['name'] ?? ''));
        $message = GeneralUtility::makeInstance(MailMessage::class);
        $message->from($from);
        $message->to(new Address($email, $name !== '' ? $name : $email));
        $message->subject($subject);
        $message->text($textBody);
        if (is_string($htmlBody) && trim($htmlBody) !== '') {
            $message->html($htmlBody);
        }
        $message->send();

        return true;
    }

    private function getFromAddress(array $settings): ?Address
    {
        $email = trim((string)($settings['fromEmail'] ?? ''));
        $name = trim((string)($settings['fromName'] ?? ''));
        if ($email === '') {
            $email = trim((string)($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] ?? ''));
        }
        if ($name === '') {
            $name = trim((string)($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] ?? ''));
        }

        if ($email === '') {
            return null;
        }

        return new Address($email, $name !== '' ? $name : $email);
    }

    private function isEnabled(array $settings, string $key): bool
    {
        $segments = explode('.', $key);
        $value = $settings;
        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return false;
            }
            $value = $value[$segment];
        }

        if (is_string($value)) {
            $value = strtolower($value);
            return $value === '1' || $value === 'true' || $value === 'yes' || $value === 'on';
        }

        return (bool)$value;
    }

    private function fetchFrontendUser(int $feUserId): ?array
    {
        if ($feUserId <= 0) {
            return null;
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
        $row = $queryBuilder
            ->select('uid', 'email', 'first_name', 'last_name', 'name', 'username')
            ->from('fe_users')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($feUserId)),
                $queryBuilder->expr()->eq('disable', 0),
                $queryBuilder->expr()->eq('deleted', 0)
            )
            ->executeQuery()
            ->fetchAssociative();

        if (!$row) {
            return null;
        }

        $name = $this->buildUserName($row);
        return [
            'uid' => (int)$row['uid'],
            'email' => (string)($row['email'] ?? ''),
            'name' => $name,
        ];
    }

    private function buildUserName(array $row): string
    {
        $first = trim((string)($row['first_name'] ?? ''));
        $last = trim((string)($row['last_name'] ?? ''));
        $name = trim($first . ' ' . $last);
        if ($name === '') {
            $name = trim((string)($row['name'] ?? ''));
        }
        if ($name === '') {
            $name = trim((string)($row['username'] ?? ''));
        }

        return $name;
    }

    private function translate(string $key, array $arguments = []): string
    {
        return (string)(LocalizationUtility::translate($key, 'Elearning', $arguments) ?? $key);
    }

    private function renderTemplate(string $templateName, array $variables): ?string
    {
        $path = GeneralUtility::getFileAbsFileName(
            'EXT:elearning/Resources/Private/Templates/Email/' . $templateName . '.html'
        );
        if (!is_string($path) || $path === '' || !is_file($path)) {
            return null;
        }

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename($path);
        $view->assignMultiple($variables);

        try {
            return $view->render();
        } catch (\Throwable) {
            return null;
        }
    }
}
