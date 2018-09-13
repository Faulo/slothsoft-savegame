<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;

abstract class AbstractValueContent extends AbstractContentNode implements BuildableInterface
{

    abstract protected function decodeValue(string $rawValue);

    abstract protected function encodeValue($value): string;

    private $valueId;

    protected $size;

    protected $value;

    public function getBuildAttributes(BuilderInterface $builder): array
    {
        return parent::getBuildAttributes($builder) + [
            'position' => $this->getContentOffset(),
            'value-id' => $this->valueId,
            'size' => $this->size
        ];
    }

    protected function loadStruc(LeanElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->size = $strucElement->hasAttribute('size')
            ? (int) $this->ownerFile->evaluate($strucElement->getAttribute('size'))
            : 1;
        $this->valueId = $this->ownerFile->registerValue($this);
    }

    protected function loadContent(LeanElement $strucElement)
    {
        if ($this->size and $this->ownerFile) {
            $this->setRawValue($this->ownerFile->extractContent($this->contentOffset, $this->size));
        }
        // echo $this->getName() . ': ' . $this->getValue() . PHP_EOL;
    }

    public function setValueId(int $id)
    {
        $this->valueId = $id;
    }

    public function getValueId(): int
    {
        return $this->valueId;
    }

    public function setValue($value, bool $updateContent = false)
    {
        $this->value = $value;
        if ($updateContent) {
            $this->updateContent();
        }
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setRawValue(string $rawValue)
    {
        $this->value = $this->decodeValue($rawValue);
    }

    public function getRawValue()
    {
        return $this->encodeValue($this->value);
    }

    public function updateContent()
    {
        if ($this->size) {
            $this->ownerFile->insertContent($this->contentOffset, $this->size, $this->getRawValue());
        }
    }
    
    public function getContentSize() : int {
        return $this->size;
    }
}
