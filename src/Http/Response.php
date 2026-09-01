<?php
declare(strict_types=1);
namespace Sierra\Http;

class Response
{
    public function __construct(
        protected mixed $content = '',
        protected int $status = 200,
        protected array $headers = []
    ) {}

    public function getStatusCode(): int { return $this->status; }
    public function getContent(): mixed { return $this->content; }
    public function getBody(): mixed { return $this->content; }
    public function getHeaders(): array { return $this->headers; }

    public function getHeader(string $key): ?string
    {
        foreach ($this->headers as $k => $v) {
            if (strcasecmp($k, $key) === 0) {
                return $v;
            }
        }
        return null;
    }

    public function status(int $code): self { $this->status = $code; return $this; }
    public function header(string $key, string $value): self { $this->headers[$key] = $value; return $this; }

    public function json(mixed $data, int $status = 200): self
    {
        $this->content = json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        $this->status = $status;
        $this->headers['Content-Type'] = 'application/json';
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $k => $v) {
            header("{$k}: {$v}");
        }
        if (is_string($this->content)) {
            echo $this->content;
        } elseif (is_array($this->content)) {
            echo json_encode($this->content);
        }
    }

    public function __toString(): string
    {
        return (string)$this->content;
    }
}
