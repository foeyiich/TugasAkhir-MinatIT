<?php

namespace Test;

use Countable;
use Throwable;

abstract class TestCase
{
    protected int $passedCount = 0;
    protected int $failedCount = 0;
    protected array $errors = [];

    final public function printStart(): void
    {
        echo "\e[34m------------------ TEST START: " . static::class . " ------------------\n\e[0m";
    }

    public function onStart(): void
    {
    }

    public function onStop(): void
    {
    }

    abstract public function run(): void;

    protected function assertEquals(mixed $expected, mixed $actual, string $message = ""): void
    {
        if ($expected === $actual) {
            $this->pass($message);
        } else {
            $detail = "Expected: " . print_r($expected, true) . ", Actual: " . print_r($actual, true);
            $this->fail($message, $detail);
        }
    }

    protected function assertNotEquals(mixed $expected, mixed $actual, string $message = ""): void
    {
        if ($expected !== $actual) {
            $this->pass($message);
        } else {
            $this->fail($message, "Expected values NOT to be equal");
        }
    }

    protected function assertTrue(bool $condition, string $message = ""): void
    {
        $condition ?
            $this->pass($message) :
            $this->fail($message, "Expected True, but got False");
    }

    protected function assertFalse(bool $condition, string $message = ""): void
    {
        !$condition ?
            $this->pass($message) :
            $this->fail($message, "Expected False, but got True");
    }

    protected function assertNull(mixed $obj, string $message = ""): void
    {
        if ($obj === null) {
            $this->pass($message);
        } else {
            $this->fail($message, "Expected value to be Null");
        }
    }

    protected function assertNotNull(mixed $obj, string $message = ""): void
    {
        if ($obj !== null) {
            $this->pass($message);
        } else {
            $this->fail($message, "Expected value not to be Null");
        }
    }

    protected function assertCount(int $expectedCount, countable|array $haystack, string $message = ""): void
    {
        $actualCount = count($haystack);
        if ($actualCount === $expectedCount) {
            $this->pass($message);
        } else {
            $this->fail($message, "Expected count $expectedCount, but got $actualCount");
        }
    }

    protected function assertContains(mixed $needle, array $haystack, string $message = ""): void
    {
        if (in_array($needle, $haystack, true)) {
            $this->pass($message);
        } else {
            $this->fail($message, "Array does not contain expected value");
        }
    }

    protected function assertStringContainsString(string $needle, string $haystack, string $message = ""): void
    {
        if (str_contains($haystack, $needle)) {
            $this->pass($message);
        } else {
            $this->fail($message, "String '$haystack' does not contain '$needle'");
        }
    }

    protected function assertInstanceOf(string $expectedClass, mixed $actual, string $message = ""): void
    {
        if ($actual instanceof $expectedClass) {
            $this->pass($message);
        } else {
            $actualType = is_object($actual) ? $actual::class : gettype($actual);
            $this->fail($message, "Expected instance of $expectedClass, but got $actualType");
        }
    }

    protected function assertThrows(string $expectedException, callable $code, string $message = ""): void
    {
        try {
            $code();
            $this->fail($message, "Expected exception $expectedException was not thrown");
        } catch (Throwable $e) {
            if ($e instanceof $expectedException) {
                $this->pass($message);
            } else {
                $this->fail($message, "Expected $expectedException, but caught " . $e::class);
            }
        }
    }

    protected function pass(string $message = ""): void
    {
        $this->passedCount++;
        $output = "✅ [PASS]";
        if ($message !== "") {
            $output .= " - $message";
        }
        echo "\e[0;92m" . $output . "\e[0m\n";
    }

    protected function fail(string $message = "", string $extraDetail = ""): void
    {
        $this->failedCount++;
        $output = "❌ [FAIL]";

        if ($message !== "") {
            $output .= " - $message";
        }

        if ($extraDetail !== "") {
            $output .= " | $extraDetail";
        }

        $output = "\e[0;31m" . $output . "\e[0m";

        $this->errors[] = $output;
        echo $output . "\n";
    }

    public function printSummary(): void
    {
        echo "\e[0;0m--- TEST RESULT: " . static::class . "\n";
        echo "Passed: \e[0;32m$this->passedCount\e[0;0m | Failed: \e[0;31m$this->failedCount\n\e[0;0m";
        if (!empty($this->errors)) {
            echo "Error Detail:\n   " . implode("\n   ", $this->errors) . "\n\e[0;0m";
        }
        echo "\n";
    }
}