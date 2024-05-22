<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Editor;
use DomainException;

class NodeFactory {

    const TAG_SAVEGAME_EDITOR = 'savegame.editor';

    const TAG_GLOBALS = 'globals';

    const TAG_GLOBAL = 'global';

    const TAG_ARCHIVE = 'archive';

    const TAG_FOR_EACH_FILE = 'for-each-file';

    const TAG_FILE = 'file';

    const TAG_BINARY = 'binary';

    const TAG_INTEGER = 'integer';

    const TAG_SIGNED_INTEGER = 'signed-integer';

    const TAG_STRING = 'string';

    const TAG_BIT = 'bit';

    const TAG_SELECT = 'select';

    const TAG_EVENT_SCRIPT = 'event-script';

    const TAG_IMAGE = 'image';

    const TAG_GROUP = 'group';

    const TAG_INSTRUCTION = 'instruction';

    const TAG_BIT_FIELD = 'bit-field';

    const TAG_STRING_DICTIONARY = 'string-dictionary';

    const TAG_EVENT_DICTIONARY = 'event-dictionary';

    const TAG_EVENT = 'event';

    const TAG_EVENT_STEP = 'event-step';

    const TAG_REPEAT_GROUP = 'repeat-group';

    const TAG_USE_GLOBAL = 'use-global';

    const TAG_IMAGE_MAP = 'image-map';

    const TAG_IMAGE_PILE = 'image-pile';

    private $editor;

    public function __construct(Editor $editor) {
        $this->editor = $editor;
    }

    /**
     *
     * @param \Slothsoft\Core\XML\LeanElement $strucElement
     * @param \Slothsoft\Savegame\Node\AbstractNode $parentValue
     * @return \Slothsoft\Savegame\Node\AbstractNode
     */
    public function createNode(LeanElement $strucElement, ?AbstractNode $parentValue = null): AbstractNode {
        $value = $this->constructValue($strucElement->getTag());
        $value->init($strucElement, $parentValue);
        return $value;
    }

    private function constructValue(string $tag): AbstractNode {
        switch ($tag) {
            // root
            case self::TAG_SAVEGAME_EDITOR:
                return new SavegameNode($this->editor, $this);
            case self::TAG_ARCHIVE:
                return new ArchiveNode();
            case self::TAG_FOR_EACH_FILE:
                return new ForEachFileNode();
            case self::TAG_FILE:
                return new FileContainer();

            // values
            case self::TAG_INTEGER:
                return new IntegerValue();
            case self::TAG_SIGNED_INTEGER:
                return new SignedIntegerValue();
            case self::TAG_STRING:
                return new StringValue();
            case self::TAG_BIT:
                return new BitValue();
            case self::TAG_SELECT:
                return new SelectValue();
            case self::TAG_EVENT_SCRIPT:
                return new EventScriptValue();
            case self::TAG_BINARY:
                return new BinaryValue();
            case self::TAG_IMAGE:
                return new ImageValue();

            // containers
            case self::TAG_GROUP:
                return new GroupContainer();
            case self::TAG_INSTRUCTION:
                return new InstructionContainer();

            // instructions
            case self::TAG_BIT_FIELD:
                return new BitFieldInstruction();
            case self::TAG_STRING_DICTIONARY:
                return new StringDictionaryInstruction();
            case self::TAG_EVENT_DICTIONARY:
                return new EventDictionaryInstruction();
            case self::TAG_EVENT:
                return new EventInstruction();
            case self::TAG_EVENT_STEP:
                return new EventStepInstruction();
            case self::TAG_REPEAT_GROUP:
                return new RepeatGroupInstruction();
            case self::TAG_USE_GLOBAL:
                return new UseGlobalInstruction();
            case self::TAG_IMAGE_MAP:
                return new ImageMapInstruction();
            case self::TAG_IMAGE_PILE:
                return new ImagePileInstruction();

            default:
                throw new DomainException(sprintf('unknown tag: <sse:%s/>', $tag));
        }
    }
}

