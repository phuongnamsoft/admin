<?php

namespace PNS\Admin\Form\Field;

use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait ImageField
{
    /**
     * Intervention calls.
     *
     * @var array
     */
    protected $interventionCalls = [];

    /**
     * Image manager instance.
     *
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * Thumbnail settings.
     *
     * @var array
     */
    protected $thumbnails = [];

    /**
     * Get the image manager instance.
     *
     * @return ImageManager
     */
    protected function getImageManager()
    {
        if (!$this->imageManager) {
            $this->imageManager = new ImageManager(new Driver());
        }
        return $this->imageManager;
    }

    /**
     * Default directory for file to upload.
     *
     * @return mixed
     */
    public function defaultDirectory()
    {
        return config('admin.upload.directory.image');
    }

    /**
     * Execute Intervention calls.
     *
     * @param string $target
     *
     * @return mixed
     */
    public function callInterventionMethods($target)
    {
        if (!empty($this->interventionCalls)) {
            $image = $this->getImageManager()->read($target);
            $normalizer = new InterventionLegacyCallNormalizer();
            $this->applyNormalizedCalls($image, $normalizer);
            $image->save($target);
        }

        return $target;
    }

    /**
     * @param \Intervention\Image\Interfaces\ImageInterface $image
     */
    protected function applyNormalizedCalls($image, InterventionLegacyCallNormalizer $normalizer): void
    {
        foreach ($this->interventionCalls as $call) {
            $steps = $normalizer->normalize($call['method'], $call['arguments']);
            foreach ($steps as [$method, $args]) {
                call_user_func_array([$image, $method], $args);
            }
        }
    }

    /**
     * Call intervention methods.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function __call($method, $arguments)
    {
        if (static::hasMacro($method)) {
            return $this;
        }

        if (!class_exists(ImageManager::class)) {
            throw new \Exception('To use image handling and manipulation, please install [intervention/image] first.');
        }

        $this->interventionCalls[] = [
            'method'    => $method,
            'arguments' => $arguments,
        ];

        return $this;
    }

    /**
     * Render a image form field.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $this->options(['allowedFileTypes' => ['image'], 'msgPlaceholder' => trans('admin.choose_image')]);

        return parent::render();
    }

    /**
     * @param string|array $name
     * @param int          $width
     * @param int          $height
     *
     * @return $this
     */
    public function thumbnail($name, ?int $width = null, ?int $height = null)
    {
        if (func_num_args() == 1 && is_array($name)) {
            foreach ($name as $key => $size) {
                if (count($size) >= 2) {
                    $this->thumbnails[$key] = $size;
                }
            }
        } elseif (func_num_args() == 3) {
            $this->thumbnails[$name] = [$width, $height];
        }

        return $this;
    }

    /**
     * Destroy original thumbnail files.
     *
     * @return void.
     */
    public function destroyThumbnail()
    {
        if ($this->retainable) {
            return;
        }

        foreach ($this->thumbnails as $name => $_) {
            if (is_array($this->original)) {
                if (empty($this->original)) {
                    continue;
                }

                foreach ($this->original as $original) {
                    $this->destroyThumbnailFile($original, $name);
                }
            } else {
                $this->destroyThumbnailFile($this->original, $name);
            }
        }
    }

    /**
     * Remove thumbnail file from disk.
     *
     * @return void.
     */
    public function destroyThumbnailFile($original, $name)
    {
        $ext = @pathinfo($original, PATHINFO_EXTENSION);

        // We remove extension from file name so we can append thumbnail type
        $path = @Str::replaceLast('.'.$ext, '', $original);

        // We merge original name + thumbnail name + extension
        $path = $path.'-'.$name.'.'.$ext;

        if ($this->storage->exists($path)) {
            $this->storage->delete($path);
        }
    }

    /**
     * Upload file and delete original thumbnail files.
     *
     * @param UploadedFile $file
     *
     * @return $this
     */
    protected function uploadAndDeleteOriginalThumbnail(UploadedFile $file)
    {
        $normalizer = new InterventionLegacyCallNormalizer();

        foreach ($this->thumbnails as $name => $size) {
            $ext = pathinfo($this->name, PATHINFO_EXTENSION);
            $path = Str::replaceLast('.'.$ext, '', $this->name);
            $path = $path.'-'.$name.'.'.$ext;

            $image = $this->getImageManager()->read($file);

            $action = $size[2] ?? 'resize';
            $targetWidth = (int) $size[0];
            $targetHeight = (int) $size[1];

            $method = $this->resolveThumbnailAction($action);
            $arguments = $this->thumbnailArgumentsFor($method, $targetWidth, $targetHeight);

            $steps = $normalizer->normalize($method, $arguments);
            foreach ($steps as [$m, $args]) {
                call_user_func_array([$image, $m], $args);
            }

            $relativePath = "{$this->getDirectory()}/{$path}";
            $encoded = $image->encodeByPath($path);
            $binary = (string) $encoded;

            if (!is_null($this->storagePermission)) {
                $this->storage->put($relativePath, $binary, $this->storagePermission);
            } else {
                $this->storage->put($relativePath, $binary);
            }
        }

        $this->destroyThumbnail();

        return $this;
    }

    private function resolveThumbnailAction(string $action): string
    {
        return match ($action) {
            'resize', 'contain' => 'contain',
            'pad' => 'pad',
            'cover' => 'cover',
            'coverDown' => 'coverDown',
            default => throw new UnsupportedLegacyInterventionCallException(
                $action,
                'Unsupported thumbnail action for ImageField::thumbnail() size tuple.',
                'Use resize (letterboxed contain), contain, pad, cover, or coverDown.'
            ),
        };
    }

    /**
     * @return array<int, mixed>
     */
    private function thumbnailArgumentsFor(string $method, int $targetWidth, int $targetHeight): array
    {
        $background = 'ffffff';
        $position = 'center';

        return match ($method) {
            'contain' => [$targetWidth, $targetHeight, $background, $position],
            'pad' => [$targetWidth, $targetHeight, $background, $position],
            'cover' => [$targetWidth, $targetHeight, $position],
            'coverDown' => [$targetWidth, $targetHeight, $position],
            default => [$targetWidth, $targetHeight],
        };
    }
}
