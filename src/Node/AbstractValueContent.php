<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;

abstract class AbstractValueContent extends AbstractContentNode implements BuildableInterface {
    
    abstract protected function decodeValue(string $rawValue) : mixed;
    
    abstract protected function encodeValue(mixed $value): string;
    
    private int $valueId;
    
    protected int $size;
    
    /** Decoded data whose type depends on the concrete value node. */
    protected mixed $value = null;
    
    /**
     * Return the field name, file offset, value ID, and byte size as XML attributes.
     */
    public function getBuildAttributes(BuilderInterface $builder): array {
        return [
            'name' => $this->name,
            'position' => $this->getContentOffset(),
            'value-id' => $this->valueId,
            'size' => $this->size
        ];
    }
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        
        $this->size = $strucElement->hasAttribute('size') ? $this->evaluateExpression($strucElement->getAttribute('size')) : 1;
        $this->valueId = $this->ownerFile->registerValue($this);
    }
    
    protected function loadContent(LeanElement $strucElement): void {
        if ($this->size) {
            $this->setRawValue($this->ownerFile->extractContent($this->contentOffset, $this->size));
        }
        // echo $this->getName() . ': ' . $this->getValue() . PHP_EOL;
    }
    
    /**
     * Assign the identifier used to look up this value in the savegame.
     */
    public function setValueId(int $id): void {
        $this->valueId = $id;
    }
    
    /**
     * Return the identifier used to look up this value in the savegame.
     */
    public function getValueId(): int {
        return $this->valueId;
    }
    
    /**
     * Convert the input to the current value's type, store it if changed, and optionally update the file bytes.
     */
    public function setValue(mixed $value, bool $updateContent = false): bool {
        settype($value, gettype($this->value));
        
        if ($this->value !== $value) {
            $this->value = $value;
            if ($updateContent) {
                $this->updateContent();
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Return the decoded field value in the concrete node's native representation.
     */
    public function getValue(): mixed {
        return $this->value;
    }
    
    /**
     * Decode and store a value from its raw file bytes.
     */
    public function setRawValue(string $rawValue): void {
        $this->value = $this->decodeValue($rawValue);
    }
    
    /**
     * Encode the current value as bytes for the savegame file.
     */
    public function getRawValue(): string {
        return $this->encodeValue($this->value);
    }
    
    /**
     * Write the encoded value back to its field when the field has a nonzero size.
     */
    public function updateContent(): void {
        if ($this->size) {
            $this->ownerFile->insertContent($this->contentOffset, $this->size, $this->getRawValue());
        }
    }
    
    /**
     * Return the size of the field in bytes.
     */
    public function getContentSize(): int {
        return $this->size;
    }
}
