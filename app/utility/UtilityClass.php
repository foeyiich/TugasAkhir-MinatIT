<?php

namespace TugasAkhir\utility;

use InvalidArgumentException;

final class UtilityClass
{
    private function __construct()
    {
        // Utility class
    }

    public static function validateMapArray(array $data): void
    {
        if (!empty($data) && array_is_list($data)) {
            throw new InvalidArgumentException("Data must be a map.");
        }
    }

    public static function validateListArray(array $data): void
    {
        if (!empty($data) && !array_is_list($data)) {
            throw new InvalidArgumentException("Data must be a list.");
        }
    }

}
