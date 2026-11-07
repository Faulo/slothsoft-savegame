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

## Variable resolution

Content nodes that evaluate expressions accept `variable-resolution="global"`
(the default) or `variable-resolution="scoped"`. The setting applies only to
expressions on that node, including its `position`, `size`, `bit-mask`,
`group-count`, `group-size`, `string-count`, and `string-size` where supported.
It is not inherited by child nodes. Files and archives do not accept it.

Global resolution preserves the existing behavior: `$name` selects the first
value with that name in the file. Scoped resolution searches initialized value
descendants of the current node in document order, then repeats the search at
each parent up to and including the file. The first match in the nearest subtree
wins. Named groups and instructions are not values; forward references are not
supported. This is subtree lookup, including sibling subtrees, rather than
strict lexical scope. A missing variable throws an exception.

For example, each string below follows its own record's little-endian pointer:

```xml
<repeat-group position="0" group-count="2" group-size="2">
    <integer name="pointer" size="2" byte-order="little" />
    <string name="text" position="$pointer" position-from="root"
            variable-resolution="scoped" type="null-delimited" encoding="ASCII" />
</repeat-group>
```

Scoped variable bindings are evaluated afresh for each context, so identical
expressions in repeated groups cannot reuse another group's result. Global
evaluation and its existing cache are unchanged. This optional attribute extends
editor schema 0.4; existing manifests and default output remain unchanged.
