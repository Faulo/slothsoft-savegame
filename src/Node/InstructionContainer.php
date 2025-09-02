<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuilderInterface;

class InstructionContainer extends AbstractContainerContent {

    private string $type;

    private string $dictionaryRef;

    public function getBuildTag(): string {
        return 'instruction';
    }

    public function getBuildAttributes(BuilderInterface $builder): array {
        return parent::getBuildAttributes($builder) + [
            'type' => $this->type,
            'dictionary-ref' => $this->dictionaryRef
        ];
    }

    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);

        $this->type = (string) $strucElement->getAttribute('type');
        $this->dictionaryRef = (string) $strucElement->getAttribute('dictionary-ref');
    }
}
