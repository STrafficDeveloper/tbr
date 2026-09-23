<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Thrown when a rule can only be checked inside the database work itself
 * (a slot filling up mid-transaction, a unique key clash). Carries
 * field => message pairs the controller can show beside the form fields.
 */
final class ValidationException extends RuntimeException
{
    /** @param array<string,string> $errors */
    public function __construct(public readonly array $errors)
    {
        parent::__construct((string) reset($errors));
    }
}
