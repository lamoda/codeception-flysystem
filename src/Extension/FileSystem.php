<?php

namespace Lamoda\Codeception\Extension;

use Codeception\Util\Shared\Asserts;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;

class FileSystem
{
    use Asserts;

    /** @var FilesystemInterface */
    protected $flySystem;

    public function __construct(FilesystemInterface $flySystem)
    {
        $this->flySystem = $flySystem;
    }

    /**
     * @param string $path
     * @param string $contents
     * @param array $config
     *
     * @throws FileExistsException
     */
    public function writeFile($path, $contents, array $config = [])
    {
        $this->flySystem->write($path, $contents, $config);
    }

    /**
     * @param string $path
     *
     * @throws FileNotFoundException
     */
    public function deleteFile($path)
    {
        $this->flySystem->delete($path);
    }

    /**
     * @param string $path
     */
    public function clearDir($path)
    {
        $this->flySystem->deleteDir($path);
        $this->flySystem->createDir($path);
    }

    /**
     * @param string $path
     * @param string $newPath
     *
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function copyFile($path, $newPath)
    {
        $this->flySystem->copy($path, $newPath);
    }

    /**
     * @param string $path
     *
     * @return array
     */
    public function grabFileList($path)
    {
        $list = [];
        $content = $this->flySystem->listContents($path);

        foreach ($content as $file) {
            if ('file' === $file['type']) {
                $list[] = $file['path'];
            }
        }

        return $list;
    }

    /**
     * @param string $regex
     * @param string $path
     */
    public function dontSeeFileFoundMatches($regex, $path = '')
    {
        foreach ($this->grabFileList($path) as $filename) {
            preg_match($regex, $filename, $matches);
            if (!empty($matches)) {
                $this->fail("File matches found for '{$regex}'");
            }
        }
    }

    /**
     * @param string $regex
     * @param string $path
     */
    public function seeFileFoundMatches($regex, $path = '')
    {
        foreach ($this->grabFileList($path) as $filename) {
            preg_match($regex, $filename, $matches);
            if (!empty($matches)) {
                return;
            }
        }
        $this->fail("no file matches found for '{$regex}'");
    }

    /**
     * @param string $path
     * @param string $needle
     *
     * @throws FileNotFoundException
     */
    public function seeInFile($path, $needle)
    {
        $content = $this->flySystem->read($path);

        if (!$content) {
            $this->fail("can't read file '{$path}'");
        }

        if (false === strpos($content, $needle)) {
            $this->fail("file '{$path}' does not contain search content");
        }
    }

    /**
     * @param string $path
     * @param string $count
     */
    public function seeFilesCount($path, $count)
    {
        $list = $this->grabFileList($path);
        $countList = count($list);

        if ($countList !== $count) {
            $this->fail("see '{$countList} file'");
        }
    }

    /**
     * @param string $path
     */
    public function canSeeFile($path)
    {
        try {
            $this->flySystem->read($path);
        } catch (FileNotFoundException $e) {
            $this->fail("can't see file '{$path}'");
        }
    }

    /**
     * @param string $path
     */
    public function cantSeeFile($path)
    {
        try {
            $this->flySystem->read($path);
        } catch (FileNotFoundException $e) {
            return;
        }

        $this->fail("file is exist '{$path}'");
    }
}
