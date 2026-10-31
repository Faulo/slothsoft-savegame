# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.3.13] - 2026-10-31

### Added

- Behavior tests for XML serialization, namespace handling, escaping, attribute filtering, and caching, using concrete test nodes.
- A DDEV development environment and cross-platform CI coverage including Linux ARM64 and Windows.
- An XML catalog for resolving the module's schemas in development tools.

### Changed

- Require `php-ds/php-ds` ^1.8.0, `slothsoft/core` ^1.15.0, and `slothsoft/farah` ^1.29.7.

### Deprecated

- `XmlBuilder::registerTagBlacklist()`, `clearTagBlacklist()`, `registerTagCachelist()`, and `clearTagCachelist()` are documented no-ops. The blacklist methods on `BuilderInterface` are deprecated too. Attribute filtering and root caching retain their existing behavior.

### Removed

- The unused, empty `ArchiveRepository` class.

### Fixed

- Image file name and string exports now use the containing file's name and content.
- Unknown value and archive lookups throw exceptions instead of falling through without a result.
- PHPStan findings in node initialization, sibling positioning, string dictionaries, and redundant conditions, while preserving expression evaluation.

## [1.3.12] - 2026-04-02

### Fixed

- Restore PHP 8.2 compatibility by lowering the minimum PHP version from 8.3 to 8.2 and requiring Farah ^1.28.2.

## [1.3.11] - 2026-04-02

### Changed

- Raise the minimum PHP version from 7.4 to 8.3. PHP 7.4 through 8.2 no longer satisfy the Composer requirement in this release.
- Require `php-ds/php-ds` ^1.7.0 and Farah ^1.28.1.
- Declare `Ds\Set` as the return type of the internal file-name filtering method.

## [1.3.10] - 2026-03-18

### Changed

- Store extracted archive files in `Ds\Map` and return file names as `Ds\Set` from `ArchiveNode::getFileNames()`.
- Apply file-list and range filters using set operations.

## [1.3.9] - 2026-03-18

### Added

- Inclusive `range-start` and `range-end` filters on `<for-each-file>`, using lexical file-name comparisons.
- PHP 8.5 coverage in CI.

### Changed

- Rename the `<for-each-file>` attribute `file-range` to `list`. Existing structure documents must use the new name.
- Require Core ^1.14.19 and Farah ^1.27.1.

## [1.3.8] - 2026-02-03

### Added

- `SavegameNode::setSaveId()` for changing the exported save identifier.

## [1.3.7] - 2026-01-27

### Fixed

- Accept a boolean value in `FileContainer::insertContentBit()` so bit updates work with strict typing.

## [1.3.6] - 2026-01-27

### Added

- Dirty-state propagation from changed file content through archive and savegame build hashes.

### Changed

- `AbstractValueContent::setValue()` preserves the existing value's type, updates content only when the value changes, and returns whether a change occurred.

### Removed

- `SavegameNode::getChunkWriter()`. Construct an `XmlBuilder` with the node and configure its cache directory instead.

### Fixed

- Compute cache hashes from current in-memory content after edits, preventing reuse of XML for an earlier value state.

## [1.3.5] - 2026-01-26

### Added

- `ArchiveNode::getArchivePath()` and `getName()` to distinguish the archive's lookup path from its display name.

### Changed

- Look up archives by path throughout `Editor` and `SavegameNode`.
- `Editor::writeGameFile()` accepts `StringWriterInterface`; archive and image nodes explicitly implement that interface.

### Removed

- `ArchiveNode::getArchiveId()` and `SavegameNode::getArchiveById()`. Use `getArchivePath()` and `getArchiveByPath()` respectively.

## [1.3.4] - 2026-01-24

### Fixed

- Validate numeric values and cast them to integers before encoding integer, signed-integer, select, and bit values. Invalid input raises `InvalidArgumentException`.

## [1.3.3] - 2026-01-19

### Fixed

- Correct the schema's number-expression pattern to accept comparison and logical operators, including `<`, `>`, `!`, `&`, and `|`.

## [1.3.2] - 2026-01-18

### Added

- A `type` attribute on `<string>` with runtime support for `size-fixed` and `null-delimited` strings. Fixed-size strings remain the default.

### Changed

- Share the schema's `stringSizing` type between strings and string dictionaries.

### Fixed

- Reject unsupported string and string-dictionary sizing types with a `DomainException`.

## [1.3.1] - 2026-01-18

### Changed

- Broaden the schema's number-expression syntax to allow more complex position expressions, including conditional expressions.

## [1.3.0] - 2026-01-18

### Added

- A `position-from` attribute for positioning content relative to its parent, the previous value's end, or the file root. The default remains `parent`.

## [1.2.1] - 2026-01-18

### Fixed

- Accept negative hexadecimal positions in both expression evaluation and the schema.

## [1.2.0] - 2026-01-18

### Added

- A `position-at-string` attribute that locates content by searching for a string at or after the calculated offset. A missing match raises `RangeException`.
- `FileContainer::findStringAtOrAfter()` for finding byte offsets in file content.

