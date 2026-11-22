<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Build;

interface BuildableInterface {
    
    /**
     * Return the XML element name for this node.
     */
    public function getBuildTag(): string;
    
    /**
     * Return the node's cache key component, or an empty string when none is available.
     */
    public function getBuildHash(): string;
    
    /**
     * Return the attribute names and values to write on this node's XML element.
     *
     * @return array<string, scalar>
     */
    public function getBuildAttributes(BuilderInterface $builder): array;
    
    /**
     * Return the ancestors used to build this node's cache path.
     *
     * @return iterable<BuildableInterface|\Slothsoft\Savegame\Node\AbstractNode>
     */
    public function getBuildAncestors(): iterable;
    
    /**
     * Return the child nodes to serialize, or null when there are none.
     *
     * @return iterable<BuildableInterface>|null
     */
    public function getBuildChildren(): ?iterable;
    
    /**
     * Add a child node to the build tree.
     */
    public function appendBuildChild(BuildableInterface $childNode): void;
}
