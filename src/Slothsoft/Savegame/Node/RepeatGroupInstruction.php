<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

class RepeatGroupInstruction extends AbstractInstructionContent
{

    private $groupSize;

    private $groupCount;

    protected function getInstructionType(): string
    {
        return 'repeat-group';
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->groupSize = (int) $strucElement->getAttribute('group-size', 0, $this->ownerFile);
        $this->groupCount = (int) $strucElement->getAttribute('group-count', 0, $this->ownerFile);
    }

    protected function loadInstruction(EditorElement $strucElement)
    {
        $instructionList = [];
        
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
            
            $instructionList[] = new EditorElement(EditorElement::NODE_TYPES['group'], $strucData, $strucElement->getChildren());
        }
        
        return $instructionList;
    }
}

