<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\EmotionLog;
use App\Models\User;
use App\Services\QuizGeneratorService;
use App\Services\GamificationService;

class QuizController extends Controller
{
    public function index(): void
    {
        $userId = $this->requireAuth();
        $quizzes = Quiz::byUser($userId);
        $lastMood = EmotionLog::lastByUser($userId);

        $this->view('quiz.index', compact('quizzes', 'lastMood'));
    }

    public function generate(): void
    {
        $userId = $this->requireAuth();

        if (!Session::verifyCsrf()) {
            Session::flash('error', 'Invalid form submission.');
            $this->redirect('/quiz');
        }

        $moodContext = trim($this->post('mood_context', 'general'));

        // Use GPT to generate quiz
        $generator = new QuizGeneratorService($this->config);
        $quizData = $generator->generate($moodContext);

        if (empty($quizData['questions'])) {
            Session::flash('error', 'Failed to generate quiz. Please try again.');
            $this->redirect('/quiz');
        }

        $quiz = new Quiz([
            'user_id'        => $userId,
            'title'          => $quizData['title'] ?? "EmoEat Quiz — {$moodContext}",
            'mood_context'   => $moodContext,
            'questions_json' => json_encode($quizData['questions']),
        ]);
        $quiz->save();

        $this->redirect('/quiz/take/' . $quiz->id);
    }

    public function take(string $id): void
    {
        $userId = $this->requireAuth();
        $quiz = Quiz::find((int)$id);

        if (!$quiz || (int)$quiz->user_id !== $userId) {
            $this->redirect('/quiz');
        }

        $questions = $quiz->getQuestions();
        $this->view('quiz.take', compact('quiz', 'questions'));
    }

    public function submit(): void
    {
        $userId = $this->requireAuth();

        if (!Session::verifyCsrf()) {
            Session::flash('error', 'Invalid form submission.');
            $this->redirect('/quiz');
        }

        $quizId = (int)$this->post('quiz_id', 0);
        $quiz = Quiz::find($quizId);

        if (!$quiz || (int)$quiz->user_id !== $userId) {
            $this->redirect('/quiz');
        }

        $questions = $quiz->getQuestions();
        $answers = $this->post('answers', []);
        $score = 0;
        $maxScore = count($questions);

        foreach ($questions as $i => $q) {
            $userAnswer = $answers[$i] ?? '';
            if (strtolower(trim($userAnswer)) === strtolower(trim($q['correct'] ?? ''))) {
                $score++;
            }
        }

        $xpEarned = $this->config['gamification']['xp_per_quiz'];
        if ($score === $maxScore && $maxScore > 0) {
            $xpEarned *= 2; // Bonus for perfect score
        }

        // Generate feedback via LLM
        $generator = new QuizGeneratorService($this->config);
        $feedback = $generator->generateFeedback($questions, $answers, $score, $maxScore);

        $result = new QuizResult([
            'quiz_id'      => $quizId,
            'user_id'      => $userId,
            'answers_json' => json_encode($answers),
            'score'        => $score,
            'max_score'    => $maxScore,
            'xp_earned'    => $xpEarned,
            'feedback'     => $feedback,
        ]);
        $result->save();

        // Award XP
        $user = User::find($userId);
        $user->addXp($xpEarned);

        // Check badges
        $gamification = new GamificationService();
        $gamification->checkBadges($userId);

        $this->redirect('/quiz/result/' . $result->id);
    }

    public function result(string $id): void
    {
        $userId = $this->requireAuth();
        $result = QuizResult::find((int)$id);

        if (!$result || (int)$result->user_id !== $userId) {
            $this->redirect('/quiz');
        }

        $quiz = Quiz::find((int)$result->quiz_id);
        $questions = $quiz ? $quiz->getQuestions() : [];
        $answers = json_decode($result->answers_json, true) ?: [];

        $this->view('quiz.result', compact('result', 'quiz', 'questions', 'answers'));
    }
}
