Farah Module: Savegame Editor
=============================
[![Packagist Version](https://img.shields.io/packagist/v/slothsoft/savegame)](https://packagist.org/packages/slothsoft/savegame)
[![PHP Version Support](https://img.shields.io/packagist/php-v/slothsoft/savegame)](https://www.php.net/)
[![Documentation](https://img.shields.io/badge/docs-reference-blue.svg)](https://faulo.github.io/slothsoft-savegame/)
[![Test Status](https://github.com/Faulo/slothsoft-savegame/actions/workflows/ci-tests.yml/badge.svg)](https://github.com/Faulo/slothsoft-savegame/actions/workflows/ci-tests.yml)
[![license badge](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

A Farah module for editing arbitrary savegame files.

## Numeric formats

Numeric fields remain big-endian by default. Set `byte-order="little"` on an
`integer`, `signed-integer`, or `select` to read and write little-endian values
of one to four bytes, including three-byte values. The corresponding methods
on `Converter` accept the same optional third argument (`'big'` or `'little'`).

Unsigned `integer` and `select` fields also accept `bit-mask`. Values are masked
without shifting; writes preserve the other bits from the current file content.
Masks must be nonzero and fit the field size. A value containing bits outside
the mask is rejected. This allows a selector and a flag to share a byte:

```xml
<select name="weapon" position="0x518" size="1" bit-mask="0x7f" dictionary-ref="weapons" />
<bit name="equipped" position="0x518" bit="7" />
<integer name="gold" position="0x41c" size="3" byte-order="little" max="999999" />
```

These optional attributes extend editor schema 0.4. Existing structures and
default XML serialization retain their original byte order and attributes.
