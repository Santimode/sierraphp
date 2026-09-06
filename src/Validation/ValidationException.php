<?php
declare(strict_types=1);
namespace Sierra\Validation;

use Sierra\Http\HttpException;

class ValidationException extends HttpException
{
    protected array $errors = [];

    public function __construct(array $errors)
    {
        parent::__construct(422, 'The given data was invalid.');
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
