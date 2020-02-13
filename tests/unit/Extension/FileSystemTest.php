<?php

namespace Lamoda\Codeception\Tests\Extension;

use Codeception\Test\Unit;
use Lamoda\Codeception\Extension\FileSystem;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use PHPUnit\Framework\AssertionFailedError;

class FileSystemTest extends Unit
{
    /**
     * @param array $parameters
     * @param array $expectedParameters
     * @param bool  $operationResult
     *
     * @throws \League\Flysystem\FileExistsException
     *
     * @dataProvider dataWriteFile
     */
    public function testWriteFile($parameters, $expectedParameters, $operationResult)
    {
        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('write')
            ->with(...$expectedParameters)
            ->willReturn($operationResult);

        $fileSystem = new FileSystem($flySystem);

        $this->assertEquals($operationResult, $fileSystem->writeFile(...$parameters));
    }

    public function dataWriteFile()
    {
        return [
            [
                'Parameters' => ['path/to/file', 'file content', ['key' => 'value']],
                'Expected parameters' => ['path/to/file', 'file content', ['key' => 'value']],
                'Operation result' => true,
            ],
            [
                'Parameters' => ['path/to/file', 'file content'],
                'Expected parameters' => ['path/to/file', 'file content', []],
                'Operation result' => true,
            ],
            [
                'Parameters' => ['path/to/file', 'file content'],
                'Expected parameters' => ['path/to/file', 'file content', []],
                'Operation result' => false,
            ],
        ];
    }

    /**
     * @param string $path
     * @param bool   $operationResult
     *
     * @throws FileNotFoundException
     *
     * @dataProvider dataFilePath
     */
    public function testDeleteFile($path, $operationResult)
    {
        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('delete')
            ->with($path)
            ->willReturn($operationResult);

        $fileSystem = new FileSystem($flySystem);

        $this->assertEquals($operationResult, $fileSystem->deleteFile($path));
    }

    /**
     * @param string $path
     * @param bool   $operationResult
     *
     * @dataProvider dataFilePath
     */
    public function testClearDir($path, $operationResult)
    {
        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('deleteDir')
            ->with($path)
            ->willReturn(true);
        $flySystem
            ->expects($this->once())
            ->method('createDir')
            ->with($path)
            ->willReturn($operationResult);

        $fileSystem = new FileSystem($flySystem);
        $this->assertEquals($operationResult, $fileSystem->clearDir($path));
    }

    public function dataFilePath()
    {
        return [
            [
                'Path' => 'path/to/file',
                'Operation result' => true,
            ],
            [
                'Path' => 'path/to/file',
                'Operation result' => false,
            ],
        ];
    }

    /**
     * @param string $path
     */
    public function testClearDirErrorOnDeleting()
    {
        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('deleteDir')
            ->with('/some/path')
            ->willReturn(false);
        $flySystem
            ->expects($this->never())
            ->method('createDir');

        $fileSystem = new FileSystem($flySystem);
        $this->assertFalse($fileSystem->clearDir('/some/path'));
    }

    /**
     * @param string $path
     * @param bool   $operationResult
     *
     * @dataProvider dataDirPath
     */
    public function testCreateDir($path, $operationResult)
    {
        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('createDir')
            ->with($path)
            ->willReturn($operationResult);

        $fileSystem = new FileSystem($flySystem);
        $this->assertEquals($operationResult, $fileSystem->createDir($path));
    }

    public function dataDirPath()
    {
        return [
            [
                'Path' => 'path/to/dir',
                'Operation result' => true,
            ],
            [
                'Path' => 'path/to/dir',
                'Operation result' => false,
            ],
        ];
    }

    /**
     * @param $path
     * @param $newPath
     * @param bool   $operationResult
     *
     * @throws FileNotFoundException
     * @throws \League\Flysystem\FileExistsException
     *
     * @dataProvider dataFilePaths
     */
    public function testCopyFile($path, $newPath, $operationResult)
    {
        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('copy')
            ->with($path, $newPath)
            ->willReturn($operationResult);

        $fileSystem = new FileSystem($flySystem);
        $this->assertEquals($operationResult, $fileSystem->copyFile($path, $newPath));
    }

