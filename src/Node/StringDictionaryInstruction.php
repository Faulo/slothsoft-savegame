<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use DomainException;

class StringDictionaryInstruction extends AbstractInstructionContent {

    const LIST_TYPE_NULL_DELIMITED = 'null-delimited';

    const LIST_TYPE_SIZE_INTERSPERSED = 'size-interspersed';

    const LIST_TYPE_SIZE_FIRST = 'size-first';

    const LIST_TYPE_SIZE_FIXED = 'size-fixed';

    private $type;

    private $encoding;

    private $stringCount;

    private $stringSize;

    protected function getInstructionType(): string {
        return NodeFactory::TAG_STRING_DICTIONARY;
    }

    public function loadStruc(LeanElement $strucElement) {
        parent::loadStruc($strucElement);

        $this->encoding = (string) $strucElement->getAttribute('encoding');
        $this->type = (string) $strucElement->getAttribute('type');
    }

    protected function loadInstruction(LeanElement $strucElement) {
        // string-count
        switch ($this->type) {
            case self::LIST_TYPE_NULL_DELIMITED:
                $this->stringCount = $strucElement->hasAttribute('string-count') ? (int) $this->ownerFile->evaluate($strucElement->getAttribute('string-count')) : 0;
                break;
            case self::LIST_TYPE_SIZE_INTERSPERSED:
            case self::LIST_TYPE_SIZE_FIRST:
                $countSize = 2;
                for ($countOffset = 0; $countOffset < 10; $countOffset += $countSize) {
                    $count = $this->ownerFile->extractContent($this->contentOffset + $countOffset, $countSize);
                    $count = $this->getConverter()->decodeInteger($count, $countSize);
                    if ($count > 0) {
                        $this->stringCount = $count;
                        break;
                    }
                }
                break;
            case self::LIST_TYPE_SIZE_FIXED:
                $this->stringCount = $strucElement->hasAttribute('string-count') ? (int) $this->ownerFile->evaluate($strucElement->getAttribute('string-count')) : 0;
                $this->stringSize = $strucElement->hasAttribute('string-size') ? (int) $this->ownerFile->evaluate($strucElement->getAttribute('string-size')) : 0;
                break;
            default:
                throw new DomainException('unknown text-list type: ' . $this->type);
        }

        $strucDataList = [];

        switch ($this->type) {
            case self::LIST_TYPE_NULL_DELIMITED:
                $textOffset = $this->contentOffset;
                for ($i = 0; $i < $this->stringCount; $i ++) {
                    $text = $this->ownerFile->extractContent($textOffset, 'auto');
                    $textLength = strlen($text);

                    if (! $textLength) {
                        // break;
                    }

                    $strucData = [];
                    $strucData['position'] = $textOffset - $this->contentOffset;
                    $strucData['size'] = $textLength;
                    $strucData['encoding'] = $this->encoding;

                    $strucDataList[] = $strucData;

                    $textOffset += $textLength + 1;
                }
                break;
            case self::LIST_TYPE_SIZE_INTERSPERSED:
                $countSize = 2;
                $textLengthSize = 1;

                $textOffset = $this->contentOffset + $countSize;
                for ($i = 0; $i < $this->stringCount; $i ++) {
                    $textLength = $this->ownerFile->extractContent($textOffset, $textLengthSize);
                    $textLength = $this->getConverter()->decodeInteger($textLength, $textLengthSize);

                    $textOffset += $textLengthSize;

                    $strucData = [];
                    $strucData['position'] = $textOffset - $this->contentOffset;
                    $strucData['size'] = $textLength;
                    $strucData['encoding'] = $this->encoding;

                    $strucDataList[] = $strucData;

                    $textOffset += $textLength;
                }
                break;
            case self::LIST_TYPE_SIZE_FIRST:
                $countSize = 2;
                $textLengthSize = 2;

                $textOffset = $this->contentOffset + $countOffset + $countSize;
                $textLengthList = [];
                for ($i = 0; $i < $this->stringCount; $i ++) {
                    $textLength = $this->ownerFile->extractContent($textOffset, $textLengthSize);
                    $textLength = $this->getConverter()->decodeInteger($textLength, $textLengthSize);

                    $textLengthList[] = $textLength;

                    $textOffset += $textLengthSize;
                }
                $textOffset = $this->contentOffset + $countOffset + $countSize + $this->stringCount * $textLengthSize;
                foreach ($textLengthList as $textLength) {
                    $strucData = [];
                    $strucData['position'] = $textOffset - $this->contentOffset;
                    $strucData['size'] = $textLength;
                    $strucData['encoding'] = $this->encoding;

                    $strucDataList[] = $strucData;

                    $textOffset += $textLength;
                }
                break;
            case self::LIST_TYPE_SIZE_FIXED:
                $textPosition = 0;
                for ($i = 0; $i < $this->stringCount; $i ++) {
                    $strucData = [];
                    $strucData['position'] = $textPosition;
                    $strucData['size'] = $this->stringSize;
                    $strucData['encoding'] = $this->encoding;

                    $strucDataList[] = $strucData;

                    $textPosition += $this->stringSize;
                }
                break;
        }

        foreach ($strucDataList as $strucData) {
            yield LeanElement::createOneFromArray(NodeFactory::TAG_STRING, $strucData, $strucElement->getChildren());
        }
    }
}
