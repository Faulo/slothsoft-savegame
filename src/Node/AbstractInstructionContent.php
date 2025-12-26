<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;

abstract class AbstractInstructionContent extends AbstractContentNode implements BuildableInterface {
    
    abstract protected function loadInstruction(LeanElement $strucElement): iterable;
    
    abstract protected function getInstructionType(): string;
    
    protected string $dictionaryRef;
    
    public function getBuildTag(): string {
        return 'instruction';
    }
    
    public function getBuildAttributes(BuilderInterface $builder): array {
        return [
            'name' => $this->name,
            'type' => $this->getInstructionType(),
            'dictionary-ref' => $this->dictionaryRef
        ];
    }
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        
        $this->dictionaryRef = (string) $strucElement->getAttribute('dictionary-ref');
    }
    
    protected function loadContent(LeanElement $strucElement): void {}
    
    protected function loadChildren(LeanElement $strucElement): void {
        if ($instructionList = $this->loadInstruction($strucElement)) {
            foreach ($instructionList as $instruction) {
                $this->loadChild($instruction);
            }
        }
    }
}
