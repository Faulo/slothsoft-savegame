<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame;

use OverflowException;

/**
 *
 * @author Daniel Schulz
 *
 */
interface NodeEvaluatorInterface {
    
    /**
     * Resolve a savegame expression to a numeric value.
     *
     * @throws OverflowException If a hexadecimal value or sign adjustment exceeds the integer range.
     */
    public function evaluate(string|int $expression): int;
    
    /**
     * Compute the integer result of a math expression.
     */
    public function evaluateMath(string $expression): int;
}

