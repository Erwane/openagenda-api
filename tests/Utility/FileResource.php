<?php
declare(strict_types=1);

/**
 * Copyright (c) Erwane BRETON
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Erwane BRETON
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace OpenAgenda\Test\Utility;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

// resources base dir
if (!defined('TEST_RESOURCES_BASE_DIR')) {
    define('TEST_RESOURCES_BASE_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR);
}

// resources base dir
if (!defined('TEST_RESOURCES_TMP_DIR')) {
    define('TEST_RESOURCES_TMP_DIR', sys_get_temp_dir());
}

/**
 * @coversNothing
 */
class FileResource
{
    /**
     * @var string[] Base resources paths
     */
    protected $baseDir = TEST_RESOURCES_BASE_DIR;

    /**
     * @var string Tmp dir path to copy resources in.
     */
    protected $tmpDir = TEST_RESOURCES_TMP_DIR;

    /**
     * @var string Resources manifest file
     */
    protected $manifest = TEST_RESOURCES_TMP_DIR . 'resources_manifest.json';

    /**
     * Copied resources
     *
     * @var array
     */
    protected $resources = [];

    protected $prefix;

    /**
     * Build instance.
     *
     * @param \PHPUnit\Framework\TestCase $test
     */
    public function __construct(TestCase $test)
    {
        $class = (new ReflectionClass($test))->getShortName();
        $this->prefix = $class . '-' . $test->getName() . '_';

        $this->_cleanResources();
    }

    /**
     * Create fresh resources instance.
     *
     * @param TestCase $test Current test.
     * @return self
     */
    public static function instance(TestCase $test): FileResource
    {
        return new self($test);
    }

    /**
     * Destructor, mark copied resources in a manifest
     */
    public function __destruct()
    {
        if (!$this->resources) {
            return;
        }

        $paths = [];
        if (is_file($this->manifest)) {
            $paths = json_decode(file_get_contents($this->manifest), true);
        }
        foreach ($this->resources as $path) {
            $paths[] = $path;
        }

        $this->_saveManifest($paths);
    }

    /**
     * Clean previous resources.
     *
     * @return void
     */
    protected function _cleanResources()
    {
        if (is_file($this->manifest)) {
            $paths = json_decode(file_get_contents($this->manifest), true);
            foreach ($paths as $k => $path) {
                if (is_file($path)) {
                    unlink($path);
                    unset($paths[$k]);
                }
            }
            $this->_saveManifest($paths);
        }
    }

    /**
     * Save manifest file.
     *
     * @param array $paths Paths
     * @return void
     */
    protected function _saveManifest(array $paths)
    {
        file_put_contents($this->manifest, json_encode($paths, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Look in $_paths for file resource and return path.
     *
     * @param string $path Resource name or path.
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getPath(string $path): string
    {
        $resourcePath = null;

        $paths = $this->getPaths();

        foreach ($paths as $base) {
            $fullPath = $base . $path;
            if (is_file($fullPath)) {
                return $fullPath;
            }
        }

        if (!$resourcePath) {
            throw new InvalidArgumentException(sprintf('Path "%s" not found', $path));
        }

        return $resourcePath;
    }

    /**
     * Get available paths for resources.
     *
     * @return string[]
     */
    public function getPaths(): array
    {
        return [
            $this->baseDir,
        ];
    }

    /**
     * Get resource file infos.
     *
     * @param string $path Resource name or path.
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getInfos(string $path): array
    {
        $resourcePath = $this->getPath($path);

        $fileInfo = pathinfo($resourcePath);
        $hash = hash_file('sha1', $resourcePath);

        return [
            'path' => $resourcePath,
            'filename' => $fileInfo['basename'],
            'hash' => $hash,
            'uniqHash' => hash('sha1', $fileInfo['basename'] . $hash),
        ];
    }

    /**
     * Get resources file content.
     *
     * @param string $path Resource name or path.
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getContent(string $path): string
    {
        return file_get_contents($this->getPath($path));
    }

    /**
     * Get resources file infos with a copy of file resource.
     *
     * @param string $path Resource name or path.
     * @return array
     * @throws \LogicException
     */
    public function get(string $path): array
    {
        $infos = $this->getInfos($path);

        $resourcePath = $infos['path'];

        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0750, true);
        }

        $infos['path'] = $this->tmpDir . $this->prefix . $infos['filename'];

        copy($resourcePath, $infos['path']);

        $this->resources[] = $infos['path'];

        return $infos;
    }
}