    public function dataFilePaths()
    {
        return [
            [
                'Path' => 'old/path/to/file',
                'New path' => 'new/path/to/file',
                'Operation result' => true,
            ],
            [
                'Path' => 'old/path/to/file',
                'New path' => 'new/path/to/file',
                'Operation result' => false,
            ],
        ];
    }

    /**
     * @param string $path
     * @param array $files
     * @param array $expectedList
     *
     * @dataProvider dataGrabFileList
     */
    public function testGrabFileList($path, $files, $expectedList)
    {
        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('listContents')
            ->with($path)
            ->willReturn($files);

        $fileSystem = new FileSystem($flySystem);

        $this->assertEquals(
            $expectedList,
            $fileSystem->grabFileList($path)
        );
    }

    public function dataGrabFileList()
    {
        $fileWithInvalidType = [
            'type' => 'not_file',
            'path' => 'not/file/path',
        ];

        $file = [
            'type' => 'file',
            'path' => 'file/path',
        ];

        return [
            'Empty directory' => [
                'path/to/directory',
                [],
                [],
            ],
            'File with invalid type' => [
                'path/to/directory',
                [$fileWithInvalidType],
                [],
            ],
            'Valid file' => [
                'path/to/directory',
                [$file],
                [
                    'file/path',
                ],
            ],
        ];
    }

    /**
     * @param string $path
     * @param array $files
     * @param string $regexp
     *
     * @dataProvider dataDontSeeFileFoundMatchesOk
     */
    public function testDontSeeFileFoundMatchesOk($path, $files, $regexp)
    {
        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('listContents')
            ->with($path)
            ->willReturn($files);

        $fileSystem = new FileSystem($flySystem);

        $fileSystem->dontSeeFileFoundMatches($regexp, $path);
    }

    public function dataDontSeeFileFoundMatchesOk()
    {
        $file = [
            'type' => 'file',
            'path' => 'file/path',
        ];

        return [
            'Empty directory' => [
                '',
                [],
                '',
            ],
            'Files without matches' => [
                'directory/path',
                [$file],
                '/(pattern)/',
            ],
        ];
    }

    /**
     * @param string $path
     * @param array $files
     * @param string $regexp
     * @param string $expectedExceptionMessage
     *
     * @dataProvider dataDontSeeFileFoundMatchesFailed
     */
    public function testDontSeeFileFoundMatchesFailed($path, $files, $regexp, $expectedExceptionMessage)
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('listContents')
            ->with($path)
            ->willReturn($files);

        $fileSystem = new FileSystem($flySystem);

