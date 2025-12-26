<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuilderInterface;

final class ImageMapInstruction extends AbstractInstructionContent {
    
    private int $width;
    
    private int $height;
    
    private int $bitplanes;
    
    private int $imageCount;
    
    public function getBuildAttributes(BuilderInterface $builder): array {
        return parent::getBuildAttributes($builder) + [
            'width' => $this->width,
            'height' => $this->height,
            'bitplanes' => $this->bitplanes,
            'image-count' => $this->imageCount
        ];
    }
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        
        $this->width = (int) $strucElement->getAttribute('width');
        $this->height = (int) $strucElement->getAttribute('height');
        $this->bitplanes = (int) $strucElement->getAttribute('bitplanes');
        $this->imageCount = (int) $strucElement->getAttribute('image-count');
    }
    
    protected function getInstructionType(): string {
        return NodeFactory::TAG_IMAGE_MAP;
    }
    
    protected function loadInstruction(LeanElement $strucElement): iterable {
        $strucData = [];
        $strucData['width'] = $this->width;
        $strucData['height'] = $this->height / $this->imageCount;
        $strucData['size'] = $strucData['width'] * $strucData['height'] * 5 / 8;
        $strucData['bitplanes'] = $this->bitplanes;
        
        for ($i = 0; $i < $this->imageCount; $i ++) {
            $strucData['position'] = $i * $strucData['size'];
            yield LeanElement::createOneFromArray(NodeFactory::TAG_IMAGE, $strucData, $strucElement->getChildren());
        }
    }
}
