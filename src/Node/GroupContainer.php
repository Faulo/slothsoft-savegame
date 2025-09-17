<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuilderInterface;

class GroupContainer extends AbstractContainerContent {
    
    private string $dictionaryRef;
    
    public function getBuildTag(): string {
        return 'group';
    }
    
    public function getBuildAttributes(BuilderInterface $builder): array {
        return parent::getBuildAttributes($builder) + [
            'dictionary-ref' => $this->dictionaryRef
        ];
    }
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        
        $this->dictionaryRef = (string) $strucElement->getAttribute('dictionary-ref');
    }
}