## [1.1.8] - 2026-01-18

### Fixed

- Emit the savegame-editor namespace declaration only on the XML export's root element, including exports rooted at an archive or file.

## [1.1.7] - 2025-12-27

### Added

- `Editor::loadSavegame()` with independent archive and file loading options.
- `Editor::loadFile()` for loading a file node directly.

### Changed

- `Editor::loadArchive()` returns the loaded archive node.
- `Editor::getSavegameNode()` initializes the savegame on demand.

### Removed

- `Editor::loadAllArchives()`. Use `loadSavegame(true, $loadFiles)` instead.
- `Editor::getArchiveNodes()`. Access the archive nodes through `getSavegameNode()` instead.
- The obsolete `Editor::shouldLoadArchive()` method.

## [1.1.6] - 2025-12-27

### Fixed

- Load files through `ArchiveNode::getFileNodes()`, which supplies an empty iterable when an archive has no build children.
- Document the node types returned by archive and file collections.

## [1.1.5] - 2025-12-27

### Added

- Optional file-content loading when loading an archive or all archives, controlled by a boolean argument.

### Removed

- The empty `AbstractNode::load()` method; loading is provided by the concrete nodes that need it.

## [1.1.4] - 2025-12-26

### Fixed

- Extract archives when their cache directory is missing or empty, rather than attempting to scan a nonexistent directory first.

## [1.1.3] - 2025-12-26

### Fixed

- Return an empty iterable from event-step instructions when no referenced global instructions are found, instead of returning `null`.

## [1.1.2] - 2025-12-26

### Fixed

- Restore `getBuildAttributes()` on container content nodes so their names can be exported.
- Align Composer's development branch alias with the 1.1 release line.

## [1.1.1] - 2025-12-26

### Fixed

- Correct the fixed schema-version attribute in schema 0.4 from `0.3` to `0.4`.

## [1.1.0] - 2025-12-26

### Added

- Savegame editor schema 0.4.

### Changed

- Rename the root element from `<savegame.editor>` to `<savegame>` in structure parsing and XML output, and export schema version `0.4`. Structure documents must use the new root name.
- Mark concrete node classes, archive copy implementations, `XmlBuilder`, `Converter`, `EditorConfig`, and `NodeFactory` as `final`; subclassing those classes is no longer supported.
- Make the content-node name available to subclasses as a protected property.

### Fixed

- Include the savegame-editor XML namespace in exports rooted at individual nodes.

## [1.0.13] - 2025-12-01

### Fixed

- Resolve the schema's XML namespace import through Farah's bundled XML schema instead of the external W3C URL.

## [1.0.12] - 2025-12-01

### Added

- Optional schema-version attributes on `<archive>` and `<globals>` in schema 0.3.

### Changed

- Make the schema-version attribute optional in schema 0.3.
- Update development dependencies and CI configuration.

## [1.0.11] - 2025-10-06

### Changed

- Require Core ^1.13.13 and Farah ^1.21.3.
- Update Linux and Windows integration-test infrastructure.

### Fixed

- Ensure the copy archive extractor's target directory exists using the shared filesystem helper before copying the archive.

## [1.0.10] - 2025-09-03

### Added

- Explicit `DomainException` messages for unknown archive files and file nodes, including the available file names.

### Changed

- Remove the PHP 8.0 upper bound from Composer's PHP requirement and add CI coverage through PHP 8.3; PHP 7.4 remains the minimum.
- Add native property, parameter, and return types across the editor, converters, node APIs, and build interfaces. Custom implementations and subclasses must match the new signatures.

### Fixed

- Match child, image, and global-element collection types to their `Ds\Vector` implementations.
- Align Composer's development branch alias with the 1.0 release line.

## [1.0.9] - 2025-09-02

### Added

- Jenkins integration tests and lowest/stable dependency coverage in CI.

### Changed

- Require Core ^1.13.3 and Farah ^1.16.3, and update the module manifest to Farah asset format 1.1.
- Move Composer's module bootstrap to `src/bootstrap.php`.
- Upgrade the test suite to PHPUnit 9.6.

### Removed

- The tracked Composer lockfile; development and CI resolve the allowed dependency versions instead.

## [1.0.8] - 2025-07-04

### Changed

- Build and publish API documentation through GitHub Pages instead of storing generated documentation in the repository.
- Refresh locked development dependencies and package-export configuration.

## [1.0.7] - 2024-10-05

### Changed

- Refresh locked dependencies, PHPUnit configuration, and phpDocumentor configuration. No runtime source or schema changes.

## [1.0.6] - 2024-09-29

### Changed

- Adopt the MIT license and align Composer's license metadata with it.
- Refresh locked dependencies and package-export configuration.

## [1.0.5] - 2024-09-23

### Changed

- Update development tooling, documentation configuration, source formatting, and the Composer branch alias for `main`. No runtime behavior changes.

## [1.0.4] - 2024-04-01

### Changed

