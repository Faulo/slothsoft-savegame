<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
use Slothsoft\Savegame\Build\BuilderInterface;
declare(ticks = 1000);

class InstructionContainer extends AbstractContainerContent
{

    private $type;

    private $dictionaryRef;

    public function getBuildTag(): string
    {
        return 'instruction';
    }
	
	public function getBuildAttributes(BuilderInterface $builder): array
    {
		return parent::getBuildAttributes($builder) + [
			'type' 				=> $this->type,
			'dictionary-ref' 	=> $this->dictionaryRef,
		];
	}

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->type = (string) $strucElement->getAttribute('type');
        $this->dictionaryRef = (string) $strucElement->getAttribute('dictionary-ref');
    }
}
