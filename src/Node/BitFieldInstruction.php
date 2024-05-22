<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;

class BitFieldInstruction extends AbstractInstructionContent {

    private $size;

    private $firstBit;

    private $lastBit;

    protected function getInstructionType(): string {
        return NodeFactory::TAG_BIT_FIELD;
    }

    protected function loadStruc(LeanElement $strucElement) {
        parent::loadStruc($strucElement);

        $this->size = $strucElement->hasAttribute('size') ? (int) $this->ownerFile->evaluate($strucElement->getAttribute('size')) : 1;
        $this->firstBit = (int) $strucElement->getAttribute('first-bit', 0);
        $this->lastBit = (int) $strucElement->getAttribute('last-bit', $this->size * 8 - 1);
    }

    protected function loadInstruction(LeanElement $strucElement) {
        $max = $this->size - 1;
        for ($i = $this->firstBit; $i <= $this->lastBit; $i ++) {
            $offset = (int) ($i / 8);
            $pos = $max - $offset;
            $bit = $i - 8 * $offset;

            $strucData = [];
            $strucData['position'] = $pos;
            $strucData['bit'] = $bit;
            $strucData['size'] = 1;
            // $strucData['name'] = $this->dictionary ? (string) $this->dictionary->getOption($i) : '';

            yield LeanElement::createOneFromArray(NodeFactory::TAG_BIT, $strucData, $strucElement->getChildren());
        }
    }
}