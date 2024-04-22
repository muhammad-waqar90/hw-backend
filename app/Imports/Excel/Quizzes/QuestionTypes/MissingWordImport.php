<?php

namespace App\Imports\Excel\Quizzes\QuestionTypes;

use App\DataObject\QuizData;
use App\DataObject\QuizQuestionDifficultyData;
use App\Exceptions\Quizzes\Imports\MissingWordImportException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MissingWordImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
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
     * @throws MissingWordImportException
     */
    public function collection(Collection $collection): Collection
    {
        $validator = Validator::make($collection->toArray(), $this->rules());
        if($validator->fails())
            throw new MissingWordImportException('Data is invalid', 101, 0, $this->fileName, $validator->messages()->get('*'));

        $this->collection = $this->parseQuestionList($collection);
        return $this->collection;
    }

    /**
     * @throws MissingWordImportException
     */
    public function parseQuestionList($collection): Collection
    {
        $parsedQuestions = [];
        foreach($collection as $item) {
            $parsedQuestions[] = $this->parseQuestion($item);
            $this->row++;
        }
        return collect($parsedQuestions);
    }

    /**
     * @throws MissingWordImportException
     */
    public function parseQuestion($item): Collection
    {
        $item = $item->trimValues();
        $id = Str::orderedUuid()->toString();
        $options = $this->getOptions($item);

        $this->validateQuestion($options, $item);

        return collect(
            $this->mapQuestion($id, $options, $item),
        );
    }

    public function mapAnswer($options, $item): Collection
    {
        $correctAnswer = $options['list']->first(function ($option) use($item) {
            return $option['value'] === $item['correct_answer'];
        });
        return collect([
            'answerId' => $correctAnswer['id']
        ]);
    }

    public function mapQuestion($id, $options, $item): Collection
    {
        return collect([
            'uuid' => $id,
            'question' => $item['question'],
            'difficulty' => QuizQuestionDifficultyData::difficultyStringToInt($item['difficulty']),
            'type' => QuizData::QUESTION_MISSING_WORD,
            'options' => $options->toJson(),
            'answer' => $this->mapAnswer($options, $item)->toJson()
        ]);
    }

    public function getOptions($item): Collection
    {
        $options = [];
        $list = [];
        for($i = 0; $i < 10; $i++)
            if(strlen($item['answer_' . $i+1]))
                $list[] = [
                    'id' => Str::orderedUuid()->toString(),
                    'value' => $item['answer_' . $i+1]
                ];
        $options['list'] = collect($list);
        return collect($options);
    }

    /**
     * @throws MissingWordImportException
     */
    public function validateQuestion($options, $question)
    {
        if(!$this->hasUniqueOptions($question))
            throw new MissingWordImportException('Found duplicate options', 100, $this->row, $this->fileName);
        //Check if answer is NOT found in the options
        if(!$options['list']->contains(function ($item) use($question) {
            return $item['value'] === $question['correct_answer'];
        }))
            throw new MissingWordImportException('Correct answer not found in the options', 100, $this->row, $this->fileName);

    }

    public function hasUniqueOptions($question): bool
    {
        $options = [];
        for($i = 0; $i < 10; $i++)
            if(strlen($question['answer_' . $i+1]))
                $options[] = $question['answer_' . $i+1];
        return count($options) === count(array_unique($options));
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
                Rule::in(QuizQuestionDifficultyData::getStringConstants())
            ],
            '*.question' => 'required|min:3|max:1000',
            '*.answer_1' => 'required',
            '*.answer_2' => 'required',
            '*.answer_3' => 'required',
            '*.answer_4' => 'required',
            '*.answer_5' => 'present',
            '*.answer_6' => 'present',
            '*.answer_7' => 'present',
            '*.answer_8' => 'present',
            '*.answer_9' => 'present',
            '*.answer_10' => 'present',
            '*.correct_answer' => 'required',
        ];
    }
}
