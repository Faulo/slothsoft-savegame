<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;

abstract class AbstractInstructionContent extends AbstractContentNode implements BuildableInterface {

    abstract protected function loadInstruction(LeanElement $strucElement);

    abstract protected function getInstructionType(): string;

    // protected $dictionary;
    protected $dictionaryRef;

    public function getBuildTag(): string {
        return 'instruction';
    }

    public function getBuildAttributes(BuilderInterface $builder): array {
        return parent::getBuildAttributes($builder) + [
            'type' => $this->getInstructionType(),
            'dictionary-ref' => $this->dictionaryRef
        ];
    }

    protected function loadStruc(LeanElement $strucElement) {
        parent::loadStruc($strucElement);

        $this->dictionaryRef = (string) $strucElement->getAttribute('dictionary-ref');
    }

    protected function loadContent(LeanElement $strucElement) {}

    protected function loadChildren(LeanElement $strucElement) {
        if ($instructionList = $this->loadInstruction($strucElement)) {
            foreach ($instructionList as $instruction) {
                $this->loadChild($instruction);
            }
        }
    }
}
