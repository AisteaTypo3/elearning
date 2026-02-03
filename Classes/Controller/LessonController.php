<?php

declare(strict_types=1);

namespace Vendor\Elearning\Controller;

use Vendor\Elearning\Domain\Model\Lesson;
use Vendor\Elearning\Domain\Model\Question;
use Vendor\Elearning\Domain\Repository\LessonRepository;
use Vendor\Elearning\Service\ProgressService;
use TYPO3\CMS\Core\Error\Http\PageNotFoundException;
use TYPO3\CMS\Core\Http\JsonResponse;

class LessonController extends AbstractFrontendController
{
    public function __construct(
        private readonly LessonRepository $lessonRepository,
        private readonly ProgressService $progressService,
        \TYPO3\CMS\Core\Context\Context $context
    ) {
        parent::__construct($context);
    }

    public function showAction(Lesson $lesson): \Psr\Http\Message\ResponseInterface
    {
        if (!$lesson->isPublished()) {
            throw new PageNotFoundException($this->translate('errors.lesson_not_published'), 1738564032);
        }

        $feUserId = $this->getFrontendUserId();
        $progress = $this->progressService->recordVisit($feUserId, $lesson);
        $hasQuiz = $lesson->getQuestions()->count() > 0;
        $quizPassed = $progress->isQuizPassed();
        $isCompleted = $hasQuiz ? $quizPassed : $progress->isCompleted();
        $showMarkComplete = !$hasQuiz && !$progress->isCompleted();

        $course = $lesson->getCourse();
        $nextLesson = null;
        $previousLesson = null;
        $lessonList = [];
        $completedLessonUids = [];
        $courseProgress = [
            'total' => 0,
            'completed' => 0,
            'percent' => 0,
        ];
        if ($course !== null) {
            $orderedLessons = $this->lessonRepository->findPublishedByCourse($course);
            foreach ($orderedLessons as $orderedLesson) {
                $lessonList[] = $orderedLesson;
            }
            $currentIndex = null;
            foreach ($lessonList as $index => $item) {
                if ($item->getUid() === $lesson->getUid()) {
                    $currentIndex = $index;
                    break;
                }
            }
            if ($currentIndex !== null) {
                $previousLesson = $lessonList[$currentIndex - 1] ?? null;
                $nextLesson = $lessonList[$currentIndex + 1] ?? null;
            }

            $lessonUids = [];
            foreach ($lessonList as $item) {
                $lessonUids[] = $item->getUid();
            }
            $completedLessonUids = $this->progressService->getCompletedLessonUids($feUserId, $lessonUids);
            $total = count($lessonUids);
            $completed = count($completedLessonUids);
            $courseProgress = [
                'total' => $total,
                'completed' => $completed,
                'percent' => $total > 0 ? (int)round(($completed / $total) * 100) : 0,
            ];
        }

        $this->view->assignMultiple([
            'lesson' => $lesson,
            'progress' => $progress,
            'nextLesson' => $nextLesson,
            'previousLesson' => $previousLesson,
            'lessonPid' => $this->getConfiguredPid('lessonPid'),
            'videoEmbedUrl' => $this->buildVideoEmbedUrl($lesson->getVideoUrl()),
            'hasQuiz' => $hasQuiz,
            'isCompleted' => $isCompleted,
            'quizPassed' => $quizPassed,
            'showMarkComplete' => $showMarkComplete,
            'courseDetailPid' => $this->getConfiguredPid('courseDetailPid'),
            'course' => $course,
            'lessonList' => $lessonList,
            'completedLessonUids' => $completedLessonUids,
            'courseProgress' => $courseProgress,
        ]);

        return $this->htmlResponse();
    }

