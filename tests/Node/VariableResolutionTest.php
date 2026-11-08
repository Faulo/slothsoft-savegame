<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Node;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\EditorConfig;
use Slothsoft\Savegame\Node\ArchiveParser\CopyArchiveBuilder;
use Slothsoft\Savegame\Node\ArchiveParser\CopyArchiveExtractor;

/** @runTestsInSeparateProcesses */
final class VariableResolutionTest extends TestCase {
    /**
     * @dataProvider resolutionModes
     * @param list<string> $expected
     */
    public function testRepeatedPointersUseTheSelectedResolution(string $attribute, array $expected): void {
        $file = $this->loadFile("\x04\x06\0\0A\0B\0", '
            <repeat-group group-count="2" group-size="1">
                <integer name="pointer" />
                <string name="text" position-from="root" position="$pointer" type="null-delimited" encoding="ISO-8859-1" ' . $attribute . ' />
            </repeat-group>');
        self::assertSame($expected, $this->namedValues($file, 'text'));
        self::assertSame([4, 6], $this->namedValues($file, 'pointer'));
        self::assertSame(4, $file->getValueByName('pointer')->getValue());
        self::assertSame(4, $file->evaluate('$pointer'));
    }
    
    /**
     * @return iterable<string, array{string, list<string>}>
     */
    public function resolutionModes(): iterable {
        yield 'default remains global' => ['', ['A', 'A']];
        yield 'explicit global' => ['variable-resolution="global"', ['A', 'A']];
        yield 'scoped follows each pointer' => ['variable-resolution="scoped"', ['A', 'B']];
    }
    
    public function testScopedLookupUsesTheNearestSubtreeAndFirstValue(): void {
        $file = $this->loadFile("\x01\x02\x03\x04" . str_repeat("\0", 12), '
            <integer name="pointer" position="0" />
            <group>
                <integer name="pointer" position="1" />
                <group>
                    <group name="pointer">
                        <integer name="pointer" position="2" />
                        <integer name="pointer" position="3" />
                    </group>
                    <integer name="nearest" position-from="root" position="$pointer" variable-resolution="scoped" />
                </group>
                <integer name="enclosing" position-from="root" position="$pointer" variable-resolution="scoped" />
            </group>');
        self::assertSame(3, $file->getValueByName('nearest')->getContentOffset());
        self::assertSame(2, $file->getValueByName('enclosing')->getContentOffset());
    }
    
    public function testModeAppliesToTheNodeAndIsNotInheritedByChildren(): void {
        $file = $this->loadFile("\x01\x02" . str_repeat("\0", 14), '
            <integer name="pointer" position="0" />
            <group>
                <integer name="pointer" position="1" />
                <group position-from="root" position="$pointer" variable-resolution="scoped">
                    <integer name="child" position-from="root" position="$pointer" />
                    <integer name="scoped-child" position-from="root" position="$pointer" variable-resolution="scoped" />
                </group>
            </group>');
        $child = $file->getValueByName('child');
        $parent = $child->getParentNode();
        self::assertInstanceOf(GroupContainer::class, $parent);
        self::assertSame(2, $parent->getContentOffset());
        self::assertSame(1, $child->getContentOffset());
        self::assertSame(2, $file->getValueByName('scoped-child')->getContentOffset());
    }
    
    public function testScopedModeReachesAllExpressionAttributes(): void {
        $content = str_repeat("\xFF", 64);
        $content[0] = "\x01";
        $content[1] = "\x02";
        $content = substr_replace($content, 'ABCD', 30, 4);
        $content = substr_replace($content, "EF\0GH\0", 40, 6);
        $file = $this->loadFile($content, '
            <integer name="setting" position="0" />
            <group>
                <integer name="setting" position="1" />
                <integer name="sized" position="10" size="$setting" variable-resolution="scoped" />
                <select name="masked" position="12" bit-mask="$setting" variable-resolution="scoped" />
                <bit-field name="flags" position="13" size="$setting" variable-resolution="scoped" />
                <repeat-group position="20" group-size="$setting" group-count="$setting" variable-resolution="scoped">
                    <integer name="repeated" />
                </repeat-group>
                <string-dictionary name="fixed" position="30" type="size-fixed" string-size="$setting" string-count="$setting" encoding="ISO-8859-1" variable-resolution="scoped" />
                <string-dictionary name="terminated" position="40" type="null-delimited" string-count="$setting" encoding="ISO-8859-1" variable-resolution="scoped" />
            </group>');
        self::assertSame(65535, $file->getValueByName('sized')->getValue());
        self::assertSame(2, $file->getValueByName('masked')->getValue());
        $positions = [];
        $flags = [];
        $fixed = [];
        $terminated = [];
        foreach ($file->getValueList() as $value) {
            if ($value->getName() === 'repeated') {
                $positions[] = $value->getContentOffset();
            }
            $parent = $value->getParentNode();
            if ($parent instanceof AbstractContentNode) {
                switch ($parent->getName()) {
                    case 'flags':
                        $flags[] = $value->getValue();
                        break;
                    case 'fixed':
                        $fixed[] = $value->getValue();
                        break;
                    case 'terminated':
                        $terminated[] = $value->getValue();
                        break;
                }
            }
        }
        self::assertSame([20, 22], $positions);
        self::assertSame(array_fill(0, 16, true), $flags);
        self::assertSame(['AB', 'CD'], $fixed);
        self::assertSame(['EF', 'GH'], $terminated);
    }
    
    public function testScopedLookupDoesNotResolveAForwardDeclaration(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->loadFile("\x01\x02", '
            <integer name="first" size="$later" variable-resolution="scoped" />
            <integer name="later" position="1" />');
    }
    
    public function testScopedEvaluationUsesCurrentValuesAndRejectsForeignContexts(): void {
        $fields = '<integer name="pointer" /><integer name="context" position="1" />';
        $file = $this->loadFile("\x01\x02", $fields);
        $context = $file->getValueByName('context');
        self::assertSame(2, $file->evaluateScoped('$pointer + 1', $context));
        self::assertSame(2, $file->evaluateScoped('$context', $context));
        $file->getValueByName('pointer')->setValue(7);
        self::assertSame(8, $file->evaluateScoped('$pointer + 1', $context));
        $other = $this->loadFile("\x03\x04", $fields);
        $this->expectException(InvalidArgumentException::class);
        $file->evaluateScoped('$pointer + 1', $other->getValueByName('context'));
    }
    
    public function testUnknownModeIsRejectedByTheParser(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->loadFile("\x01", '<integer variable-resolution="unknown" />', false);
    }
    
    public function testSchemaRejectsUnknownMode(): void {
        $document = $this->document('unused', '<integer variable-resolution="unknown" />');
        $previous = libxml_use_internal_errors(true);
        try {
            self::assertFalse($document->schemaValidateSource($this->schema()->saveXML() ?: throw new \RuntimeException('Cannot serialize schema.')));
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
    
    /**
     * @return list<mixed>
     */
    private function namedValues(FileContainer $file, string $name): array {
        $values = [];
        foreach ($file->getValueList() as $value) {
            if ($value->getName() === $name) {
                $values[] = $value->getValue();
            }
        }
        return $values;
    }
    
    private function document(string $path, string $fields): DOMDocument {
        return (new DOMHelper())->parseDocument(
            '<savegame xmlns="http://schema.slothsoft.net/savegame/editor" version="0.4">'
            . '<archive path="' . $path . '" type="Raw">'
            . '<file file-name="1">' . $fields . '</file></archive></savegame>'
        );
    }
    
    private function schema(): DOMDocument {
        return Module::resolveToDOMWriter(FarahUrl::createFromReference('farah://slothsoft@savegame/schema/editor/0.4'))->toDocument();
    }
    
    private function loadFile(string $content, string $fields, bool $validate = true): FileContainer {
        $source = FileInfoFactory::createFromString($content);
        $document = $this->document($source->getFilename(), $fields);
        if ($validate) {
            self::assertTrue($document->schemaValidateSource($this->schema()->saveXML() ?: throw new \RuntimeException('Cannot serialize schema.')));
        }
        $config = new EditorConfig(
            FileInfoFactory::createFromPath($source->getPath()),
            FileInfoFactory::createFromPath(temp_dir(__CLASS__, 'user-')),
            FileInfoFactory::createFromPath(temp_dir(__CLASS__, 'cache-')),
            FileInfoFactory::createFromDocument($document),
            ['Raw' => new CopyArchiveExtractor()],
            ['Raw' => new CopyArchiveBuilder()]
        );
        return (new Editor($config))->loadArchive($source->getFilename(), true)->getFileNodeByName('1');
    }
}
