<?php

namespace TugasAkhir\core\data;

/**
 * Interface Fields
 * * Represents a strict contract for defining database schema fields.
 * Classes or Enums implementing this interface must provide the column's name,
 * its SQL definition, and a static method to build the complete schema array.
 */
interface DataField
{

    /**
     * @return string field name
     */
    public function field(): string;

    /**
     * Retrieves the SQL definition for the database column.
     * * Example: "VARCHAR(100) NOT NULL UNIQUE"
     *
     * @return string The SQL column definition.
     */
    public function getDefinition(): string;

    /**
     * Builds and returns the complete database schema definition for the entity.
     * * NOTE: Use EnumSchemaBuilder to automatically provide a build function with enum class.
     *
     * @return array<string, string> An associative array mapping column names to their SQL definitions.
     */
    public static function buildSchema(): array;
}