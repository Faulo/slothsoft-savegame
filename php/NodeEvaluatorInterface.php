<?php
namespace Slothsoft\Savegame;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface NodeEvaluatorInterface
{
    public function evaluate($expression);
    public function evaluateMath(string $expression) : int;
}

