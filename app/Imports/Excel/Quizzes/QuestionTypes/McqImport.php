<?php

namespace App\Imports\Excel\Quizzes\QuestionTypes;

use App\DataObject\QuizData;
use App\DataObject\QuizQuestionDifficultyData;
use App\Exceptions\Quizzes\Imports\McqImportException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class McqImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    use Importable;

    private Collection $collection;

    private string $fileName;

    private int $row = 2;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @throws McqImportException
     */
    public function collection(Collection $collection): Collection
    {
        $validator = Validator::make($collection->toArray(), $this->rules());
        if ($validator->fails()) {
            throw new McqImportException('Data is invalid', 101, 0, $this->fileName, $validator->messages()->get('*'));
        }

        $this->collection = $this->parseQuestionList($collection);

        return $this->collection;
    }

    /**
     * @throws McqImportException
     */
    public function parseQuestionList($collection): Collection
    {
        $parsedQuestions = [];
        foreach ($collection as $item) {
            $parsedQuestions[] = $this->parseQuestion($item);
            $this->row++;
        }

        return collect($parsedQuestions);
    }

    /**
     * @throws McqImportException
     */
    public function parseQuestion($item): Collection
    {
        $item = $item->trimValues();
        $id = Str::orderedUuid()->toString();
        $numOfAnswers = $this->getNumOfAnswers($item);
        $numOfOptions = $this->getNumOfOptions($item);
        if ($numOfAnswers >= $numOfOptions) {
            throw new McqImportException('Number of answers exceeds or is the same as number of options', 100, $this->row, $this->fileName);
        }

        $type = $numOfAnswers === 1 ? QuizData::QUESTION_MCQ_SINGLE : QuizData::QUESTION_MCQ_MULTIPLE;
        $options = $this->getOptions($item, $type, $numOfAnswers);

        $this->validateQuestion($options, $item);

        return collect(
            $this->mapQuestion($id, $type, $options, $numOfAnswers, $item)
        );
    }

    public function mapMcqSingleAnswer($options, $item): Collection
    {
        $correctAnswer = $options['list']->first(function ($option) use ($item) {
            return $option['value'] === $item['correct_answer_1'];
        });

        return collect([
            'answerId' => $correctAnswer['id'],
        ]);
    }

    public function mapMcqMultipleAnswer($options, $numOfAnswers, $item): Collection
    {
        $correctAnswers = [];
        for ($i = 0; $i < 3; $i++) {
            if (strlen($item['correct_answer_'.$i + 1])) {
                $correctAnswers[] = $item['correct_answer_'.$i + 1];
            }
        }

        $correctAnswer = $options['list']->filter(function ($option) use ($correctAnswers) {
            return in_array($option['value'], $correctAnswers);
        });

        return collect([
            'answerId' => $correctAnswer->map(function ($item) {
                return $item['id'];
            })->values(),
            'maxChoices' => $numOfAnswers,
        ]);
    }

    public function mapQuestion($id, $type, $options, $numOfAnswers, $item): Collection
    {
        return collect([
            'uuid' => $id,
            'question' => $item['question'],
            'difficulty' => QuizQuestionDifficultyData::difficultyStringToInt($item['difficulty']),
            'type' => $type,
            'options' => $options->toJson(),
            'answer' => $type === QuizData::QUESTION_MCQ_SINGLE ?
                $this->mapMcqSingleAnswer($options, $item)->toJson() :
                $this->mapMcqMultipleAnswer($options, $numOfAnswers, $item)->toJson(),
        ]);
    }

    public function getOptions($item, $type, $numOfAnswers): Collection
    {
        $options = [];
        $list = [];
        for ($i = 0; $i < 4; $i++) {
            if (strlen($item['answer_'.$i + 1])) {
                $list[] = [
                    'id' => Str::orderedUuid()->toString(),
                    'value' => $item['answer_'.$i + 1],
                ];
            }
        }

        $options['list'] = collect($list);
        if ($type === QuizData::QUESTION_MCQ_MULTIPLE) {
            $options['maxChoices'] = $numOfAnswers;
        }

        return collect($options);
    }

    /**
     * @throws McqImportException
     */
    public function validateQuestion($options, $question)
    {
        if (! $this->hasUniqueOptions($question)) {
            throw new McqImportException('Found duplicate options', 100, $this->row, $this->fileName);
        }
        if (! $this->hasUniqueAnswers($question)) {
            throw new McqImportException('Found duplicate answers', 100, $this->row, $this->fileName);
        }
        for ($i = 1; $i < 4; $i++) {
            if ($question["correct_answer_$i"]) {
                //Check if answer is NOT found in the options
                if (! $options['list']->contains(function ($item) use ($question, $i) {
                    return $item['value'] === $question["correct_answer_$i"];
                })) {
                    throw new McqImportException('Correct answer not found in the options', 100, $this->row, $this->fileName);
                }
            }
        }
    }

    public function hasUniqueOptions($question): bool
    {
        $options = [];
        for ($i = 0; $i < 4; $i++) {
            if (strlen($question['answer_'.$i + 1])) {
                $options[] = $question['answer_'.$i + 1];
            }
        }

        return count($options) === count(array_unique($options));
    }

    public function hasUniqueAnswers($question)
    {
        $options = [];
        for ($i = 0; $i < 3; $i++) {
            if (strlen($question['correct_answer_'.$i + 1])) {
                $options[] = $question['correct_answer_'.$i + 1];
            }
        }

        return count($options) === count(array_unique($options));
    }

    public function getNumOfAnswers($item): int
    {
        $answersSum = 0;
        for ($i = 1; $i < 4; $i++) {
            $answersSum += (bool) strlen($item["correct_answer_$i"]);
        }

        return $answersSum;
    }

    public function getNumOfOptions($item): int
    {
        $optionsSum = 0;
        for ($i = 1; $i < 5; $i++) {
            $optionsSum += (bool) strlen($item["answer_$i"]);
        }

        return $optionsSum;
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    public function rules(): array
    {
        return [
            '*.difficulty' => [
                'required',
                Rule::in(QuizQuestionDifficultyData::getStringConstants()),
            ],
            '*.question' => 'required|min:3|max:1000',
            '*.answer_1' => 'required',
            '*.answer_2' => 'required',
            '*.answer_3' => 'present',
            '*.answer_4' => 'present',
            '*.correct_answer_1' => 'required',
            '*.correct_answer_2' => 'present|nullable',
            '*.correct_answer_3' => 'present|nullable',
        ];
    }
}
