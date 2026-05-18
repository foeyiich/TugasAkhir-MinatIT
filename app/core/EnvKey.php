<?php

namespace TugasAkhir\core;
enum EnvKey
{
    case JUST_GENERATED;
    case DEBUG_MODE;
    case DB_TYPE;
    case DB_SQLITE_FILE;
    case DB_MYSQL_HOST;
    case DB_MYSQL_USER;
    case DB_MYSQL_PASSWORD;
    case DB_MYSQL_PORT;
    case DB_MYSQL_DATABASE;

    public function type(): int
    {
        return match ($this) {
            self::JUST_GENERATED, self::DEBUG_MODE => FILTER_VALIDATE_BOOL,
            self::DB_MYSQL_PORT => FILTER_VALIDATE_INT,
            default => FILTER_UNSAFE_RAW,
        };
    }
}
