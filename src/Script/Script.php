<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Script;

class Script extends AbstractElement {
    
    /** @var list<Event> */
    protected array $eventList = [];
    
    protected function init(): void {
        $this->eventList = [];
    }
    
    /**
     * Reset the event list while binary script parsing is unimplemented.
     */
    public function fromBinary(string $binary): void {
        $this->init();
        /*
         * $offsetWordSize = 2;
         * $eventWordSize = 12;
         * $pointer = 0;
         *
         * $eventCount = substr($binary, $pointer, $offsetWordSize);
         * $eventCount = $this->converter->decodeInteger($eventCount, $offsetWordSize);
         * $pointer += $offsetWordSize;
         *
         * $eventSizeList = [];
         * $lastEnd = 0;
         * $eventSizeOffset = $this->valueOffset + 4;
         * for ($eventNo = 0; $eventNo < $eventCount; $eventNo ++) {
         * $eventEnd = $this->ownerFile->extractContent($eventSizeOffset, $offsetWordSize);
         * $eventEnd = $this->converter->decodeInteger($eventEnd, $offsetWordSize);
         * $eventEnd *= $eventWordSize;
         *
         * $eventSizeList[] = $eventEnd - $lastEnd;
         *
         * $lastEnd = $eventEnd;
         * $eventSizeOffset += $offsetWordSize;
         * }
         *
         * $eventStartOffset = $this->valueOffset + 4 + $eventNo * $offsetWordSize;
         *
         * $value = $this->ownerFile->extractContent($this->valueOffset, $eventStartOffset - $this->valueOffset);
         * foreach ($eventSizeList as $i => $eventSize) {
         * $value .= $this->ownerFile->extractContent($eventStartOffset, $eventSize);
         * $eventStartOffset += $eventSize;
         * }
         *
         * $this->setRawValue($value);
         *
         * //
         */
    }
    
    /**
     * Reset the event list while text script parsing is unimplemented.
     */
    public function fromCode(string $code): void {
        $this->init();
    }
    
    /**
     * Concatenate the binary representations of the script's events in order.
     */
    public function toBinary(): string {
        $ret = '';
        foreach ($this->eventList as $event) {
            $ret .= $event->toBinary();
        }
        return $ret;
    }
    
    /**
     * Concatenate the text representations of the script's events in order.
     */
    public function toCode(): string {
        $ret = '';
        foreach ($this->eventList as $event) {
            $ret .= $event->toCode();
        }
        return $ret;
    }
}
