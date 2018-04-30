<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Build;

use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;

class XmlBuilder implements BuilderInterface
{

    private $tagBlacklist = [];

    public function registerTagBlacklist(array $list)
    {
        foreach ($list as $key) {
            $this->tagBlacklist[$key] = true;
        }
    }

    public function clearTagBlacklist()
    {
        $this->tagBlacklist = [];
    }

    private $attributeBlacklist = [];

    public function registerAttributeBlacklist(array $list)
    {
        foreach ($list as $key) {
            $this->attributeBlacklist[$key] = true;
        }
    }

    public function clearAttributeBlacklist()
    {
        $this->attributeBlacklist = [];
    }

    public function buildStream(BuildableInterface $node)
    {
        $handle = fopen('php://temp', StreamWrapperInterface::MODE_CREATE_READWRITE);
        $this->appendBuildable($handle, $node);
        fseek($handle, 0);
        return $handle;
    }

    public function buildString(BuildableInterface $node): string
    {
        $handle = $this->buildStream($node);
        $ret = stream_get_contents($handle);
        fclose($handle);
        return $ret;
    }

    public function escapeAttribute(string $data): string
    {
        return htmlspecialchars($data, ENT_COMPAT | ENT_XML1, 'UTF-8');
    }

    private function append($handle, string $data)
    {
        fwrite($handle, $data);
    }

    private function appendBuildable($handle, BuildableInterface $node)
    {
        $tag = $node->getBuildTag();
        $attributes = $node->getBuildAttributes($this);
        $children = $node->getBuildChildren();
        
        if (isset($this->tagBlacklist[$tag]) and $children === null) {
            return;
        }
        
        $this->append($handle, "<$tag");
        
        foreach ($attributes as $key => $val) {
            if ($val !== '' and ! isset($this->attributeBlacklist[$key])) {
                $this->append($handle, " $key=\"$val\"");
            }
        }
        if ($children) {
            $this->append($handle, ">");
            foreach ($children as $child) {
                $this->appendBuildable($handle, $child);
            }
            $this->append($handle, "</$tag>");
        } else {
            $this->append($handle, "/>");
        }
    }
}