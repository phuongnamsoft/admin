# Intervention Image v3 — `ImageField` compatibility design

## Summary

The package already requires **`intervention/image` ^3.x** (see `composer.json`). The `ImageField` trait uses v3-style **`ImageManager`**, **`read()`**, and **`save()`**, but **thumbnail generation** and some **queued fluent calls** still follow **Intervention Image v2** patterns (notably `resize($w, $h, Closure)` and legacy **`resizeCanvas`** argument shapes). This design specifies an **execution-time v2→v3 compatibility layer** with **strong** coverage of common patterns, and locks **padded “fit in box”** behavior to **`contain()`** (may upscale; preserves aspect ratio; fills with background).

## Goals

- Execute **only** against Intervention Image **v3** APIs at runtime (no dual dependency on v2).
- Preserve **legacy fluent chains** recorded via `ImageField::__call` for **documented v2 patterns**, translating them immediately before each invocation on the v3 image instance.
- Rewrite **built-in thumbnail** generation so it uses v3 primitives consistent with the translator—default **`contain(width, height, background, position)`** matching prior intent (aspect-preserving fit inside target dimensions with padding).
- Encode written bytes explicitly for **`FilesystemAdapter::put`** using v3 **`EncodedImage`** semantics (e.g. `encode()` / `toJpeg()` / format chosen from extension).
- Add **focused unit tests** for the translator and thumbnail dimensions on fixture images.
- Document **supported** patterns and **explicit non-support** for removed or non-emulatable v2 behavior.

## Non-goals

- Perfect reproduction of **every** v2 program (impossible without reimplementing removed APIs).
- Restoring removed features: **`backup` / `reset`**, **`response` / `psrResponse` / `stream`**, **imagecache**, **URI-based `make()`**, etc.
- Introducing a full **proxy object** that mimics the entire v2 `Image` surface.
- Optional **`intervention/image-laravel`** service wiring unless a later spec requires it; this trait may continue to instantiate **`ImageManager`** with **`Drivers\Gd\Driver`** by default.

## Compatibility strategy

### Execution-time normalization (recommended shape)

Introduce a dedicated **normalizer** used in two places:

1. **`callInterventionMethods($target)`** — for each queued `['method' => …, 'arguments' => …]`, translate to one or more v3 calls, then apply to the image from `$manager->read($target)`.
2. **`uploadAndDeleteOriginalThumbnail`** — stop using v2-style **`resize` + constraint closure + `resizeCanvas`** chains; call **`contain()`** (or the same helper the normalizer uses) so behavior stays aligned with the **contain** decision below.

The normalizer returns either:

- a single **`[$method, $args]`** for `call_user_func_array`, or
- an ordered list of invocations when a v2 pattern maps to multiple v3 steps.

### Method renames

Map v2 names to v3 per the official upgrade guide (aliases), including but not limited to:

| v2 (conceptual) | v3 |
|-----------------|-----|
| `insert` | `place` |
| `orientate` | `orient` |
| `make` | not used on the instance; reading is `$manager->read()` (already done before the queue runs) |

Other renames from the guide are included in implementation as a maintainable alias table.

### `resize($width, $height, Closure $constraint)` (constraint callback)

**Unsupported as arbitrary PHP:** the closure’s body cannot be compiled to v3.

**Supported convention:** when the third argument is a **`Closure`**, treat the call as **“fit within `$width` × `$height` preserving aspect ratio, pad to exact size with background.”** Replace with:

```text
contain($width, $height, $background, $position)
```

**Defaults for this design:**

- **`$background`:** `'#ffffff'` when migrating the current thumbnail code path; for arbitrary user chains, use **`'ffffff'`** / `'#ffffff'` consistently with v3 color rules unless a later queued call sets canvas (document as **default only** if no color is inferable).
- **`$position`:** `'center'`.

If the old code used **`resize` + closure** without a following **`resizeCanvas`**, **`contain`** alone is still the chosen semantic for “fit in box with padding” per product decision.

### `resizeCanvas` (legacy argument lists)

Map legacy v2 **five-argument** (or otherwise outdated) **`resizeCanvas`** calls to v3’s signature:

```text
resizeCanvas(?int $width, ?int $height, mixed $background = 'ffffff', string $position = 'center')
```

Explicitly document how old **`false`** / relative flags map to **`resizeCanvas`** vs **`resizeCanvasRelative`** in v3 so behavior does not silently change.

### Unsupported patterns

When a queued call cannot be translated, throw a **clear exception** that states:

- the **original method name** and argument shape,
- the **reason** (e.g. unsupported constraint closure variant),
- a **suggested v3 replacement** (e.g. use **`cover()`** for crop-to-fill, **`pad()`** if upscaling must be forbidden).

## Built-in thumbnails

Today’s logic aims for **letterboxed thumbnails**: preserve aspect ratio inside **`$size[0]` × `$size[1]`**, then pad to exact dimensions with **white**.

**Decision (approved):** implement this with **`contain($width, $height, $background, $position)`** where:

- **`$width` / `$height`** come from the thumbnail size array,
- **`$background`** is **`#ffffff`** (or equivalent accepted by v3 for `contain`),
- **`$position`** is **`center`**.

The optional **`$size[2]`** “action” string (`resize` vs others) is either:

- **interpreted** via the same normalizer rules if it matches supported v2 names, or
- **narrowed** to a small whitelist mapped to v3 (`resize`, `contain`, `cover`, etc.) with **`contain`** as default when absent.

## Encoding and storage

- Thumbnails and post-processed files written with **`Storage::put`** must pass **raw binary** from **`EncodedImage`** (e.g. `(string) $encoded` or documented API from `image-output`), not rely on ambiguous casting behavior.
- **`encode()`** with no arguments uses **AutoEncoder** semantics per v3; prefer **encoder chosen from target file extension** where correctness matters (JPEG vs PNG vs WebP).

## Driver

- **Default:** **`Intervention\Image\Drivers\Gd\Driver`** (current behavior).
- **Note:** GD may drop or mishandle some EXIF workflows; document that **Imagick** is optional for consumers who configure a custom manager in a future extension—out of scope unless added in implementation.

## Testing

- **Unit tests** for the normalizer: rename aliases, `resize`+`Closure` → **`contain`**, legacy **`resizeCanvas`** mapping, failure cases with readable messages.
- **Integration-style test** (optional if harness allows): small PNG fixture, assert output dimensions after thumbnail pipeline.

## Documentation and release

- **CHANGELOG:** note adoption of v3-only APIs, compatibility layer behavior, **`contain`** default, and any breaking changes for chains that relied on unsupported v2 behavior.
- **README or docs:** short section “ImageField & Intervention v3” listing supported legacy patterns.

## Risks

- **`contain`** vs **`pad`:** upscaling small images may increase file size or introduce softness; product chose **`contain`** deliberately.
- **Closure-based `resize`:** only the **third-argument Closure** pattern is interpreted; other overloads must error or pass through if they are already valid v3.

## Approval record

- **Compatibility depth:** strong (option **3**) — broader translation layer for legacy chains.
- **Fit-in-box primitive:** **`contain()`** for thumbnails and for **`resize($w,$h,Closure)`** migration path.
