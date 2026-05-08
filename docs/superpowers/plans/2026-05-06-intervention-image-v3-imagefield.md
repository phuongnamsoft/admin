# Intervention Image v3 — `ImageField` compatibility implementation plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Run `ImageField` only against Intervention Image **v3** APIs by centralizing a **legacy-call normalizer**, switching built-in thumbnails to **`contain()`**, and passing **explicit binary** from **`EncodedImage`** into `Storage::put`, with unit tests and short docs.

**Architecture:** Add a small **normalizer** class that turns each queued `['method' => …, 'arguments' => …]` record into one or more `[v3 method, arguments]` steps (method renames, `resize`+`Closure` → `contain`, legacy **`resizeCanvas`** shapes → v3 **`resizeCanvas` / `resizeCanvasRelative`**). `ImageField::callInterventionMethods` applies those steps then **`save($target)`** (v3 supports path-based save). `uploadAndDeleteOriginalThumbnail` stops using v2-style **`resize` + constraint + `resizeCanvas`**; it applies **`contain`** (or a whitelisted action mapping) and writes bytes via **`encodeByPath()`** cast to string. Unsupported queued patterns throw a **dedicated exception** with method name, reason, and a suggested v3 API.

**Tech stack:** PHP 8.2+, `intervention/image` ^3.11 (see `composer.json`), Laravel `FilesystemAdapter`, PHPUnit 10.

---

## File map

| Responsibility | File |
|-----------------|------|
| Clear errors for untranslatable legacy chains | Create: `src/Form/Field/UnsupportedLegacyInterventionCallException.php` |
| v2-shaped → v3 invocation list | Create: `src/Form/Field/InterventionLegacyCallNormalizer.php` |
| Queue consumption + thumbnails + encoding | Modify: `src/Form/Field/ImageField.php` (full trait; key regions ~63–79, ~196–223) |
| Unit tests (normalizer + thumbnail dimensions) | Create: `tests/InterventionLegacyCallNormalizerTest.php`, Create: `tests/ImageFieldThumbnailEncodingTest.php` |
| Optional tiny PNG fixture (if not reusing `tests/assets/test.jpg`) | Create: `tests/assets/thumbnail-source.png` (or document reuse of existing asset) |
| PSR-4 for new test classes (if using namespaces) | Modify: `composer.json` — add `"Tests\\": "tests/"` under `autoload-dev.psr-4` **or** keep tests **without** namespace (match existing top-level tests) |
| Release notes + user-facing behavior | Modify: `CHANGELOG.md`, Modify: `README.md` (new subsection under Configuration or end of doc) |

**Authoritative v3 signatures used in this plan** (verify against installed minor version if needed):

- `contain(int $width, int $height, $background = 'ffffff', string $position = 'center')`
- `pad(int $width, int $height, $background = 'ffffff', string $position = 'center')`
- `cover(int $width, int $height, string $position = 'center')`
- `resizeCanvas(?int $width, ?int $height, mixed $background = 'ffffff', string $position = 'center')`
- `resizeCanvasRelative(?int $width, ?int $height, mixed $background = 'ffffff', string $position = 'center')`
- `encodeByPath(?string $path = null, mixed ...$options): EncodedImage` then `(string) $encoded` via `EncodedImage::__toString()`
- `Image::save(?string $path = null, mixed ...$options)` for in-place file rewrite after queued modifiers