    public function markCompleteAction(Lesson $lesson): \Psr\Http\Message\ResponseInterface
    {
        if (!$lesson->isPublished()) {
            throw new PageNotFoundException($this->translate('errors.lesson_not_published'), 1738564039);
        }

        if ($lesson->getQuestions()->count() > 0) {
            $this->addFlashMessage($this->translate('messages.quiz_required'), '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::WARNING);
            $pageUid = $this->getConfiguredPid('lessonPid');
            return $this->redirect('show', 'Lesson', null, ['lesson' => $lesson], $pageUid);
        }

        $feUserId = $this->getFrontendUserId();
        $this->progressService->markCompleted($feUserId, $lesson);

        $pageUid = $this->getConfiguredPid('lessonPid');
        return $this->redirect('show', 'Lesson', null, ['lesson' => $lesson], $pageUid);
    }

    public function submitQuizAction(Lesson $lesson, array $answers = []): \Psr\Http\Message\ResponseInterface
    {
        if (!$lesson->isPublished()) {
            throw new PageNotFoundException($this->translate('errors.lesson_not_published'), 1738564049);
        }

        $feUserId = $this->getFrontendUserId();
        $progress = $this->progressService->getProgressForLesson($feUserId, $lesson);
        if ($progress !== null) {
            $lastFailedAt = $progress->getLastQuizFailedAt();
            $lastVisitedAt = $progress->getLastVisitedAt();
            if ($lastFailedAt !== null && $lastVisitedAt !== null && $lastVisitedAt <= $lastFailedAt) {
                $this->addFlashMessage($this->translate('messages.quiz_revisit_required'), '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::WARNING);
                $pageUid = $this->getConfiguredPid('lessonPid');
                return $this->redirect('show', 'Lesson', null, ['lesson' => $lesson], $pageUid);
            }
        }

        $allCorrect = $this->evaluateQuiz($lesson, $answers);
        if ($allCorrect) {
            $this->progressService->markQuizPassed($feUserId, $lesson);
            $this->addFlashMessage($this->translate('messages.quiz_passed'));
        } else {
            $this->progressService->markQuizFailed($feUserId, $lesson);
            $this->addFlashMessage($this->translate('messages.quiz_failed'), '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
        }

        $pageUid = $this->getConfiguredPid('lessonPid');
        return $this->redirect('show', 'Lesson', null, ['lesson' => $lesson], $pageUid);
    }

    public function markVideoCompletedAction(Lesson $lesson): \Psr\Http\Message\ResponseInterface
    {
        if (!$lesson->isPublished()) {
            throw new PageNotFoundException($this->translate('errors.lesson_not_published'), 1738564059);
        }

        if ($lesson->getQuestions()->count() > 0) {
            return new JsonResponse(['ok' => false, 'reason' => 'quiz_required'], 409);
        }

        $feUserId = $this->getFrontendUserId();
        $this->progressService->markCompleted($feUserId, $lesson);

        return new JsonResponse(['ok' => true]);
    }

    private function evaluateQuiz(Lesson $lesson, array $answers): bool
    {
        $questions = $lesson->getQuestions();
        if ($questions->count() === 0) {
            return true;
        }

        foreach ($questions as $question) {
            if (!$question instanceof Question) {
                return false;
            }
            $selectedAnswerUid = (int)($answers[$question->getUid()] ?? 0);
            if ($selectedAnswerUid === 0) {
                return false;
            }

            $correct = false;
            foreach ($question->getAnswers() as $answer) {
                if ($answer->isCorrect() && $answer->getUid() === $selectedAnswerUid) {
                    $correct = true;
                    break;
                }
            }

            if (!$correct) {
                return false;
            }
        }

        return true;
    }

    private function buildVideoEmbedUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (preg_match('~youtu\\.be/([\\w-]+)~', $url, $matches) === 1) {
            return 'https://www.youtube.com/embed/' . $matches[1] . '?enablejsapi=1&rel=0';
        }
        if (preg_match('~youtube\\.com/watch\\?v=([\\w-]+)~', $url, $matches) === 1) {
            return 'https://www.youtube.com/embed/' . $matches[1] . '?enablejsapi=1&rel=0';
        }
        if (preg_match('~vimeo\\.com/(\\d+)~', $url, $matches) === 1) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }

        return '';
    }
}
