<?php
namespace TugasAkhir\core;

final class Registries
{
    private static array $registry = [];

    private function __construct()
    {
    }

    public static function bind(string $key, $object): void
    {
        self::$registry[$key] = $object;
    }

    public static function get(string $key): mixed
    {
        return self::$registry[$key] ?? null;
    }

}
