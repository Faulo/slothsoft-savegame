<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

class EventStepInstruction extends AbstractInstructionContent
{

    /*
     * const EVENT_TYPE_IF = 13;
     * const EVENT_TYPE_TRIGGER = 16;
     * const EVENT_TYPE_TEXT = 17;
     * const EVENT_TYPE_CREATE = 18;
     * const EVENT_TYPE_MEMBERSHIP = 23;
     *
     * const EVENT_IF_SWITCH = 0;
     * const EVENT_IF_ITEM = 6;
     *
     * const EVENT_TRIGGER_KEYWORD = 0;
     * const EVENT_TRIGGER_SHOW_ITEM = 1;
     * const EVENT_TRIGGER_GIVE_ITEM = 2;
     * const EVENT_TRIGGER_GIVE_GOLD = 3;
     * const EVENT_TRIGGER_GIVE_FOOD = 4;
     * const EVENT_TRIGGER_JOIN = 5;
     * const EVENT_TRIGGER_LEAVE = 6;
     * const EVENT_TRIGGER_GREETING = 7;
     * const EVENT_TRIGGER_GOODBYE = 8;
     *
     * const EVENT_CREATE_ITEM = 0;
     * const EVENT_CREATE_FOOD = 2;
     *
     */
    protected function getInstructionType(): string
    {
        return 'event-step';
    }

    protected function loadInstruction(EditorElement $strucElement)
    {
        $instructionList = [];
        
        $savegame = $this->getOwnerSavegame();
        
        $eventType = $this->ownerFile->extractContent($this->contentOffset, 1);
        $eventType = $this->getConverter()->decodeInteger($eventType, 1);
        
        $eventSubType = $this->ownerFile->extractContent($this->contentOffset + 1, 1);
        $eventSubType = $this->getConverter()->decodeInteger($eventSubType, 1);
        
        $ref = sprintf('event-%02d.%02d', $eventType, $eventSubType);
        
        $instructionList = $savegame->getGlobalElementsById($ref);
        
        if (! $instructionList) {
            $ref = sprintf('event-%02d', $eventType);
            $instructionList = $savegame->getGlobalElementsById($ref);
        }
        
        if (! $instructionList) {
            $ref = 'event-unknown';
            $instructionList = $savegame->getGlobalElementsById($ref);
        }
        
        return $instructionList;
    }
}