- Refresh locked dependencies and generated API documentation, and update package-export configuration. No runtime source or schema changes.

## [1.0.3] - 2024-04-01

### Changed

- Refresh locked dependencies, PHPUnit configuration, and package-export rules. No runtime source or schema changes.

## [1.0.2] - 2022-01-21

### Added

- An optional `file-hash` attribute on `<savegame.editor>` in schemas 0.2 and 0.3.

### Changed

- Raise the minimum PHP version to 7.4, dropping PHP 7.2 and 7.3 support.
- Require Farah ^1.6.
- Move Composer's module registration script to `scripts/registerModule.php`.

## [1.0.1] - 2022-01-21

### Added

- File-backed XML caching keyed by node and ancestor build hashes. `EditorConfig` gains a required cache-directory constructor argument, and `XmlBuilder` requires a cache directory before exporting.
- `dictionary-ref` on groups and `/` in schema node names.
- GitHub Actions tests and regenerated API documentation.

### Changed

- Switch Composer autoloading from PSR-0 to PSR-4.
- Replace the custom `EditorElement` tree with Core's `LeanElement` in node construction and initialization.
- Support PHP 7.2 through 8.0 and require Core and Farah ^1.5.

### Removed

- The `EditorElement` class.
- `<dictionary>` and `<option>` declarations from schema 0.3.
- XML tag blacklist filtering. Its public registration and clearing methods remain, but no longer affect output.

## [1.0.0] - 2018-08-10

### Added

- Initial tagged release of the Farah module for describing, reading, editing, and rebuilding savegame files from XML structures.
- Savegame editor schemas 0.2 and 0.3, published through the module manifest.
- Configurable archive builders and extractors, including copy-based archive handling.
- Binary, bit, integer, signed-integer, select, string, image, and event-script values, with groups, dictionaries, repeated content, and reusable global instructions.
- Streamed XML export with attribute filtering and filtering of listed tags when they have no build children.
- Binary conversion helpers and event-script parsing.
- Composer packaging for PHP 7.2 and later PHP 7.x releases.

[Unreleased]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.13...HEAD
[1.3.13]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.12...1.3.13
[1.3.12]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.11...1.3.12
[1.3.11]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.10...1.3.11
[1.3.10]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.9...1.3.10
[1.3.9]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.8...1.3.9
[1.3.8]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.7...1.3.8
[1.3.7]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.6...1.3.7
[1.3.6]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.5...1.3.6
[1.3.5]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.4...1.3.5
[1.3.4]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.3...1.3.4
[1.3.3]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.2...1.3.3
[1.3.2]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.1...1.3.2
[1.3.1]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.0...1.3.1
[1.3.0]: https://github.com/Faulo/slothsoft-savegame/compare/1.2.1...1.3.0
[1.2.1]: https://github.com/Faulo/slothsoft-savegame/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/Faulo/slothsoft-savegame/compare/1.1.8...1.2.0
[1.1.8]: https://github.com/Faulo/slothsoft-savegame/compare/1.1.7...1.1.8
[1.1.7]: https://github.com/Faulo/slothsoft-savegame/compare/1.1.6...1.1.7
[1.1.6]: https://github.com/Faulo/slothsoft-savegame/compare/1.1.5...1.1.6
[1.1.5]: https://github.com/Faulo/slothsoft-savegame/compare/1.1.4...1.1.5
[1.1.4]: https://github.com/Faulo/slothsoft-savegame/compare/1.1.3...1.1.4
[1.1.3]: https://github.com/Faulo/slothsoft-savegame/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/Faulo/slothsoft-savegame/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/Faulo/slothsoft-savegame/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/Faulo/slothsoft-savegame/compare/1.0.13...1.1.0
[1.0.13]: https://github.com/Faulo/slothsoft-savegame/compare/1.0.12...1.0.13
[1.0.12]: https://github.com/Faulo/slothsoft-savegame/compare/1.0.11...1.0.12
[1.0.11]: https://github.com/Faulo/slothsoft-savegame/compare/1.0.10...1.0.11
[1.0.10]: https://github.com/Faulo/slothsoft-savegame/compare/1.0.9...1.0.10
[1.0.9]: https://github.com/Faulo/slothsoft-savegame/compare/1.0.8...1.0.9
[1.0.8]: https://github.com/Faulo/slothsoft-savegame/compare/1.0.7...1.0.8
[1.0.7]: https://github.com/Faulo/slothsoft-savegame/compare/1.0.6...1.0.7
[1.0.6]: https://github.com/Faulo/slothsoft-savegame/compare/1.0.5...1.0.6
[1.0.5]: https://github.com/Faulo/slothsoft-savegame/compare/1.0.4...1.0.5
[1.0.4]: https://github.com/Faulo/slothsoft-savegame/compare/1.0.3...1.0.4
[1.0.3]: https://github.com/Faulo/slothsoft-savegame/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/Faulo/slothsoft-savegame/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/Faulo/slothsoft-savegame/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/Faulo/slothsoft-savegame/releases/tag/1.0.0
