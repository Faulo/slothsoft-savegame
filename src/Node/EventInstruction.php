<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;

final class EventInstruction extends AbstractInstructionContent {
    
    private $size;
    
    private $stepSize;
    
    protected function getInstructionType(): string {
        return NodeFactory::TAG_EVENT;
    }
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        
        $this->size = (int) $strucElement->getAttribute('size');
        $this->stepSize = (int) $strucElement->getAttribute('step-size');
    }
    
    protected function loadInstruction(LeanElement $strucElement): iterable {
        for ($i = 0; $i < $this->size; $i += $this->stepSize) {
            $strucData = [];
            $strucData['position'] = $i;
            // $strucData['size'] = $this->stepSize;
            
            yield LeanElement::createOneFromArray(NodeFactory::TAG_EVENT_STEP, $strucData, $strucElement->getChildren());
        }
    }
}
