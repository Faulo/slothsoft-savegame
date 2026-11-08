<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Node;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Savegame\Build\BuilderInterface;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\EditorConfig;
use Slothsoft\Savegame\Node\ArchiveParser\CopyArchiveBuilder;
use Slothsoft\Savegame\Node\ArchiveParser\CopyArchiveExtractor;

/**
 * @runTestsInSeparateProcesses
 */
final class NumericFormatTest extends TestCase {
    
    private function loadFile(string $content, string $fields): FileContainer {
        $source = FileInfoFactory::createFromString($content);
        $document = (new DOMHelper())->parseDocument(
            '<savegame xmlns="http://schema.slothsoft.net/savegame/editor" version="0.4">'
            . '<archive path="' . $source->getFilename() . '" type="Raw">'
            . '<file file-name="1">' . $fields . '</file></archive></savegame>'
        );
        $schema = Module::resolveToDOMWriter(FarahUrl::createFromReference('farah://slothsoft@savegame/schema/editor/0.4'))->toDocument();
        $this->assertTrue($document->schemaValidateSource($schema->saveXML() ?: throw new \RuntimeException('Cannot serialize schema.')));
        $config = new EditorConfig(
            FileInfoFactory::createFromPath($source->getPath()),
            FileInfoFactory::createFromPath(temp_dir(__CLASS__, 'user-')),
            FileInfoFactory::createFromPath(temp_dir(__CLASS__, 'cache-')),
            FileInfoFactory::createFromDocument($document),
            ['Raw' => new CopyArchiveExtractor()],
            ['Raw' => new CopyArchiveBuilder()]
        );
        $archive = (new Editor($config))->loadArchive($source->getFilename(), true);
        return $archive->getFileNodeByName('1');
    }
    
    /**
     * @dataProvider unsignedNodeTypes
     */
    public function testMaskedWritePreservesOtherFields(string $tag): void {
        $file = $this->loadFile("\x91\x55\xaa", '<' . $tag . ' name="equipment" position="0" bit-mask="0x7f"/>'
            . '<bit name="equipped" position="0" bit="7"/>');
        $equipment = $file->getValueByName('equipment');
        $equipped = $file->getValueByName('equipped');
        $this->assertSame(17, $equipment->getValue());
        $this->assertSame("\x91\x55\xaa", $file->getContent());
        $equipment->setValue(32, true);
        $this->assertSame("\xa0\x55\xaa", $file->getContent());
        $equipped->setValue(false, true);
        $equipment->setValue(2, true);
        $this->assertSame("\x02\x55\xaa", $file->getContent());
        $equipped->setValue(true, true);
        $equipment->setValue(40, true);
        $this->assertSame("\xa8\x55\xaa", $file->getContent());
        $this->expectException(InvalidArgumentException::class);
        $equipment->setValue(128, true);
    }
    
    /**
     * @return iterable<string, array{string}>
     */
    public function unsignedNodeTypes(): iterable {
        yield 'integer' => ['integer'];
        yield 'select' => ['select'];
    }
    
    public function testLittleEndianFieldsAndLegacyDefaults(): void {
        $file = $this->loadFile("\x12\x34\x56\x78\x9a\xbc\xfe\xff\x55", '<integer name="legacy" position="0" size="2"/>'
            . '<integer name="little" position="2" size="3" byte-order="little"/>'
            . '<select name="selected" position="5" size="1" bit-mask="0x0f"/>'
            . '<signed-integer name="signed" position="6" size="2" byte-order="little"/>');
        $this->assertSame(0x1234, $file->getValueByName('legacy')->getValue());
        $this->assertSame(0x9a7856, $file->getValueByName('little')->getValue());
        $this->assertSame(12, $file->getValueByName('selected')->getValue());
        $this->assertSame(-2, $file->getValueByName('signed')->getValue());
        foreach ($file->getValueList() as $value) {
            $value->updateContent();
        }
        $this->assertSame("\x12\x34\x56\x78\x9a\xbc\xfe\xff\x55", $file->getContent());
        $file->getValueByName('little')->setValue(999999, true);
        $this->assertSame("\x12\x34\x3f\x42\x0f\xbc\xfe\xff\x55", $file->getContent());
        $builder = $this->createMock(BuilderInterface::class);
        $attributes = $file->getValueByName('legacy')->getBuildAttributes($builder);
        $this->assertArrayNotHasKey('byte-order', $attributes);
        $this->assertArrayNotHasKey('bit-mask', $attributes);
        $this->assertSame('little', $file->getValueByName('little')->getBuildAttributes($builder)['byte-order']);
        $this->assertSame(15, $file->getValueByName('selected')->getBuildAttributes($builder)['bit-mask']);
    }
    
    public function testMultiByteLittleEndianMask(): void {
        $file = $this->loadFile("\x34\xa2", '<select name="value" size="2" byte-order="little" bit-mask="0x0fff"/>');
        $value = $file->getValueByName('value');
        $this->assertSame(0x234, $value->getValue());
        $value->setValue(0x567, true);
        $this->assertSame("\x67\xa5", $file->getContent());
    }
    
    /**
     * @dataProvider invalidMasks
     */
    public function testRejectsInvalidMask(string $mask): void {
        $this->expectException(InvalidArgumentException::class);
        $this->loadFile("\x91", '<integer name="value" bit-mask="' . $mask . '"/>');
    }
    
    /**
     * @return iterable<string, array{string}>
     */
    public function invalidMasks(): iterable {
        yield 'empty' => ['0'];
        yield 'negative' => ['-1'];
        yield 'too large' => ['0x100'];
    }
}
