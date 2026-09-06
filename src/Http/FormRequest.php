<?php
declare(strict_types=1);
namespace Sierra\Http;

abstract class FormRequest extends Request
{
    public static function createFromBase(Request $request): static
    {
        return new static(
            $request->method,
            $request->uri,
            $request->query,
            $request->body,
            $request->headers,
            $request->attributes,
            $request->server
        );
    }

    abstract public function rules(): array;

    public function validateResolved(): void
    {
        $this->validate($this->rules());
    }
}
