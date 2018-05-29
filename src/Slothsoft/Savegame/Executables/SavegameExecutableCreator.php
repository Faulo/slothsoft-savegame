<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Executables;

use Slothsoft\Farah\Module\Executables\ExecutableCreator;
use Slothsoft\Farah\Module\Executables\ExecutableInterface;
use Slothsoft\Savegame\Editor;

class SavegameExecutableCreator extends ExecutableCreator {
    public function createEditorExecutable(Editor $editor) : ExecutableInterface {
        return $this->initExecutable(new EditorExecutable($editor));
    }
}
