<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Build;

final class TestBuildableNode implements BuildableInterface {
    
    public int $attributeReads = 0;
    
    public int $childrenReads = 0;
    
    public function __construct(
        private string $tag,
        public array $attributes = [],
        private ?array $children = null,
        private string $hash = '',
        private array $ancestors = []
    ) {
    }
    
    public function getBuildTag(): string {
        return $this->tag;
    }
    
    public function getBuildHash(): string {
        return $this->hash;
    }
    
    public function getBuildAttributes(BuilderInterface $builder): array {
        $this->attributeReads++;
        $attributes = [];
        foreach ($this->attributes as $name => $value) {
            $attributes[$name] = is_string($value) ? $builder->escapeAttribute($value) : $value;
        }
        return $attributes;
    }
    
    public function getBuildAncestors(): iterable {
        return $this->ancestors;
    }
    
    public function getBuildChildren(): ?iterable {
        $this->childrenReads++;
        return $this->children;
    }
    
    public function appendBuildChild(BuildableInterface $childNode): void {
        $this->children[] = $childNode;
    }
}
