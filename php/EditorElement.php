<?php
namespace Slothsoft\Savegame;

use DomainException;
use DS\Vector;
declare(ticks = 1000);

class EditorElement
{

    const NODE_TYPES = [
        'savegame.editor' => 1,
        'global' => 2,
        'archive' => 3,
        'for-each-file' => 4,
        'file' => 5,
        
        'binary' => 10,
        'integer' => 11,
        'signed-integer' => 12,
        'string' => 13,
        'bit' => 14,
        'select' => 15,
        'event-script' => 16,
        
        'group' => 20,
        'instruction' => 21,
        
        'bit-field' => 30,
        'string-dictionary' => 31,
        'event-dictionary' => 32,
        'event' => 33,
        'event-step' => 34,
        'repeat-group' => 35,
        'use-global' => 36,
    ];

    public static function getNodeTag(int $val)
    {
        $key = array_search($val, self::NODE_TYPES, true);
        if ($key === false) {
            throw new DomainException('unknown node type: ' . $val);
        }
        return $key;
    }

    public static function getNodeType(string $key)
    {
        if (! isset(self::NODE_TYPES[$key])) {
            throw new DomainException('unknown node tag: ' . $key);
        }
        return self::NODE_TYPES[$key];
    }

    private $type;

    private $attributes;

    private $children;

    /**
     *
     * @param int $type
     * @param array $attributes
     * @param \DS\Vector $children
     */
    public function __construct(int $type, array $attributes, Vector $children)
    {
        $this->type = $type;
        $this->attributes = $attributes;
        $this->children = $children;
    }

    /**
     *
     * @param int $type
     * @param array $attributes
     * @param \DS\Vector $children
     * @return \Slothsoft\Savegame\EditorElement
     */
    public function clone(int $type = null, array $attributes = null, Vector $children = null)
    {
        return new EditorElement($type === null ? $this->type : $type, $attributes === null ? $this->attributes : $attributes + $this->attributes, $children === null ? $this->children : $children);
    }

    /**
     *
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     *
     * @return string
     */
    public function getTag(): string
    {
        return self::getNodeTag($this->type);
    }

    /**
     *
     * @param string $key
     * @return bool
     */
    public function hasAttribute(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key, $default = null, NodeEvaluatorInterface $evaluator = null)
    {
        if ($evaluator === null) {
            return $this->attributes[$key] ?? $default;
        } else {
            return isset($this->attributes[$key])
            ? $evaluator->evaluate($this->attributes[$key])
            : $default;
        }
        
    }

    /**
     *
     * @param string $key
     * @param mixed $val
     */
    public function setAttribute(string $key, $val)
    {
        $this->attributes[$key] = $val;
    }

    /**
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     *
     * @return \DS\Vector
     */
    public function getChildren(): Vector
    {
        return $this->children;
    }
}