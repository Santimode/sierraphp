<?php
declare(strict_types=1);
namespace Sierra\Validation;

class Validator
{
    protected array $data;
    protected array $rules;
    protected array $errors = [];
    protected bool $validated = false;

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function validate(): array
    {
        $this->runRules();

        if ($this->fails()) {
            throw new ValidationException($this->errors);
        }

        $validated = [];
        foreach (array_keys($this->rules) as $key) {
            if (array_key_exists($key, $this->data)) {
                $validated[$key] = $this->data[$key];
            }
        }
        return $validated;
    }

    protected function runRules(): void
    {
        if ($this->validated) {
            return;
        }

        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            
            foreach ($rules as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        $this->validated = true;
    }

    public function fails(): bool
    {
        $this->runRules();
        return !empty($this->errors);
    }

    public function errors(): array
    {
        $this->runRules();
        return $this->errors;
    }

    protected function applyRule(string $field, string $rule): void
    {
        $value = $this->data[$field] ?? null;

        if (str_starts_with($rule, 'min:')) {
            $min = (int) substr($rule, 4);
            if ($value !== null && $value !== '' && strlen((string)$value) < $min) {
                $this->addError($field, "The {$field} must be at least {$min} characters.");
            }
            return;
        }

        if (str_starts_with($rule, 'max:')) {
            $max = (int) substr($rule, 4);
            if ($value !== null && $value !== '' && strlen((string)$value) > $max) {
                $this->addError($field, "The {$field} must not be greater than {$max} characters.");
            }
            return;
        }

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '') {
                    $this->addError($field, "The {$field} field is required.");
                }
                break;
            case 'email':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "The {$field} must be a valid email address.");
                }
                break;
            case 'string':
                if ($value !== null && !is_string($value)) {
                    $this->addError($field, "The {$field} must be a string.");
                }
                break;
            case 'int':
                if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, "The {$field} must be an integer.");
                }
                break;
        }
    }

    protected function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
