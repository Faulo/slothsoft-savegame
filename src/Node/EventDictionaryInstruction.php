<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use RangeException;

final class EventDictionaryInstruction extends AbstractInstructionContent {
    
    protected function getInstructionType(): string {
        return NodeFactory::TAG_EVENT_DICTIONARY;
    }
    
    protected function loadInstruction(LeanElement $strucElement): iterable {
        $offsetWordSize = 2;
        $eventWordSize = 12;
        
        $eventCount = $this->ownerFile->extractContent($this->contentOffset, $offsetWordSize);
        $eventCount = $this->getConverter()->decodeInteger($eventCount, $offsetWordSize);
        
        if ($eventCount > 256) {
            throw new RangeException("there probably shouldn't be $eventCount events at $this->contentOffset in " . $this->ownerFile->getFileName());
        }
        
        $eventSizeList = [];
        $lastEnd = 0;
        for ($eventNo = 0; $eventNo < $eventCount; $eventNo ++) {
            $eventOffset = $this->contentOffset + 4 + $eventNo * $offsetWordSize;
            
            $eventEnd = $this->ownerFile->extractContent($eventOffset, $offsetWordSize);
            $eventEnd = $this->getConverter()->decodeInteger($eventEnd, $offsetWordSize);
            $eventEnd *= $eventWordSize;
            
            $eventSizeList[] = $eventEnd - $lastEnd;
            $lastEnd = $eventEnd;
        }
        $eventStartOffset = $this->contentOffset + 4 + $eventNo * $offsetWordSize;
        
        foreach ($eventSizeList as $i => $eventSize) {
            $strucData = [];
            $strucData['name'] = sprintf('event-%02d', $i + 1);
            $strucData['position'] = $eventStartOffset - $this->contentOffset;
            $strucData['size'] = $eventSize;
            $strucData['step-size'] = $eventWordSize;
            
            yield LeanElement::createOneFromArray(NodeFactory::TAG_EVENT, $strucData, $strucElement->getChildren());
            
            $eventStartOffset += $eventSize;
        }
    }
}