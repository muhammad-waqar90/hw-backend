<?php

namespace App\Imports\Excel\Quizzes;

use App\Imports\Excel\Quizzes\QuestionTypes\LinkingImport;
use App\Imports\Excel\Quizzes\QuestionTypes\McqImport;
use App\Imports\Excel\Quizzes\QuestionTypes\MissingWordImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Validators\ValidationException;

class ExamQuestionTypeImport implements ToCollection, WithMultipleSheets
{
    use Importable;

    public array $sheet;
    private string $fileName;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function collection(Collection $collection): Collection
    {
        return $collection;
    }

    /**
     * @throws \App\Exceptions\Quizzes\Imports\McqImportException
     */
    public function sheets(): array
    {
        try {
            $this->sheets = [
                'mcq' => new McqImport($this->fileName),
                'missing_word' => new MissingWordImport($this->fileName),
                'linking' => new LinkingImport($this->fileName)
            ];

            return $this->sheets;
        } catch (ValidationException $e) {
            Log::error('Exception: ExamQuestionTypeImport@sheets', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }
}