        $fileSystem->dontSeeFileFoundMatches($regexp, $path);
    }

    public function dataDontSeeFileFoundMatchesFailed()
    {
        $file = [
            'type' => 'file',
            'path' => 'file/path/with/pattern',
        ];

        return [
            'Files with matches' => [
                'directory/path',
                [$file],
                '/(pattern)/',
                "File matches found for '/(pattern)/'",
            ],
        ];
    }

    /**
     * @param string $path
     * @param array $files
     * @param string $regexp
     *
     * @dataProvider dataSeeFileFoundMatchesOk
     */
    public function testSeeFileFoundMatchesOk($path, $files, $regexp)
    {
        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('listContents')
            ->with($path)
            ->willReturn($files);

        $fileSystem = new FileSystem($flySystem);

        $fileSystem->seeFileFoundMatches($regexp, $path);
    }

    public function dataSeeFileFoundMatchesOk()
    {
        $file = [
            'type' => 'file',
            'path' => 'file/path/with/pattern',
        ];

        return [
            'Files with matches' => [
                'directory/path',
                [$file],
                '/(pattern)/',
            ],
        ];
    }

    /**
     * @param string $path
     * @param array $files
     * @param string $regexp
     * @param string $expectedExceptionMessage
     *
     * @dataProvider dataSeeFileFoundMatchesFailed
     */
    public function testSeeFileFoundMatchesFailed($path, $files, $regexp, $expectedExceptionMessage)
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('listContents')
            ->with($path)
            ->willReturn($files);

        $fileSystem = new FileSystem($flySystem);

        $fileSystem->seeFileFoundMatches($regexp, $path);
    }

    public function dataSeeFileFoundMatchesFailed()
    {
        $file = [
            'type' => 'file',
            'path' => 'file/path',
        ];

        return [
            'Empty directory' => [
                '',
                [],
                '',
                "no file matches found for ''",
            ],
            'Files without matches' => [
                'directory/path',
                [$file],
                '/(pattern)/',
                "no file matches found for '/(pattern)/'",
            ],
        ];
    }

    /**
     * @param string $path
     * @param string $content
     * @param string $needle
     *
     * @throws FileNotFoundException
     *
     * @dataProvider dataSeeInFileOk
     */
    public function testSeeInFileOk($path, $content, $needle)
    {
        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('read')
            ->with($path)
            ->willReturn($content);

        $fileSystem = new FileSystem($flySystem);

        $fileSystem->seeInFile($path, $needle);
    }

    public function dataSeeInFileOk()
    {
        return [
            [
                'path/to/file',
                'File content',
                'content',
            ],
        ];
    }

    /**
     * @param string $path
     * @param string $content
     * @param string $needle
     * @param string $expectedExceptionMessage
     *
     * @throws FileNotFoundException
     *
     * @dataProvider dataSeeInFileFailed
     */
    public function testSeeInFileFailed($path, $content, $needle, $expectedExceptionMessage)
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('read')
            ->with($path)
            ->willReturn($content);

        $fileSystem = new FileSystem($flySystem);

        $fileSystem->seeInFile($path, $needle);
    }

    public function dataSeeInFileFailed()
    {
        return [
            [
                'path/to/file',
                '',
                'needle',
                "can't read file 'path/to/file'",
            ],
            [
                'path/to/file',
                'File content',
                'needle',
                "file 'path/to/file' does not contain search content",
            ],
        ];
    }

    /**
     * @param string $path
     * @param array $files
     * @param int $expectedCount
     *
     * @dataProvider dataTestSeeFilesCountOk
     */
    public function testSeeFilesCountOk($path, $files, $expectedCount)
    {
        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('listContents')
            ->with($path)
            ->willReturn($files);

        $fileSystem = new FileSystem($flySystem);

        $fileSystem->seeFilesCount($path, $expectedCount);
    }

    public function dataTestSeeFilesCountOk()
    {
        $file = [
            'type' => 'file',
            'path' => 'file/path',
        ];

        return [
            [
                'path/to/directory',
                [],
                0,
            ],
            [
                'path/to/directory',
                [$file],
                1,
            ],
        ];
    }

    /**
     * @param string $path
     * @param array $files
     * @param int $expectedCount
     * @param string $expectedExceptionMessage
     *
     * @dataProvider dataTestSeeFilesCountFailed
     */
    public function testSeeFilesCountFailed($path, $files, $expectedCount, $expectedExceptionMessage)
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('listContents')
            ->with($path)
            ->willReturn($files);

        $fileSystem = new FileSystem($flySystem);

        $fileSystem->seeFilesCount($path, $expectedCount);
    }

    public function dataTestSeeFilesCountFailed()
    {
        $file = [
            'type' => 'file',
            'path' => 'file/path',
        ];

        return [
            [
                'path/to/directory',
                [$file],
                2,
                "see '1 file'",
            ],
        ];
    }

    /**
     * @param string $path
     *
     * @dataProvider dataCanSeeFileOk
     */
    public function testCanSeeFileOk($path)
    {
        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('read')
            ->with($path);

        $fileSystem = new FileSystem($flySystem);

        $fileSystem->canSeeFile($path);
    }

    public function dataCanSeeFileOk()
    {
        return [
            ['path/to/file'],
        ];
    }

    /**
     * @param string $path
     * @param string $expectedExceptionMessage
     *
     * @dataProvider dataCanSeeFileFailed
     */
    public function testCanSeeFileFailed($path, $expectedExceptionMessage)
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $flySystem = $this->createMock(FilesystemInterface::class);
        $flySystem
            ->expects($this->once())
            ->method('read')
            ->with($path)
            ->willThrowException(new FileNotFoundException($path));

        $fileSystem = new FileSystem($flySystem);

        $fileSystem->canSeeFile($path);
    }

    public function dataCanSeeFileFailed()
    {
        return [
            [
                'path/to/file',
                "can't see file 'path/to/file'",
            ],
        ];
    }
}
