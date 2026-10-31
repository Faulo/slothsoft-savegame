# Changelog

## [Unreleased]

## [1.3.13] - 2026-10-31

### Fixed

- Correct image file name and string exports to use the containing file.
- Resolve PHPStan findings in node initialization, sibling positioning, string dictionaries, and expression evaluation.
- Throw an exception when looking up an unknown value or archive.

### Deprecated

- The tag blacklist and tag cachelist registration and clearing methods are now documented no-ops. XML output, attribute filtering, and root caching retain their existing behavior.

### Removed

- Remove the unused, empty `ArchiveRepository` class.

### Changed

- Add behavior tests for XML serialization, escaping, attribute filtering, and caching using concrete test nodes.

[Unreleased]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.13...HEAD
[1.3.13]: https://github.com/Faulo/slothsoft-savegame/compare/1.3.12...1.3.13
