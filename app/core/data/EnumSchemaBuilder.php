<?php

namespace TugasAkhir\core\data;

use BadMethodCallException;
use UnitEnum;

/**
 * Trait EnumSchemaBuilder
 * * Provides an automated mechanism to build a database schema array directly from a Pure Enum.
 * The consuming enum must strictly be a Pure Enum (not a BackedEnum) and must implement
 * the Fields interface to ensure proper definition retrieval.
 */
trait EnumSchemaBuilder
{

    /**
     * Builds and returns an associative array representing the database schema.
     * * Iterates through all cases of the consuming Enum, mapping the case name
     * to its corresponding SQL column definition.
     *
     * @return array<string, string> An associative array where the key is the field name and the value is the SQL definition.
     * @throws BadMethodCallException If the calling class is not Enum.
     * @throws BadMethodCallException If the calling class does not implement the Fields interface.
     */
    public static function buildSchema(): array
    {

        if (!is_subclass_of(static::class, UnitEnum::class)) {
            throw new BadMethodCallException("Class " . static::class . " must be a Enum");
        }

        if (!is_subclass_of(static::class, DataField::class)) {
            throw new BadMethodCallException("Class " . static::class . " must implement " . DataField::class);
        }

        $schema = [];
        foreach (static::cases() as $case) {
            $schema[$case->field()] = $case->getDefinition();
        }

        return $schema;
    }
}