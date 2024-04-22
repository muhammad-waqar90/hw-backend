<?php

namespace App\Repositories\IU;

use App\DataObject\QuizData;
use App\DataObject\QuizQuestionDifficultyData;
use App\Models\Quiz;
use App\Models\QuizItem;
use Illuminate\Support\Collection;

class IuQuizItemRepository
{
    private QuizItem $quizItem;

    public function __construct(QuizItem $quizItem)
    {
        $this->quizItem = $quizItem;
    }

    public function getQuestionsAnswersForQuiz($userId, Quiz $quiz)
    {
        $isMinor = IuUserProfileRepository::getIsMinor($userId);
        $quizItems = $this->getQuizItems($isMinor, $quiz);
        return $this->formatQuestionsAnswers($quizItems);
    }

    private function getQuizItems($isMinor, Quiz $quiz)
    {
        $numOfDifficultQuestions = (int) ($isMinor ? 1 / 4 * $quiz->num_of_questions : 3 / 4 * $quiz->num_of_questions);
        $numOfEasyQuestions = (int) ($isMinor ? 3 / 4 * $quiz->num_of_questions : 1 / 4 * $quiz->num_of_questions);

        $difficultQuizItemsQuery = $this->quizItem->where('quiz_id', $quiz->id)
            ->where('difficulty', QuizQuestionDifficultyData::DIFFICULT_INT)
            ->inRandomOrder()
            ->limit($numOfDifficultQuestions);
        $easyQuizItemsQuery = $this->quizItem->where('quiz_id', $quiz->id)
            ->where('difficulty', QuizQuestionDifficultyData::EASY_INT)
            ->inRandomOrder()
            ->limit($numOfEasyQuestions);

        $quizItems = $difficultQuizItemsQuery->union($easyQuizItemsQuery)->get();
        return $quizItems->shuffle();
    }

    private function formatQuestionsAnswers(Collection $quizItems)
    {
        $questionsAnswers = ['questions' => collect([]), 'answers' => collect([])];

        foreach($quizItems as $quizItem) {
            $questionsAnswers['questions'][] = $this->formatQuestion($quizItem);
            $questionsAnswers['answers']["$quizItem->uuid"] = $this->formatAnswer($quizItem);
        }

        return $questionsAnswers;
    }

    private function formatQuestion(QuizItem $quizItem)
    {
        $options = json_decode($quizItem->options);
        $formatted = collect([
            'id' => $quizItem->uuid,
            'type' => $quizItem->type,
            'question' => $quizItem->question,
            'options' => $this->formatOptions(collect($options))
        ]);
        if($quizItem->type === QuizData::QUESTION_MCQ_MULTIPLE)
            $formatted['maxChoices'] = $options->maxChoices;

        return $formatted;
    }

    private function formatAnswer(QuizItem $quizItem)
    {
        $answerId = json_decode($quizItem->answer)->answerId;
        $options = json_decode($quizItem->options);

        $formatted = collect([
                'type' => $quizItem->type,
                'answerId' => $answerId
        ]);

        if($quizItem->type === QuizData::QUESTION_MCQ_MULTIPLE)
            $formatted['maxChoices'] = $options->maxChoices;

        return $formatted;
    }

    private function formatOptions($options)
    {
        $formatted = [];
        if($options->has('list'))
            $formatted = collect($options['list'])->shuffle();
        if($options->has('leftSide')) {
            $formatted['leftSide'] = collect($options['leftSide'])->shuffle();
            $formatted['rightSide'] = collect($options['rightSide'])->shuffle();
        }

        return $formatted;
    }
}
