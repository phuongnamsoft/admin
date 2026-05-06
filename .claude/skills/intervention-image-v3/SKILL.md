---
name: intervention-image-v3
description: >-
  Use when manipulating images in PHP with Intervention Image v3: ImageManager,
  GD/Imagick/Vips drivers, read/decode/encode/save, resizing, watermarks,
  animations, format support, colors, or DecoderException.
metadata:
  category: reference
  triggers:
    - intervention/image
    - Intervention Image
    - ImageManager
    - PHP image processing
    - GD driver
    - Imagick
    - libvips
    - Vips driver
    - DecoderException
    - composer intervention/image
    - image encode WebP AVIF
---

# Intervention Image v3 (PHP)

Fluent API over **GD**, **Imagick**, or **libvips** (via add-on driver). **Authoritative detail** lives in local docs under `references/docs/` (mirrored from the v3 documentation set).

## When to use

- Implementing **resize/scale**, **place/watermark**, **draw/text**, **effects**, or **animations** in PHP
- Choosing or configuring a **driver** and `ImageManager` options (`autoOrientation`, `decodeAnimation`, `blendingColor`, `strip`)
- **Reading** paths, binary, Base64, data URIs, uploads (`SplFileInfo`), or **creating** canvases / **animate()** sequences
- **Encoding** (`toJpeg`, `toWebp`, `encode`, `encodeByMediaType`, `save`) and handling **`EncodedImage`** (string cast, `save`, `toDataUri`)
- **Format or color** questions (driver-dependent support, `driver()->supports(...)`)

## Quick reference

| Topic | Local doc |
| --- | --- |
| Intro & feature summary | `references/docs/getting-started/introduction.md` |
| PHP ≥8.1, extensions, Composer | `references/docs/getting-started/installation.md` |
| Format matrix (GD vs Imagick vs Vips) | `references/docs/getting-started/formats.md` |
| Upgrade from v2 | `references/docs/getting-started/upgrade.md` |
| Framework notes | `references/docs/getting-started/frameworks.md` |
| `ImageManager`, drivers, options | `references/docs/basics/configuration-drivers.md` |
| `read()`, `create()`, `animate()`, decoders | `references/docs/basics/instantiation.md` |
| Encoders, `to*()`, `save()`, `EncodedImage` | `references/docs/basics/image-output.md` |
| Colors, color spaces | `references/docs/basics/colors.md` |
| EXIF, resolution, ICC | `references/docs/basics/meta-information.md` |
| Resizing, effects, drawing, text, animations, modifiers | `references/docs/modifying-images/` |

### Minimal flow (from docs)

```php
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

$manager = new ImageManager(new Driver());
$image = $manager->read('images/example.jpg');
$image->scale(width: 300)->place('images/watermark.png');
$image->toPng()->save('images/foo.png');
```

Static shortcuts: `ImageManager::gd()`, `ImageManager::imagick()`, `ImageManager::withDriver(Driver::class, ...)`.

## Agent workflow

1. **Confirm v3** (`intervention/image:^3` in Composer) and **PHP ≥ 8.1** per `getting-started/installation.md`.
2. **Pick driver** from `configuration-drivers.md`; check **format support** in `formats.md` and optionally `driver()->supports(...)`.
3. For **input errors**, see **DecoderException** and optional explicit decoders in `instantiation.md`.
4. For **output**, use `image-output.md` (shortcut `to*()` methods vs `encode()` / `encodeByMediaType` / `encodeByPath`).
5. For **editing operations**, open the relevant file under `references/docs/modifying-images/`.

## Common mistakes

| Issue | Notes |
| --- | --- |
| **GD and EXIF** | GD driver **drops Exif on encode**; prefer Imagick/Vips when metadata matters (`configuration-drivers.md`). |
| **Wrong format on a driver** | Animated WebP, TIFF, JPEG 2000, HEIC, etc. are **not universally available**—see `formats.md` and runtime `supports()`. |
| **Decode failures** | Thrown as `Intervention\Image\Exceptions\DecoderException`; constrain decoders with `read($input, [...])` when needed (`instantiation.md`). |
| **`encodeByPath` vs `save`** | `encodeByPath` only **infers format from the path string**; it does not write that path (`image-output.md`). |

## Limitations

- This skill **does not** replace reading the full topic file: API parameters, edge cases, and driver-specific caveats are in `references/docs/`.
- **Official online docs** may be newer than the mirrored files; prefer the mirror for offline consistency, then verify against [Intervention Image v3](https://image.intervention.io/v3) if something disagrees.
