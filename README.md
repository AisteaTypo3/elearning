# Elearning (TYPO3 v13)

Modern Extbase/Fluid extension for TYPO3 v13.4+ that provides a course catalog, course detail view, lesson view with next/previous navigation, quizzes, and a per‑user dashboard. Frontend login is required (defense in depth), and pages should still be protected by TYPO3 page access.

## Features
- Course catalog (published courses)
- Course detail with published lessons
- Lesson view with type‑specific content and next/previous navigation
- Optional quiz per lesson (single‑choice per question)
- Quiz retry rule: after a failed attempt, the learner must revisit the lesson before retrying the quiz
- Dashboard with completion percentage per course
- Progress tracking per frontend user (completed, quiz status, last visited)
- Apple‑style UI theme + Netflix‑style course grid
- Full i18n: EN + DE translations for all UI texts

## Requirements
- TYPO3 v13.4+
- PHP version supported by TYPO3 v13 (check your TYPO3 installation requirements)

## Installation (Composer)
1) Ensure your root `composer.json` contains the local packages repository:
```json
{
  "repositories": [
    {"type": "path", "url": "packages/*", "options": {"symlink": true}}
  ]
}
```
2) Require the extension:
```
composer require vendor/elearning:@dev
```
3) Flush caches and run the database schema update.

## TYPO3 Backend Setup
1) Create a frontend user group `Learners`.
2) Create pages:
   - **Login** page with `felogin`
   - **Learning** (restricted to `Learners`)
   - Subpages under *Learning*:
     - **Courses** (plugin: Courses)
     - **Course Detail** (plugin: Course Detail)
     - **Lesson** (plugin: Lesson View)
     - **Dashboard** (plugin: Dashboard)
3) Place plugins on those pages.
4) Configure FlexForms:
   - Courses plugin: set **Course detail page** to the Course Detail page
   - Course Detail plugin: set **Lesson page** to the Lesson page

## Editor Guide
- Create **Courses** and **Lessons** in the list module on a storage page.
- Set **Published** to make records visible in the frontend.
- Lesson types:
  - `content`: use the RTE content field
  - `video`: set a YouTube or Vimeo URL (auto‑embedded) or any other URL (shown as link)
  - `file`: attach a file (FAL)
  - `link`: set an external link URL
- Quiz:
  - Add quiz questions inside a lesson (tab “Quiz”)
  - Each question has multiple answers; mark the correct one
  - Lesson is marked completed only after passing the quiz
  - If a quiz attempt fails, the learner must open the lesson again before retrying

## Database Notes
- Progress is stored in `tx_elearning_domain_model_progress`
- New field: `last_quiz_failed_at` (tracks failed quiz attempts for retry rule)
- Run the DB compare after updating the extension

## Localization (EN/DE)
All UI text is translated:
- `Resources/Private/Language/locallang.xlf` (default language, EN by default)
- `Resources/Private/Language/locallang.de.xlf`

Important: Language ID 0 uses `locallang.xlf`.  
If your default language is German, put German strings into `locallang.xlf` and use `locallang.en.xlf` for English.

## Notes on Security & Caching
- The extension enforces a login check on every action and returns a 403 if not logged in.
- You should still protect learning pages with TYPO3 page access permissions (FE group).
- For user‑specific progress, keep these plugins uncached (default behavior in this setup).

## Styling
- Stylesheet: `EXT:elearning/Resources/Public/Css/elearning.css`
- Auto‑included via TypoScript (`page.includeCSS.elearning`)
- The theme is Apple‑style (light, clean, blue accents) with modern cards

## Speaking URLs (Route Enhancers)
Add these route enhancers in your site configuration (`config/sites/<site>/config.yaml`) to enable slugs:
```yaml
routeEnhancers:
  ElearningCourseDetail:
    type: Extbase
    extension: Elearning
    plugin: CourseDetail
    routes:
      - routePath: '/courses/{course}'
        _controller: 'Course::show'
        _arguments:
          course: course
    defaultController: 'Course::show'
    aspects:
      course:
        type: PersistedAliasMapper
        tableName: tx_elearning_domain_model_course
        routeFieldName: slug
  ElearningLesson:
    type: Extbase
    extension: Elearning
    plugin: Lesson
    routes:
      - routePath: '/{lesson}'
        _controller: 'Lesson::show'
        _arguments:
          lesson: lesson
    defaultController: 'Lesson::show'
    aspects:
      lesson:
        type: PersistedAliasMapper
        tableName: tx_elearning_domain_model_lesson
        routeFieldName: slug
```

Note: If your lesson page slug is `/lesson`, the route path should be `/{lesson}` so you get `/lesson/<slug>` without `/lesson/lesson/...`.

## Roadmap Ideas
- Enrollment & access control per course
- Certificates and completion rules
- Multi‑choice or graded quizzes
