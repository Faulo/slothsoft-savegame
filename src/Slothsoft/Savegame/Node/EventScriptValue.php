<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\Build\BuilderInterface;

declare(ticks = 1000);

class EventScriptValue extends AbstractValueContent
{

    public function getBuildTag(): string
    {
        return 'event-script';
    }
	public function getBuildAttributes(BuilderInterface $builder): array
    {
		return parent::getBuildAttributes($builder) + [
			'value' 	=> $builder->escapeAttribute($this->value),
		];
	}

    protected function loadContent()
    {
        $scriptSize = 4;
        
        $offsetWordSize = 2;
        $eventWordSize = 12;
        
        $eventCount = $this->ownerFile->extractContent($this->contentOffset, $offsetWordSize);
        $eventCount = $this->getConverter()->decodeInteger($eventCount, $offsetWordSize);
        
        $lastEnd = 0;
        $eventSizeOffset = $this->contentOffset + 4;
        for ($eventNo = 0; $eventNo < $eventCount; $eventNo ++) {
            $eventEnd = $this->ownerFile->extractContent($eventSizeOffset, $offsetWordSize);
            $eventEnd = $this->getConverter()->decodeInteger($eventEnd, $offsetWordSize);
            $eventEnd *= $eventWordSize;
            
            $scriptSize += $offsetWordSize;
            $scriptSize += $eventEnd - $lastEnd;
            
            $lastEnd = $eventEnd;
            $eventSizeOffset += $offsetWordSize;
        }
        
        $value = $this->ownerFile->extractContent($this->contentOffset, $scriptSize);
        $this->setRawValue($value);
    }

    protected function decodeValue(string $rawValue)
    {
        return $this->getConverter()->decodeScript($rawValue);
    }

    protected function encodeValue($value): string
    {
        return $this->getConverter()->encodeScript($value);
    }
}