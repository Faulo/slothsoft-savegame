<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class GroupContainer extends AbstractContainerContent
{

    public function getBuildTag(): string
    {
        return 'group';
    }
}
