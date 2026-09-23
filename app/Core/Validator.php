<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Rule strings use pipes, e.g. ['email' => 'required|email|max:190'].
 * Messages are in Bahasa Malaysia because they are shown to members as-is.
 */
final class Validator
{
    /** @var array<string,string> */
    private array $errors = [];

    /** @var array<string,string|null> */
    private array $validated = [];

    /**
     * @param array<string,mixed> $data
     * @param array<string,string> $rules
     * @param array<string,string> $labels human-readable field names for messages
     */
    public function __construct(private array $data, array $rules, private array $labels = [])
    {
        foreach ($rules as $field => $ruleString) {
            $this->validateField($field, explode('|', $ruleString));
        }
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    /** @return array<string,string> first failing message per field */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @return array<string,string|null> only the fields that had rules, trimmed */
    public function validated(): array
    {
        return $this->validated;
    }

    /** @param list<string> $rules */
    private function validateField(string $field, array $rules): void
    {
        $raw = $this->data[$field] ?? null;
        $value = is_string($raw) ? trim($raw) : null;
        $value = $value === '' ? null : $value;

        $this->validated[$field] = $value;

        if ($value === null && !in_array('required', $rules, true) && !in_array('accepted', $rules, true)) {
            return;
        }

        foreach ($rules as $rule) {
            [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
            $message = $this->check($field, $name, $param, $value);

            if ($message !== null) {
                $this->errors[$field] = $message;

                return;
            }
        }
    }

    private function check(string $field, string $rule, ?string $param, ?string $value): ?string
    {
        $label = $this->labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
        $length = $value === null ? 0 : mb_strlen($value);

        return match ($rule) {
            'required' => $value === null ? "{$label} diperlukan." : null,
            'accepted' => in_array($value, ['1', 'on', 'yes'], true) ? null : "Sila tandakan {$label}.",
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) ? null : "{$label} tidak sah.",
            'min' => $length >= (int) $param ? null : "{$label} mesti sekurang-kurangnya {$param} aksara.",
            'max' => $length <= (int) $param ? null : "{$label} tidak boleh melebihi {$param} aksara.",
            'in' => in_array($value, explode(',', (string) $param), true) ? null : "{$label} tidak sah.",
            'phone' => preg_match('/^(\+?60|0)[1-9][0-9]{7,9}$/', preg_replace('/[\s-]/', '', (string) $value) ?? '')
                ? null
                : "{$label} tidak sah. Contoh: 012-3456789.",
            // Checked in stored form (see normalizePlate), so spacing and case don't matter.
            // Letters up to 10 cover special series such as PUTRAJAYA 1.
            'plate' => preg_match('/^[A-Z]{1,10}[0-9]{1,4}[A-Z]{0,2}$/', normalizePlate((string) $value))
                ? null
                : "{$label} tidak sah. Contoh: WXY 1234.",
            'confirmed' => $value === ($this->data[$field . '_confirmation'] ?? null)
                ? null
                : "Pengesahan {$label} tidak sepadan.",
            default => throw new \InvalidArgumentException("Unknown validation rule: {$rule}"),
        };
    }
}
