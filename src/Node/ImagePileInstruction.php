<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuilderInterface;

class ImagePileInstruction extends AbstractInstructionContent {

    private int $width;

    private int $height;

    private int $size;

    private int $bitplanes;

    private string $imageDimensions;

    public function getBuildAttributes(BuilderInterface $builder): array {
        return parent::getBuildAttributes($builder) + [
            'width' => $this->width,
            'height' => $this->height,
            'size' => $this->size,
            'bitplanes' => $this->bitplanes,
            'image-dimensions' => $this->imageDimensions
        ];
    }

    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);

        $this->width = 0;
        $this->height = 0;
        $this->size = 0;
        $this->bitplanes = (int) $strucElement->getAttribute('bitplanes');
        $this->imageDimensions = (string) $strucElement->getAttribute('image-dimensions');
        $this->imageDimensions = preg_replace('~\s+~', ' ', trim($this->imageDimensions));
    }

    protected function getInstructionType(): string {
        return NodeFactory::TAG_IMAGE_PILE;
    }

    protected function loadInstruction(LeanElement $strucElement): iterable {
        $this->width = 0;
        $this->height = 0;
        $this->size = 0;
        foreach (explode(' ', $this->imageDimensions) as $imageDimension) {
            $imageDimension = explode('x', $imageDimension);
            $width = (int) $imageDimension[0];
            $height = (int) $imageDimension[1];

            $strucData = [];
            $strucData['width'] = $width;
            $strucData['height'] = $height;
            $strucData['size'] = $strucData['width'] * $strucData['height'] * 5 / 8;
            $strucData['bitplanes'] = $this->bitplanes;
            $strucData['position'] = $this->size;

            yield LeanElement::createOneFromArray(NodeFactory::TAG_IMAGE, $strucData, $strucElement->getChildren());

            $this->width = max($width, $this->width);
            $this->height += $height;
            $this->size += $strucData['size'];
        }
    }
}
