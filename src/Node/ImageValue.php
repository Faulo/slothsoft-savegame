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
    
    /**
     * Return the XML tag name for an image field.
     */
    public function getBuildTag(): string {
        return 'image';
    }
    
    /**
     * Include the image value, dimensions, bitplane count, and file-local ID in the XML attributes.
     */
    public function getBuildAttributes(BuilderInterface $builder): array {
        return parent::getBuildAttributes($builder) + [
                'value' => $builder->escapeAttribute($this->value),
                'width' => $this->width,
                'height' => $this->height,
                'bitplanes' => $this->bitplanes,
                'image-id' => $this->imageId
            ];
    }
    
    /**
     * Return the image width in pixels.
     */
    public function getWidth(): int {
        return $this->width;
    }
    
    /**
     * Return the image height in pixels.
     */
    public function getHeight(): int {
        return $this->height;
    }
    
    /**
     * Return the number of bitplanes used by the image.
     */
    public function getBitplanes(): int {
        return $this->bitplanes;
    }
    
    /**
     * Return the image's zero-based registration ID within its file.
     */
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
    
    protected function decodeValue(string $rawValue): string {
        return $this->getConverter()->decodeBinary($rawValue);
    }
    
    protected function encodeValue(mixed $value): string {
        return $this->getConverter()->encodeBinary($value);
    }
    
    /**
     * Return the backing file that contains the image.
     */
    public function toFile(): SplFileInfo {
        return $this->getOwnerFile()->toFile();
    }
    
    /**
     * Return the name of the file that contains the image.
     */
    public function toFileName(): string {
        return $this->getOwnerFile()->getFileName();
    }
    
    /**
     * Return the full in-memory contents of the file that contains the image.
     */
    public function toString(): string {
        return $this->getOwnerFile()->getContent();
    }
}
