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
     * @param string|int|null $expression
     * @return int|float
     */
    public function evaluate($expression);
    
    public function evaluateMath(string $expression): int;
}

