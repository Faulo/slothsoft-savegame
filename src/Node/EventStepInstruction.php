<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;

class EventStepInstruction extends AbstractInstructionContent {

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
    protected function getInstructionType(): string {
        return NodeFactory::TAG_EVENT_STEP;
    }

    protected function loadInstruction(LeanElement $strucElement): iterable {
        $savegame = $this->getOwnerSavegame();

        $eventType = $this->ownerFile->extractContent($this->contentOffset, 1);
        $eventType = $this->getConverter()->decodeInteger($eventType, 1);
        $eventType = sprintf('%02d', $eventType);

        $eventSubType = $this->ownerFile->extractContent($this->contentOffset + 1, 1);
        $eventSubType = $this->getConverter()->decodeInteger($eventSubType, 1);
        $eventSubType = sprintf('%02d', $eventSubType);

        foreach ([
            "event-$eventType.$eventSubType",
            "event-$eventType",
            "event-unknown"
        ] as $ref) {
            if ($instructionList = $savegame->getGlobalElementsById($ref)) {
                break;
            }
        }
        return $instructionList;
    }
}
