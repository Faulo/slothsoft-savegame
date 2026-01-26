<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuilderInterface;
use SplFileInfo;

final class ImageValue extends AbstractValueContent implements FileWriterInterface, StringWriterInterface {
    
    private int $width;
    
    private int $height;
    
    private int $bitplanes;
    
    private int $imageId;
    
    public function getBuildTag(): string {
        return 'image';
    }
    
    public function getBuildAttributes(BuilderInterface $builder): array {
        return parent::getBuildAttributes($builder) + [
            'value' => $builder->escapeAttribute($this->value),
            'width' => $this->width,
            'height' => $this->height,
            'bitplanes' => $this->bitplanes,
            'image-id' => $this->imageId
        ];
    }
    
    public function getWidth(): int {
        return $this->width;
    }
    
    public function getHeight(): int {
        return $this->height;
    }
    
    public function getBitplanes(): int {
        return $this->bitplanes;
    }
    
    public function getImageId(): int {
        return $this->imageId;
    }
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        
        $this->value = '';
        $this->width = (int) $strucElement->getAttribute('width');
        $this->height = (int) $strucElement->getAttribute('height');
        $this->bitplanes = (int) $strucElement->getAttribute('bitplanes');
        $this->size = (int) $strucElement->getAttribute('size'); // $this->height * $this->width * 5 / 8;
        $this->imageId = $this->ownerFile->registerImage($this);
    }
    
    protected function decodeValue(string $rawValue) {
        return $this->getConverter()->decodeBinary($rawValue);
    }
    
    protected function encodeValue($value): string {
        return $this->getConverter()->encodeBinary($value);
    }
    
    public function toFile(): SplFileInfo {
        return $this->getOwnerFile()->toFile();
    }
    
    public function toFileName(): string {
        return $this->getOwnerFile()->toFileName();
    }
    
    public function toString(): string {
        return $this->getOwnerFile()->toString();
    }
}
