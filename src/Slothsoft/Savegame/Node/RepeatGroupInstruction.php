<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;

class RepeatGroupInstruction extends AbstractInstructionContent
{

    private $groupSize;

    private $groupCount;

    protected function getInstructionType(): string
    {
        return NodeFactory::TAG_REPEAT_GROUP;
    }

    protected function loadStruc(LeanElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->groupSize = $strucElement->hasAttribute('group-size')
            ? (int) $this->ownerFile->evaluate($strucElement->getAttribute('group-size'))
            : 0;
        $this->groupCount = $strucElement->hasAttribute('group-count')
            ? (int) $this->ownerFile->evaluate($strucElement->getAttribute('group-count'))
            : 0;
    }

    protected function loadInstruction(LeanElement $strucElement)
    {
        $start = 0;
        $step = $this->groupSize;
        $count = $this->groupCount * $step;
        
        $positionList = [];
        for ($i = $start; $i < $count; $i += $step) {
            $positionList[] = $i;
        }
        
        foreach ($positionList as $i => $position) {
            $strucData = [];
            $strucData['position'] = $position;
            // $strucData['name'] = $this->dictionary ? (string) $this->dictionary->getOption($i) : '';
            
            yield LeanElement::createOneFromArray(NodeFactory::TAG_GROUP, $strucData, $strucElement->getChildren());
        }
    }
}

