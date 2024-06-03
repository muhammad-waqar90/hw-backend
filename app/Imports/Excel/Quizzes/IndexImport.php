<?php

namespace App\Imports\Excel\Quizzes;

use App\DataObject\QuizData;
use App\DataObject\QuizQuestionDifficultyData;
use App\Exceptions\Quizzes\Imports\IndexImportException;
use App\Models\BulkImportStatus;
use App\Models\CourseLevel;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class IndexImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    use Importable;

    public string $workingDirectory;

    private int $row = 2;

    private string $fileName;

    private string $workingFile = '';

    private BulkImportStatus $bis;

    public function __construct($bis, $workingDirectory, $fileName)
    {
        $this->bis = $bis;
        $this->workingDirectory = $workingDirectory;
        $this->fileName = $fileName;
    }

    /**
     * @throws IndexImportException
     */
    public function collection(Collection $collection): Collection
    {
        $validator = Validator::make($collection->toArray(), $this->rules());
        if ($validator->fails()) {
            throw new IndexImportException('Data is invalid', 101, 0, $this->fileName, $validator->messages()->get('*'));
        }

        if ($collection->count() == 0) {
            throw new IndexImportException('Index file is empty', 103, 0, $this->fileName);
        }

        foreach ($collection as $item) {
            $this->parseIndexRow($item);
            $this->row++;
        }

        return $collection;
    }

    /**
     * @throws IndexImportException
     */
    public function parseIndexRow($item): void
    {
        $this->workingFile = $item['questions'];
        $entityData = $this->getEntityData($item);
        $questionsAnswers = new ExamQuestionTypeImport($item['questions']);
        ($questionsAnswers)->import($this->workingDirectory.'/'.$item['questions']);

        $formattedExam = $this->formatExam($entityData, $questionsAnswers, $item);

        $this->validateFormattedExam($formattedExam);
        $quiz = $this->insertQuiz($formattedExam);
        $this->insertQuizItems($formattedExam['quiz_items'], $quiz);
    }

    /**
     * @throws IndexImportException
     */
    public function validateFormattedExam($formattedExam)
    {
        $this->checkIfEntityExists($formattedExam);
        $this->checkNumberOfQuestions($formattedExam);
    }

    /**
     * @throws IndexImportException
     */
    public function checkNumberOfQuestions($formattedExam)
    {
        if ($formattedExam['quiz_items']->count() == 0) {
            throw new IndexImportException('The file does not contain any questions',
                100, 0, $this->workingFile
            );
        }

        if ($formattedExam['quiz_items']->count() < $formattedExam['num_of_questions']) {
            throw new IndexImportException('There are less questions than provided num_of_questions',
                100, 0, $this->workingFile
            );
        }
        $numOfEasy = 0;
        $numOfDifficult = 0;
        foreach ($formattedExam['quiz_items'] as $question) {
            if ($question['difficulty'] === QuizQuestionDifficultyData::EASY_INT) {
                $numOfEasy++;
            }
            if ($question['difficulty'] === QuizQuestionDifficultyData::DIFFICULT_INT) {
                $numOfDifficult++;
            }
        }

        $requiredNumOfEasyAndDifficult = $formattedExam['num_of_questions'] * 3 / 4;
        if ($numOfEasy < $requiredNumOfEasyAndDifficult && $numOfDifficult < $requiredNumOfEasyAndDifficult) {
            throw new IndexImportException('Both number of easy and difficult questions do not satisfy the quota of 0.75 * num_of_questions',
                100, 0, $this->workingFile
            );
        }
        if ($numOfEasy < $requiredNumOfEasyAndDifficult) {
            throw new IndexImportException('Number of easy questions do not satisfy the quota of 0.75 * num_of_questions',
                100, 0, $this->workingFile
            );
        }
        if ($numOfDifficult < $requiredNumOfEasyAndDifficult) {
            throw new IndexImportException('Number of difficult questions do not satisfy the quota of 0.75 * num_of_questions',
                100, 0, $this->workingFile
            );
        }
    }

    public function insertQuiz(Collection $formattedExam)
    {
        return Quiz::updateOrCreate([
            'entity_id' => $formattedExam['entity_id'],
            'entity_type' => $formattedExam['entity_type'],
        ],
            [
                'price' => $formattedExam['price'],
                'duration' => $formattedExam['duration'],
                'num_of_questions' => $formattedExam['num_of_questions'],
            ]
        );
    }

    public function insertQuizItems(Collection $quizItems, $quiz)
    {
        QuizItem::where('quiz_id', $quiz->id)->delete();

        $quizItems->map(function ($item) use ($quiz) {
            $item['quiz_id'] = $quiz->id;
            $item['created_at'] = Carbon::now();
            $item['updated_at'] = Carbon::now();

            return $item;
        });
        QuizItem::insert($quizItems->toArray());
    }

    /**
     * @throws IndexImportException
     */
    public function getEntityData($item)
    {
        if ($item['lesson_id'] && $item['price'] !== null) {
            throw new IndexImportException('Lesson quiz cannot have a price', 103, $this->row, $this->fileName);
        }
        if ($item['lesson_id']) {
            return ['entity_type' => QuizData::ENTITY_LESSON, 'entity_id' => $item['lesson_id']];
        }
        if ($item['module_id']) {
            return ['entity_type' => QuizData::ENTITY_COURSE_MODULE, 'entity_id' => $item['module_id']];
        }
        if ($item['level_id']) {
            return ['entity_type' => QuizData::ENTITY_COURSE_LEVEL, 'entity_id' => $item['level_id']];
        }

        throw new IndexImportException('Data is invalid, probably missing lesson/module/level id', 102, $this->row, $this->fileName);
    }

    public function formatExam($entityData, $questionsAnswers, $indexRow): Collection
    {
        $quizQuestionTypes = ['mcq', 'missing_word', 'linking'];
        $quizItems = [];

        foreach ($quizQuestionTypes as $type) {
            foreach ($questionsAnswers->sheets[$type]->getCollection() as $item) {
                $quizItems[] = $item;
            }
        }

        return collect([
            'entity_id' => $entityData['entity_id'],
            'entity_type' => $entityData['entity_type'],
            'quiz_items' => collect($quizItems),
            'duration' => $indexRow['duration'],
            'num_of_questions' => $indexRow['number_of_questions'],
            'price' => $indexRow['price'] ?: null,
        ]);
    }

    /**
     * @throws IndexImportException
     */
    public function checkIfEntityExists(Collection $formattedExam)
    {
        if ($formattedExam['entity_type'] === QuizData::ENTITY_LESSON) {
            $lessonExists = Lesson::where('id', $formattedExam['entity_id'])
                ->where('course_id', $this->bis->course_id)
                ->exists();
            if (! $lessonExists) {
                throw new IndexImportException('Lesson with id: '.$formattedExam['entity_id'].' not found for courseId: '.$this->bis->course_id,
                    100, $this->row, $this->fileName
                );
            }
        }
        if ($formattedExam['entity_type'] === QuizData::ENTITY_COURSE_MODULE) {
            $courseModuleExists = CourseModule::where('id', $formattedExam['entity_id'])
                ->where('course_id', $this->bis->course_id)
                ->exists();
            if (! $courseModuleExists) {
                throw new IndexImportException('Module with id: '.$formattedExam['entity_id'].' not found for courseId: '.$this->bis->course_id,
                    100, $this->row, $this->fileName
                );
            }
        }
        if ($formattedExam['entity_type'] === QuizData::ENTITY_COURSE_LEVEL) {
            $courseLevelExists = CourseLevel::where('id', $formattedExam['entity_id'])
                ->where('course_id', $this->bis->course_id)
                ->exists();
            if (! $courseLevelExists) {
                throw new IndexImportException('Level with id: '.$formattedExam['entity_id'].' not found for courseId: '.$this->bis->course_id,
                    100, $this->row, $this->fileName
                );
            }
        }
    }

    public function rules(): array
    {
        return [
            '*.lesson_id' => 'nullable|exactly_one_required:module_id,level_id|distinct',
            '*.module_id' => 'nullable|exactly_one_required:lesson_id,level_id|distinct',
            '*.level_id' => 'nullable|exactly_one_required:module_id,lesson_id|distinct',
            '*.number_of_questions' => 'required|integer|min:4|is_divisible_by:4', //16 default
            '*.duration' => 'required|integer|min:10', //300 default
            '*.price' => 'present|nullable|numeric|between:0,99.99',
            '*.questions' => 'required|string',
        ];
    }
}
