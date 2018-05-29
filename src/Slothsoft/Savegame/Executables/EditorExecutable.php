<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Executables;

use Slothsoft\Farah\Module\Executables\ExecutableBase;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Results\ResultCreator;
use Slothsoft\Savegame\Editor;
use Slothsoft\Farah\Module\Results\ResultInterface;

class EditorExecutable extends ExecutableBase
{
    private $editor;
    public function __construct(Editor $editor) {
        $this->editor = $editor;
    }

    protected function loadResult(FarahUrlStreamIdentifier $type) : ResultInterface
    {
        $creator = new ResultCreator($this, $type);
        return $creator->createDOMWriterResult($this->editor);
    }
}
