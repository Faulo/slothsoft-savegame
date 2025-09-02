<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Slothsoft\Core\IO\FileInfo;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveExtractorInterface;
use DomainException;
use SplFileInfo;

/**
 * AbstractNodeTest
 *
 * @see AbstractNode
 *
 * @todo auto-generated
 */
class AbstractNodeTest extends TestCase {

    public function testClassExists(): void {
        $this->assertTrue(class_exists(AbstractNode::class), "Failed to load class 'Slothsoft\Savegame\Node\AbstractNode'!");
    }

    private FileInfo $archiveFile;

    private MockObject $extractor;

    private string $extractedFileName = 'test-file';

    private string $extractedFileContents = 'test-file-content';

    private MockObject $editor;

    private MockObject $savegame;

    private ArchiveNode $sut;

    private function init() {
        $this->archiveFile = FileInfoFactory::createFromString('');

        $this->extractor = $this->createMock(ArchiveExtractorInterface::class);
        $this->extractor->method('extractArchive')->willReturnCallback(function (SplFileInfo $archivePath, SplFileInfo $targetDirectory): bool {
            $target = (string) $targetDirectory;
            if (is_dir($target) or mkdir($target, 0777, true)) {
                file_put_contents($target . DIRECTORY_SEPARATOR . $this->extractedFileName, $this->extractedFileContents);
            }
            return true;
        });

        $this->editor = $this->createMock(Editor::class);
        $this->editor->method('findGameFile')
            ->with('test-path')
            ->willReturn($this->archiveFile);

        $this->editor->method('getArchiveExtractor')
            ->with('test-type')
            ->willReturn($this->extractor);

        $this->savegame = $this->createMock(SavegameNode::class);
        $this->savegame->method('getOwnerEditor')->willReturn($this->editor);

        $archive = LeanElement::createOneFromArray(NodeFactory::TAG_ARCHIVE, [
            'path' => 'test-path',
            'type' => 'test-type'
        ]);

        $this->sut = new ArchiveNode();
        $this->sut->init($archive, $this->savegame);
    }

    public function testGetFileNames(): void {
        $this->init();

        $this->sut->load();

        $actual = $this->sut->getFileNames();

        $this->assertEquals([
            $this->extractedFileName
        ], $actual);
    }

    public function testGetFileByNameMissing(): void {
        $this->init();

        $this->sut->load();

        $this->expectException(DomainException::class);

        $this->sut->getFileByName('missing');
    }

    public function testGetFileByName(): void {
        $this->init();

        $this->sut->load();

        $actual = $this->sut->getFileByName($this->extractedFileName);

        $this->assertFileExists((string) $actual);
        $this->assertEquals($this->extractedFileContents, file_get_contents((string) $actual));
    }
}