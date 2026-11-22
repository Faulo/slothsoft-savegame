<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Node;

use OverflowException;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Core\ServerEnvironment;
use Slothsoft\FarahTesting\TestUtils;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\EditorConfig;
use Slothsoft\Savegame\Node\ArchiveParser\TestArchiveExtractor;

/**
 * FileContainerTest
 *
 * @see FileContainer
 */
final class FileContainerTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FileContainer::class), "Failed to load class 'Slothsoft\Savegame\Node\FileContainer'!");
    }
    
    public function testEvaluateHexadecimalIntegerLimits(): void {
        $file = new FileContainer();
        self::assertSame(PHP_INT_MAX, $file->evaluate('0x' . dechex(PHP_INT_MAX)));
        self::assertSame(PHP_INT_MIN, $file->evaluate('-0x00' . strtoupper(dechex(PHP_INT_MIN))));
    }

    /**
     * @dataProvider overflowingExpressionProvider
     * @runInSeparateProcess
     */
    public function testEvaluateRejectsOverflow(string $expression): void {
        $file = new FileContainer();
        $this->expectException(OverflowException::class);
        $file->evaluate($expression);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public function overflowingExpressionProvider(): iterable {
        yield 'positive hex outside the range' => ['0x' . dechex(PHP_INT_MIN)];
        yield 'negative hex outside the range' => ['-0x' . dechex(PHP_INT_MIN | 1)];
        yield 'negated minimum integer' => ['-(PHP_INT_MIN)'];
    }

    /**
     * @dataProvider ownerArchiveProvider
     * @runInSeparateProcess
     */
    public function testGetOwnerArchive(string $fileStructure): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
        $document = (new DOMHelper())->parseDocument(
            '<savegame xmlns="http://schema.slothsoft.net/savegame/editor" version="0.4">'
            . '<archive path="archive.txt" type="test">' . $fileStructure . '</archive></savegame>'
        );
        $config = new EditorConfig(
            FileInfoFactory::createFromPath('test-files'),
            FileInfoFactory::createFromPath(ServerEnvironment::getDataDirectory()),
            FileInfoFactory::createFromPath(ServerEnvironment::getCacheDirectory()),
            FileInfoFactory::createFromDocument($document),
            ['test' => new TestArchiveExtractor()],
            []
        );
        $archive = (new Editor($config))->loadArchive('archive.txt', true);
        
        $files = [];
        /** @var FileContainer $file */
        foreach ($archive->getFileNodes() as $file) {
            $this->assertSame($archive, $file->getOwnerArchive());
            $files[] = $file->getFileName();
        }
        $this->assertSame(['001'], $files);
    }
    
    /**
     * @return array<string, array{string}>
     */
    public function ownerArchiveProvider(): array {
        return [
            'direct file' => ['<file file-name="001"/>'],
            'for-each-file' => ['<for-each-file list="001"/>']
        ];
    }
}
