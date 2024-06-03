<?php

namespace App\Imports\Excel\Quizzes\QuestionTypes;

use App\DataObject\QuizData;
use App\DataObject\QuizQuestionDifficultyData;
use App\Exceptions\Quizzes\Imports\LinkingImportException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class LinkingImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
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
     * @throws LinkingImportException
     */
    public function collection(Collection $collection): Collection
    {
        $validator = Validator::make($collection->toArray(), $this->rules());
        if ($validator->fails()) {
            throw new LinkingImportException('Data is invalid', 101, 0, $this->fileName, $validator->messages()->get('*'));
        }

        $this->collection = $this->parseQuestionList($collection);

        return $this->collection;
    }

    /**
     * @throws LinkingImportException
     */
    public function parseQuestionList($collection): Collection
    {
        $parsedQuestions = [];
        $uniqueQuestions = [];
        for ($i = 0; $i < $collection->count(); $i += 2) {
            $question = $this->parseQuestion($collection[$i], $collection[$i + 1]);
            $uniqueQuestions[] = $question['question'];
            if (count($uniqueQuestions) !== count(array_unique($uniqueQuestions))) {
                throw new LinkingImportException('Non unique question found at current row', 100, $this->row, $this->fileName);
            }

            $parsedQuestions[] = $question;
            $this->row = $this->row + 2;
        }

        return collect($parsedQuestions);
    }

    /**
     * @throws LinkingImportException
     */
    public function parseQuestion($leftSide, $rightSide): Collection
    {
        $leftSide = $leftSide->trimValues();
        $rightSide = $rightSide->trimValues();
        $id = Str::orderedUuid()->toString();
        $options = $this->getOptions($leftSide, $rightSide);

        $this->validateQuestion($leftSide, $rightSide);

        return collect(
            $this->mapQuestion($id, $options, $leftSide),
        );
    }

    public function mapAnswer($options): Collection
    {
        $answerId = [];
        for ($i = 0; $i < count($options['leftSide']); $i++) {
            $answerId[$options['leftSide'][$i]['id']] = $options['rightSide'][$i]['id'];
        }

        return collect([
            'answerId' => $answerId,
        ]);
    }

    public function mapQuestion($id, $options, $leftSide): Collection
    {
        return collect([
            'uuid' => $id,
            'question' => $leftSide['question'],
            'difficulty' => QuizQuestionDifficultyData::difficultyStringToInt($leftSide['difficulty']),
            'type' => QuizData::QUESTION_LINKING,
            'options' => $options->toJson(),
            'answer' => $this->mapAnswer($options)->toJson(),
        ]);
    }

    /**
     * @throws LinkingImportException
     */
    public function getOptions($leftSide, $rightSide): Collection
    {
        $options = ['leftSide' => [], 'rightSide' => []];
        for ($i = 0; $i < 10; $i++) {
            if (! strlen($leftSide['link_'.$i + 1]) && strlen($rightSide['link_'.$i + 1]) || strlen($leftSide['link_'.$i + 1]) && ! strlen($rightSide['link_'.$i + 1])) {
                throw new LinkingImportException('Answer missing in link_'.$i + 1, 100, $this->row, $this->fileName);
            }
            if (! strlen($leftSide['link_'.$i + 1]) && ! strlen($rightSide['link_'.$i + 1])) {
                continue;
            }
            $options['leftSide'][] = [
                'id' => Str::orderedUuid()->toString(),
                'value' => $leftSide['link_'.$i + 1],
            ];
            $options['rightSide'][] = [
                'id' => Str::orderedUuid()->toString(),
                'value' => $rightSide['link_'.$i + 1],
            ];
        }

        return collect($options);
    }

    /**
     * @throws LinkingImportException
     */
    public function validateQuestion($leftSide, $rightSide)
    {
        if (! ($this->hasUniqueOptions($leftSide) && $this->hasUniqueOptions($rightSide))) {
            throw new LinkingImportException('Found duplicate options', 100, $this->row, $this->fileName);
        }

        $validator = Validator::make($leftSide->toArray(), [
            'difficulty' => [
                'required',
                Rule::in(QuizQuestionDifficultyData::getStringConstants()),
            ],
            'question' => 'required',
        ]);
        if ($validator->fails()) {
            throw new LinkingImportException('Data is invalid', 101, 0, $this->fileName, $validator->messages()->get('*'));
        }
    }

    public function hasUniqueOptions($question): bool
    {
        $options = [];
        for ($i = 0; $i < 10; $i++) {
            if (strlen($question['link_'.$i + 1])) {
                $options[] = $question['link_'.$i + 1];
            }
        }

        return count($options) === count(array_unique($options));
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    public function rules(): array
    {
        return [
            '*.difficulty' => 'present',
            '*.question' => 'present|max:1000',
            '*.link_1' => 'required',
            '*.link_2' => 'required',
            '*.link_3' => 'required',
            '*.link_4' => 'required',
            '*.link_5' => 'present',
            '*.link_6' => 'present',
            '*.link_7' => 'present',
            '*.link_8' => 'present',
            '*.link_9' => 'present',
            '*.link_10' => 'present',
        ];
    }
}
