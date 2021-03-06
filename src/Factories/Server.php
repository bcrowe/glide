<?php

namespace League\Glide\Factories;

use Intervention\Image\ImageManager;
use InvalidArgumentException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use League\Glide\Api;
use League\Glide\Manipulators\Blur;
use League\Glide\Manipulators\Brightness;
use League\Glide\Manipulators\Contrast;
use League\Glide\Manipulators\Filter;
use League\Glide\Manipulators\Gamma;
use League\Glide\Manipulators\Orientation;
use League\Glide\Manipulators\Output;
use League\Glide\Manipulators\Pixelate;
use League\Glide\Manipulators\Rectangle;
use League\Glide\Manipulators\Sharpen;
use League\Glide\Manipulators\Size;
use League\Glide\Server as GlideServer;

class Server
{
    /**
     * Configuration parameters.
     * @var array
     */
    protected $config;

    /**
     * Create server factory instance.
     * @param array $config Configuration parameters.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Create server instance.
     * @return GlideServer The configured Glide server.
     */
    public function getServer()
    {
        return new GlideServer(
            $this->getSource(),
            $this->getCache(),
            $this->getApi(),
            $this->getBaseUrl()
        );
    }

    /**
     * Get the source file system.
     * @return FilesystemInterface The source file system.
     */
    public function getSource()
    {
        $source = null;

        if (isset($this->config['source'])) {
            $source = $this->config['source'];
        }

        if (is_string($source)) {
            return new Filesystem(new Local($source));
        }

        if ($source instanceof FilesystemInterface) {
            return $source;
        }

        throw new InvalidArgumentException('Invalid `source` parameter.');
    }

    /**
     * Get the cache file system.
     * @return FilesystemInterface The cache file system.
     */
    public function getCache()
    {
        $cache = null;

        if (isset($this->config['cache'])) {
            $cache = $this->config['cache'];
        }

        if (is_string($cache)) {
            return new Filesystem(new Local($cache));
        }

        if ($cache instanceof FilesystemInterface) {
            return $cache;
        }

        throw new InvalidArgumentException('Invalid `cache` parameter.');
    }

    /**
     * Get the image manipulation API.
     * @return Api The image manipulation API.
     */
    public function getApi()
    {
        return new Api(
            $this->getImageManager(),
            $this->getManipulators()
        );
    }

    /**
     * Get the image manager.
     * @return ImageManager Intervention image manager.
     */
    public function getImageManager()
    {
        $driver = 'gd';

        if (isset($this->config['driver'])) {
            $driver = $this->config['driver'];
        }

        return new ImageManager([
            'driver' => $driver,
        ]);
    }

    /**
     * Get the default manipulators.
     * @return array Collection of manipulators.
     */
    public function getManipulators()
    {
        $maxImageSize = null;

        if (isset($this->config['max_image_size'])) {
            $maxImageSize = $this->config['max_image_size'];
        }

        return [
            new Orientation(),
            new Rectangle(),
            new Size($maxImageSize),
            new Brightness(),
            new Contrast(),
            new Gamma(),
            new Sharpen(),
            new Filter(),
            new Blur(),
            new Pixelate(),
            new Output(),
        ];
    }

    /**
     * Get the base URL.
     * @return string The base URL.
     */
    public function getBaseUrl()
    {
        $baseUrl = '';

        if (isset($this->config['base_url'])) {
            $baseUrl = $this->config['base_url'];
        }

        return $baseUrl;
    }

    /**
     * Create server instance.
     * @param  array       $config Configuration parameters.
     * @return GlideServer The configured server.
     */
    public static function create(array $config = [])
    {
        return (new self($config))->getServer();
    }
}
