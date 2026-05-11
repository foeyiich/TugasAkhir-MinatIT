<?php
namespace TugasAkhir\utils;
use InvalidArgumentException;

final class UtilityClass
{
    private function __construct()
    {
        // Utility class
    }

    public static function validateMapArray(array $data): void
    {
        if (array_is_list($data)) {
            throw new InvalidArgumentException("Data must be a map.");
        }
    }

    public static function validateListArray(array $data): void
    {
        if (!array_is_list($data)) {
            throw new InvalidArgumentException("Data must be a list.");
        }
    }

}
