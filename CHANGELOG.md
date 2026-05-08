## Unreleased

### Changed

- **ImageField** (Intervention Image): Built-in thumbnails now use v3 **`contain()`** for letterboxing (aspect preserved, optional upscaling, padded background). Legacy fluent chains queued via `__call` are translated through **`InterventionLegacyCallNormalizer`** (e.g. `insert` → `place`, `orientate` → `orient`, `resize($w,$h,Closure)` → `contain`, legacy five-arg **`resizeCanvas`** → v3 canvas methods). Thumbnail bytes written through Laravel storage use explicit **`EncodedImage`** binary from **`encodeByPath()`**.

### Removed / unsupported

- Queued calls that map to removed v2 APIs (**`widen`**, **`heighten`**, **`fit`**, **`backup`**, **`reset`**, **`response`**, etc.) throw **`UnsupportedLegacyInterventionCallException`** with guidance toward v3 replacements.
