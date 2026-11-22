<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame;

/**
 *
 * @author Daniel Schulz
 *
 */
interface NodeEvaluatorInterface {
    
    /**
     * Resolve a savegame expression to a numeric value.
     *
     * @param string|int|null $expression
     * @return int|float
     */
    public function evaluate($expression);
    
    /**
     * Compute the integer result of a math expression.
     */
    public function evaluateMath(string $expression): int;
}