References: [Upgrade guide](https://image.intervention.io/v3/getting-started/upgrade), [Resizing](https://image.intervention.io/v3/modifying-images/resizing), [Image output](https://image.intervention.io/v3/basics/image-output).

---

### Task 1: Exception type

**Files:**

- Create: `src/Form/Field/UnsupportedLegacyInterventionCallException.php`
- Test: `tests/InterventionLegacyCallNormalizerTest.php` (assert message shape in later task; optional minimal test here)

- [ ] **Step 1: Write the failing test**

Create `tests/InterventionLegacyCallNormalizerTest.php`:

```php
<?php

use PHPUnit\Framework\TestCase;
use PNS\Admin\Form\Field\UnsupportedLegacyInterventionCallException;

class InterventionLegacyCallNormalizerTest extends TestCase
{
    public function test_exception_extends_invalid_argument_exception(): void
    {
        $e = new UnsupportedLegacyInterventionCallException(
            'widen',
            'Intervention Image v3 removed widen(); use scale() / scaleDown() with named arguments.',
            'Use $image->scale(width: 200) or scaleDown(width: 200) depending on whether upscaling is allowed.'
        );

        $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        $this->assertStringContainsString('widen', $e->getMessage());
        $this->assertStringContainsString('scale', $e->getMessage());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run:

```bash
cd "d:/templates/projects/packages/admin"
composer dump-autoload
./vendor/bin/phpunit tests/InterventionLegacyCallNormalizerTest.php --filter test_exception_extends_invalid_argument_exception -v
```

Expected: **FAIL** — class `UnsupportedLegacyInterventionCallException` not found.

- [ ] **Step 3: Write minimal implementation**

Create `src/Form/Field/UnsupportedLegacyInterventionCallException.php`:

```php
<?php

namespace PNS\Admin\Form\Field;

class UnsupportedLegacyInterventionCallException extends \InvalidArgumentException
{
    public function __construct(
        public readonly string $originalMethod,
        string $reason,
        public readonly string $suggestedReplacement
    ) {
        parent::__construct(sprintf(
            'Unsupported legacy Intervention call [%s]: %s Suggested v3 approach: %s',
            $originalMethod,
            $reason,
            $suggestedReplacement
        ));
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run:

```bash
./vendor/bin/phpunit tests/InterventionLegacyCallNormalizerTest.php --filter test_exception_extends_invalid_argument_exception -v
```

Expected: **PASS**

---

### Task 2: Normalizer — method aliases and removed methods

**Files:**

- Modify: `src/Form/Field/InterventionLegacyCallNormalizer.php` (create in Step 3)
- Modify: `tests/InterventionLegacyCallNormalizerTest.php`

- [ ] **Step 1: Write failing tests for aliases and removed methods**

Append to `tests/InterventionLegacyCallNormalizerTest.php`:

```php
use PNS\Admin\Form\Field\InterventionLegacyCallNormalizer;

public function test_insert_alias_to_place(): void
{
    $n = new InterventionLegacyCallNormalizer();
    $steps = $n->normalize('insert', ['/tmp/watermark.png', [10, 20]]);
    $this->assertSame([['place', ['/tmp/watermark.png', [10, 20]]]], $steps);
}

public function test_orientate_alias_to_orient(): void
{
    $n = new InterventionLegacyCallNormalizer();
    $steps = $n->normalize('orientate', []);
    $this->assertSame([['orient', []]], $steps);
}

public function test_widen_throws_with_suggestion(): void
{
    $this->expectException(UnsupportedLegacyInterventionCallException::class);
    $this->expectExceptionMessage('widen');

    (new InterventionLegacyCallNormalizer())->normalize('widen', [100]);
}
```

- [ ] **Step 2: Run tests — expect failures**

```bash
./vendor/bin/phpunit tests/InterventionLegacyCallNormalizerTest.php -v
```

Expected: **FAIL** — class not found or methods missing.

- [ ] **Step 3: Implement normalizer skeleton**

Create `src/Form/Field/InterventionLegacyCallNormalizer.php`:

```php
<?php

namespace PNS\Admin\Form\Field;

final class InterventionLegacyCallNormalizer
{
    private const DEFAULT_PAD_BACKGROUND = 'ffffff';

    private const DEFAULT_POSITION = 'center';

    /** @var array<string, string> v2-style name => v3 method name */
    private const METHOD_ALIASES = [
        'insert' => 'place',
        'orientate' => 'orient',
    ];

    /** @var array<string, string> method => guidance */
    private const REMOVED_METHODS = [
        'widen' => 'Use scale(width: …) or scaleDown(width: …) instead of widen().',
        'heighten' => 'Use scale(height: …) or scaleDown(height: …) instead of heighten().',
        'fit' => 'Use cover() for crop-to-fill, pad() for letterbox without upscaling, or contain() for letterbox with upscaling.',
        'backup' => 'Clone the image object in PHP (native cloning) instead of backup()/reset().',
        'reset' => 'Clone the image object in PHP (native cloning) instead of backup()/reset().',
        'cache' => 'Image caching is not part of intervention/image v3; handle caching in application code.',
        'response' => 'Build HTTP responses from EncodedImage bytes in your framework; psrResponse()/response() were removed.',
        'psrResponse' => 'Build HTTP responses from EncodedImage bytes in your framework; psrResponse()/response() were removed.',
        'stream' => 'Use EncodedImage::toFilePointer() or raw string bytes instead of stream().',
        'destroy' => 'Unset the variable or let scope end; destroy() was removed.',
        'make' => 'Reading uses ImageManager::read(); make() is not queued on the instance.',
    ];

    /**
     * @return list<array{0: string, 1: array<int, mixed>}>
     */
    public function normalize(string $method, array $arguments): array
    {
        $original = $method;
        $method = self::METHOD_ALIASES[$method] ?? $method;

        if (isset(self::REMOVED_METHODS[$original])) {
            throw new UnsupportedLegacyInterventionCallException(
                $original,
                'This API was removed or is not representable as a v3 modifier call.',
                self::REMOVED_METHODS[$original]
            );
        }

        return match (true) {
            $method === 'resize' && array_key_exists(2, $arguments) && $arguments[2] instanceof \Closure
                => $this->normalizeResizeWithConstraint($arguments),
            $method === 'resizeCanvas' => $this->normalizeResizeCanvas($arguments),
            default => [[$method, $arguments]],
        };
    }

    /**
     * @return list<array{0: string, 1: array<int, mixed>}>
     */
    private function normalizeResizeWithConstraint(array $arguments): array
    {
        $width = $arguments[0] ?? null;
        $height = $arguments[1] ?? null;

        if (! is_int($width) || ! is_int($height) || $width <= 0 || $height <= 0) {
            throw new UnsupportedLegacyInterventionCallException(
                'resize',
                'resize() with a constraint closure is only supported when width and height are positive integers (letterbox semantics).',
                'Use contain(width, height, background, position) with explicit integers, or rewrite using scale()/cover()/pad().'
            );
        }

        return [[
            'contain',
            [$width, $height, self::DEFAULT_PAD_BACKGROUND, self::DEFAULT_POSITION],
        ]];
    }

    /**
     * Map Intervention v2 resizeCanvas ($width, $height, $anchor, $relative, $bgcolor) to v3.
     *
     * @return list<array{0: string, 1: array<int, mixed>}>
     */
    private function normalizeResizeCanvas(array $arguments): array
    {
        $count = count($arguments);

        if ($count === 2) {
            [$w, $h] = $this->nullableDimensionsPair($arguments[0], $arguments[1]);

            return [['resizeCanvas', [$w, $h]]];
        }

        if ($count === 5) {
            [$width, $height, $position, $relative, $background] = $arguments;
            $background = $this->normalizeBackground($background);
            $pair = $this->nullableDimensionsPair($width, $height);

            if ($relative === true) {
                return [['resizeCanvasRelative', array_merge($pair, [$background, (string) $position])]];
            }

            if ($relative === false) {
                return [['resizeCanvas', array_merge($pair, [$background, (string) $position])]];
            }

            throw new UnsupportedLegacyInterventionCallException(
                'resizeCanvas',
                'Legacy resizeCanvas(..., $relative, ...) requires $relative to be boolean to choose resizeCanvas vs resizeCanvasRelative.',
                'Pass true for relative canvas delta sizing (v3 resizeCanvasRelative) or false for absolute canvas size (v3 resizeCanvas).'
            );
        }

        if ($count === 3 || $count === 4) {
            throw new UnsupportedLegacyInterventionCallException(
                'resizeCanvas',
                sprintf('Unsupported argument count (%d) for legacy resizeCanvas mapping.', $count),
                'Use v3 resizeCanvas(?width, ?height, background, position) or resizeCanvasRelative with explicit booleans for relative mode in five-arg legacy form.'
            );
        }

        if ($count === 0 || $count === 1) {
            throw new UnsupportedLegacyInterventionCallException(
                'resizeCanvas',
                sprintf('Unsupported argument count (%d) for resizeCanvas.', $count),
                'Use v3 resizeCanvas(?width, ?height, background, position).'
            );
        }

        return [['resizeCanvas', $arguments]];
    }

    /**
     * @return array{0: null|int, 1: null|int}
     */
    private function nullableDimensionsPair(mixed $width, mixed $height): array
    {
        $w = $this->nullableDimension($width);
        $h = $this->nullableDimension($height);

        return [$w, $h];
    }

    private function nullableDimension(mixed $value): ?int
    {
        if ($value === null || $value === false) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        throw new UnsupportedLegacyInterventionCallException(
            'resizeCanvas',
            'Canvas dimensions must be int, null, or false (treated as null) for v3 mapping.',
            'Use null for “leave axis unchanged” per Intervention v3 resizeCanvas().'
        );
    }

    private function normalizeBackground(mixed $background): string
    {
        if ($background === null) {
            return self::DEFAULT_PAD_BACKGROUND;
        }

        if (is_string($background)) {
            $trim = ltrim($background, '#');

            return $trim !== '' ? $trim : self::DEFAULT_PAD_BACKGROUND;
        }

        throw new UnsupportedLegacyInterventionCallException(
            'resizeCanvas',
            'Intervention Image v3 does not accept legacy array color values for canvas background.',
            'Pass a hex string such as "ffffff" or a named color accepted by Intervention v3.'
        );
    }
}
```

- [ ] **Step 4: Run tests**

```bash
./vendor/bin/phpunit tests/InterventionLegacyCallNormalizerTest.php -v
```

Expected: **PASS**

---

### Task 3: Normalizer tests — `resize` edge cases

**Files:**

- Modify: `tests/InterventionLegacyCallNormalizerTest.php`
- Modify: `src/Form/Field/InterventionLegacyCallNormalizer.php` (only if tests expose a bug)

- [ ] **Step 1: Write failing tests**

Append:

```php
public function test_resize_with_closure_becomes_contain(): void
{
    $n = new InterventionLegacyCallNormalizer();
    $steps = $n->normalize('resize', [100, 80, static fn () => null]);
    $this->assertSame([['contain', [100, 80, 'ffffff', 'center']]], $steps);
}

public function test_resize_two_integers_passes_through(): void
{
    $n = new InterventionLegacyCallNormalizer();
    $steps = $n->normalize('resize', [100, 80]);
    $this->assertSame([['resize', [100, 80]]], $steps);
}

public function test_resize_invalid_dimensions_with_closure_throw(): void
{
    $this->expectException(UnsupportedLegacyInterventionCallException::class);
    (new InterventionLegacyCallNormalizer())->normalize('resize', [0, 100, static fn () => null]);
}
```

- [ ] **Step 2: Run tests**

```bash
./vendor/bin/phpunit tests/InterventionLegacyCallNormalizerTest.php -v
```

Expected: **PASS** (pass-through must remain valid for v3 native `resize` where arity matches).

---

### Task 4: Normalizer tests — legacy five-arg `resizeCanvas`

**Files:**

- Modify: `tests/InterventionLegacyCallNormalizerTest.php`

- [ ] **Step 1: Write tests**

Append:

```php
public function test_resize_canvas_five_arg_absolute_maps_to_v3_resize_canvas(): void
{
    $n = new InterventionLegacyCallNormalizer();
    $steps = $n->normalize('resizeCanvas', [800, 600, 'center', false, '#ff0000']);
    $this->assertSame([['resizeCanvas', [800, 600, 'ff0000', 'center']]], $steps);
}

public function test_resize_canvas_five_arg_relative_maps_to_relative(): void
{
    $n = new InterventionLegacyCallNormalizer();
    $steps = $n->normalize('resizeCanvas', [40, 0, 'bottom', true, null]);
    $this->assertSame([['resizeCanvasRelative', [40, 0, 'ffffff', 'bottom']]], $steps);
}

public function test_resize_canvas_two_arg_passes_dimensions_only(): void
{
    $n = new InterventionLegacyCallNormalizer();
    $steps = $n->normalize('resizeCanvas', [320, 240]);
    $this->assertSame([['resizeCanvas', [320, 240]]], $steps);
}
```

- [ ] **Step 2: Run tests**

```bash
./vendor/bin/phpunit tests/InterventionLegacyCallNormalizerTest.php -v
```

Expected: **PASS**

---

### Task 5: `ImageField::callInterventionMethods` uses normalizer

**Files:**

- Modify: `src/Form/Field/ImageField.php`
- Test: extend `tests/InterventionLegacyCallNormalizerTest.php` **or** add `tests/ImageFieldCallInterventionMethodsTest.php` that uses a stub object using the trait (heavier); **recommended:** quick integration-style test in `tests/ImageFieldThumbnailEncodingTest.php` (Task 6) plus manual reasoning for queue loop

Minimal verification for this task: run full suite after edit.

- [ ] **Step 1: Replace the foreach body in `callInterventionMethods`**

In `src/Form/Field/ImageField.php`, add import:

```php
use PNS\Admin\Form\Field\InterventionLegacyCallNormalizer;
```

Add a **protected** helper on the trait (same file):

```php
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
```

Replace `callInterventionMethods` loop:

```php
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
```

**Note:** If your IDE complains about `ImageInterface`, drop the param type and keep a one-line docblock only, to avoid coupling to an internal namespace across patch versions.

- [ ] **Step 2: Run full test suite**

```bash
./vendor/bin/phpunit -v
```

Expected: **PASS** (existing BrowserKit tests; normalizer unit tests).

---

### Task 6: Thumbnails — `contain`, action whitelist, `encodeByPath` bytes

**Files:**

- Modify: `src/Form/Field/ImageField.php`
- Create: `tests/ImageFieldThumbnailEncodingTest.php`

**Thumbnail semantics (approved in spec):** default letterbox uses **`contain($w, $h, '#ffffff', 'center')`**; v3 examples often use `'ffffff'` — both are valid color inputs; use **`'ffffff'`** for consistency with `InterventionLegacyCallNormalizer::DEFAULT_PAD_BACKGROUND` **or** pass `'#ffffff'` if your driver tests show that form is required—**pick one and use it in both places**.

**`$size[2]` action mapping:**

| `$size[2]` value | v3 call |
|------------------|---------|
| absent | `contain` |
| `'resize'` (legacy default in old code) | `contain` (same visual intent as old resize+aspect+canvas) |
| `'contain'` | `contain` |
| `'pad'` | `pad` |
| `'cover'` | `cover` |
| `'coverDown'` | `coverDown` |

Any other string → throw `UnsupportedLegacyInterventionCallException` with the **thumbnail** context in the reason string.

- [ ] **Step 1: Write failing test for thumbnail dimensions + binary string**

Create `tests/ImageFieldThumbnailEncodingTest.php`:

```php
<?php

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use PHPUnit\Framework\TestCase;

class ImageFieldThumbnailEncodingTest extends TestCase
{
    public function test_contain_produces_exact_thumbnail_dimensions(): void
    {
        $fixture = __DIR__.'/assets/test.jpg';
        $this->assertFileExists($fixture);

        $manager = new ImageManager(new Driver());
        $image = $manager->read($fixture);
        $image->contain(120, 80, 'ffffff', 'center');

        $tmp = sys_get_temp_dir().'/admin-thumb-test-'.uniqid().'.jpg';
        $image->save($tmp);

        $out = $manager->read($tmp);
        $this->assertSame(120, $out->width());
        $this->assertSame(80, $out->height());

        @unlink($tmp);
    }

    public function test_encode_by_path_returns_non_empty_string_bytes(): void
    {
        $fixture = __DIR__.'/assets/test.jpg';
        $manager = new ImageManager(new Driver());
        $image = $manager->read($fixture);
        $image->contain(60, 60, 'ffffff', 'center');

        $encoded = $image->encodeByPath('thumb.jpeg');
        $binary = (string) $encoded;

        $this->assertNotSame('', $binary);
        $this->assertGreaterThan(100, strlen($binary));
    }
}
```

- [ ] **Step 2: Run test**

```bash
./vendor/bin/phpunit tests/ImageFieldThumbnailEncodingTest.php -v
```

Expected: **PASS** once Intervention is installed (documents expected dimensions behavior).

- [ ] **Step 3: Implement `uploadAndDeleteOriginalThumbnail` changes**

In `src/Form/Field/ImageField.php`, replace the thumbnail processing block with logic equivalent to:

```php
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
```

**Important:** `normalize()` for `contain` / `pad` / `cover` currently falls through to **pass-through**. That is correct: those methods are native v3.

Remove the old v2-style chain:

```php
$image->$action(...)->resizeCanvas(...);
```

and remove `$image->encode()` without path—replace with **`encodeByPath($path)`** + string cast as above.

- [ ] **Step 4: Run full suite**

```bash
./vendor/bin/phpunit -v
```

Expected: **PASS**

---

### Task 7: Documentation and CHANGELOG

**Files:**

- Modify: `CHANGELOG.md`
- Modify: `README.md`

- [ ] **Step 1: CHANGELOG entry**

Append to `CHANGELOG.md`:

```markdown
## Unreleased

### Changed

- **ImageField** (Intervention Image): Built-in thumbnails now use v3 **`contain()`** for letterboxing (aspect preserved, optional upscaling, padded background). Legacy fluent chains queued via `__call` are translated through **`InterventionLegacyCallNormalizer`** (e.g. `insert` → `place`, `orientate` → `orient`, `resize($w,$h,Closure)` → `contain`, legacy five-arg **`resizeCanvas`** → v3 canvas methods). Thumbnail bytes written through Laravel storage use explicit **`EncodedImage`** binary from **`encodeByPath()`**.
### Removed / unsupported

- Queued calls that map to removed v2 APIs (**`widen`**, **`heighten`**, **`fit`**, **`backup`**, **`reset`**, **`response`**, etc.) throw **`UnsupportedLegacyInterventionCallException`** with guidance toward v3 replacements.
```

- [ ] **Step 2: README section**

Add subsection **“ImageField & Intervention Image v3”** (placement: after **Configurations** or **Requirements**) documenting:

- Package expects **`intervention/image` ^3**; thumbnails default to **`contain`** with white padding; **`thumbnail()` third dimension** supports `resize` (legacy alias for contain), `contain`, `pad`, `cover`, `coverDown`.
- Fluent chains: supported aliases and **`resize`+closure** convention; **not** full v2 emulation (no arbitrary constraint closure logic).
- GD default driver; EXIF/metadata limitations note per [configuration docs](https://image.intervention.io/v3/basics/configuration-drivers).

---

## Self-review (spec coverage)

| Spec requirement | Task |
|------------------|------|
| v3-only runtime | Tasks 2–6 |
| Normalizer in `callInterventionMethods` + thumbnails | Tasks 2, 5, 6 |
| `resize`+`Closure` → `contain` | Task 2 |
| Legacy `resizeCanvas` mapping + relative flag | Task 2, 4 |
| Clear exceptions + suggestions | Tasks 1–2 |
| Thumbnails use `contain`, `#ffffff`/`ffffff`, center | Task 6 |
| `$size[2]` whitelist / normalizer alignment | Task 6 |
| `Storage::put` + `EncodedImage` bytes | Task 6 |
| GD default | unchanged in `getImageManager()` |
| Unit tests normalizer + thumbnail pipeline | Tasks 1–4, 6 |
| CHANGELOG + README | Task 7 |

**Gaps:** None identified for in-scope items. **Out of scope** per spec: perfect v2 parity, proxy object, `image-laravel` wiring, Imagick driver switch.

---

**Plan complete and saved to `docs/superpowers/plans/2026-05-06-intervention-image-v3-imagefield.md`. Two execution options:**

**1. Subagent-Driven (recommended)** — Dispatch a fresh subagent per task, review between tasks, fast iteration.

**2. Inline Execution** — Execute tasks in this session using executing-plans, batch execution with checkpoints.

**Which approach?**
