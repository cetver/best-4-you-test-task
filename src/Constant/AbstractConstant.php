<?php declare(strict_types=1);

namespace App\Constant;

/**
 * The "AbstractConstant" class
 */
abstract class AbstractConstant
{
    public function asArray(): array
    {
        $rc = new \ReflectionClass(static::class);

        return $rc->getConstants();
    }
}