<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

final readonly class CommandResult
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public string $status,
        public string $label,
        public string $message = '',
        public array $context = [],
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public static function ok(string $label, string $message = '', array $context = []): self
    {
        return new self('ok', $label, $message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function warning(string $label, string $message = '', array $context = []): self
    {
        return new self('warning', $label, $message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function error(string $label, string $message = '', array $context = []): self
    {
        return new self('error', $label, $message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function info(string $label, string $message = '', array $context = []): self
    {
        return new self('info', $label, $message, $context);
    }

    public function isError(): bool
    {
        return $this->status === 'error';
    }

    public function isWarning(): bool
    {
        return $this->status === 'warning';
    }

    /**
     * @return array{status: string, label: string, message: string, context: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'label' => $this->label,
            'message' => $this->message,
            'context' => $this->context,
        ];
    }
}
