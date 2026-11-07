<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Node;

use InvalidArgumentException;
use Slothsoft\Core\XML\LeanElement;

/**
 * Binary representation shared by unsigned numeric fields.
 *
 * @internal
 */
trait IntegerFormatTrait {
    
    private string $byteOrder = 'big';
    
    private ?int $bitMask = null;
    
    private function loadIntegerFormat(LeanElement $strucElement): void {
        $this->byteOrder = (string) $strucElement->getAttribute('byte-order', 'big');
        // Validate even when the node has no content yet.
        $this->getConverter()->decodeInteger("\0", 1, $this->byteOrder);
        if ($strucElement->hasAttribute('bit-mask')) {
            $this->bitMask = (int) $this->ownerFile->evaluate($strucElement->getAttribute('bit-mask'));
            if ($this->size < 1 || $this->size > 4 || $this->bitMask < 1 || $this->bitMask >= 256 ** $this->size) {
                throw new InvalidArgumentException('The bit mask must fit the numeric field and contain at least one bit.');
            }
        }
    }
    
    private function getIntegerFormatAttributes(): array {
        $attributes = [];
        if ($this->byteOrder !== 'big') {
            $attributes['byte-order'] = $this->byteOrder;
        }
        if ($this->bitMask !== null) {
            $attributes['bit-mask'] = $this->bitMask;
        }
        return $attributes;
    }
    
    private function decodeUnsignedInteger(string $rawValue): int {
        $value = $this->getConverter()->decodeInteger($rawValue, $this->size, $this->byteOrder);
        return $this->bitMask === null ? $value : $value & $this->bitMask;
    }
    
    private function encodeUnsignedInteger(int $value): string {
        if ($this->bitMask !== null) {
            if ($value < 0 || ($value & $this->bitMask) !== $value) {
                throw new InvalidArgumentException('The value contains bits outside the numeric field mask.');
            }
            // Read current content so another field sharing this byte can be edited first.
            $rawValue = $this->ownerFile->extractContent($this->contentOffset, $this->size);
            $original = $this->getConverter()->decodeInteger($rawValue, $this->size, $this->byteOrder);
            $value |= $original & ~$this->bitMask;
        }
        return $this->getConverter()->encodeInteger($value, $this->size, $this->byteOrder);
    }
}
