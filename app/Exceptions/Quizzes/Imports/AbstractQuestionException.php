<?php

namespace App\Exceptions\Quizzes\Imports;

use Exception;
use JetBrains\PhpStorm\Pure;
use Throwable;

abstract class AbstractQuestionException extends Exception
{
    protected ?int $row;

    protected ?string $fileName;

    protected ?array $details;

    abstract public function handle();

    #[Pure]
    public function __construct(string $message = '', int $code = 0, ?int $row = null,
        ?string $fileName = null, ?array $details = null, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->row = $row;
        $this->fileName = $fileName;
        $this->details = $details;
    }

    public function getRow(): int|string|null
    {
        return $this->row;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function formatError()
    {
        $formatted = [
            'message' => $this->getMessage(),
            'row' => $this->getRow(),
            'fileName' => $this->getFileName(),
        ];
        if ($this->getDetails()) {
            $formatted['details'] = $this->getDetails();
        }

        return $formatted;
    }
}
