<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Thrown by BaseModel::findOrFail() when no matching row exists.
 */
final class ModelNotFoundException extends \RuntimeException
{
}
