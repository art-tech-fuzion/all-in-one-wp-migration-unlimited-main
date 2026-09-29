# Changelog

All notable changes and updates to this project are documented in this file.

---

## [8.0.0] - 2026-09-30

### Overview
Version 8.0.0 is a major update focused on universal `.wpress` archive format compatibility, clean-replace restoration architecture, root `wp-content/` foreign folder purging, and enterprise-grade migration stability.

---

### Added & Improved Features

#### 1. Universal Archive Compatibility (.wpress v1 & v2 CRC32)
- Added full support for modern `.wpress` v2 archives with 4,088-byte file path headers and 8-byte CRC32 block checksums (total block size 4,377 bytes).
- Implemented dual-format header parsing to seamlessly handle both legacy (4,096-byte) and modern (4,377-byte) archive streams.
- Resolved false-positive validation errors where valid backups were rejected with *"Archive is corrupted"* or *"Please make sure that your file was exported using All-in-One WP Migration"*.
- Added dynamic end-of-file detection supporting both legacy single-block and modern multi-block CRC EOF signatures (`is_v1_eof()`, `is_v2_eof()`, `is_eof_block()`).

#### 2. Clean-Replace Restoration Architecture
- Replaced the default partial-overlay extraction behavior with automated pre-extraction purging.
- When restoring a backup, obsolete plugins, inactive themes, and previous media files are wiped so that **only the content from the restored backup remains**.
- Added component-aware safety:
  - Plugins are cleaned only if the backup includes plugins (`NoPlugins` flag is empty).
  - Themes are cleaned only if the backup includes themes (`NoThemes` flag is empty).
  - Media/Uploads are cleaned only if the backup includes media (`NoMedia` flag is empty).
- Integrated multi-chunk state tracking (`content_cleaned` flag) to ensure the wipe executes strictly once on the initial offset and never impacts newly extracted files across subsequent AJAX chunks.

#### 3. Root `wp-content/` Foreign Directory & Leftover Sweep
- Added automatic detection and deletion of leftover third-party backup directories:
  - `wp-content/updraft/` (UpdraftPlus backups)
  - `wp-content/wpvividbackups/` (WPvivid backups)
  - `wp-content/wpvivid_staging/` (WPvivid staging environments)
  - `wp-content/wpvivid_uploads/` (WPvivid staging uploads)
- Added automatic purging of obsolete cache folders and core update rollbacks:
  - `wp-content/wpo-cache/` and `wp-content/wpo-cache-old/` (WP-Optimize cache)
  - `wp-content/litespeed/` (LiteSpeed cache directory)
  - `wp-content/cache/` (Page and object cache files)
  - `wp-content/upgrade-temp-backup/` (WordPress core rollback storage)
  - `wp-content/maintenance/` (Maintenance mode files)
- Added cleanup for orphan logs and leftover drop-ins:
  - `wf-licensing.log`, `advanced-cache.php`, `advanced-cache.php-old`, `index.php-old`, `.analyst_analyst_cache`.
- Automatic verification and generation of a clean, secure `index.php` (`<?php // Silence is golden.`) in `wp-content/`.

#### 4. Stream Extraction Exclusion Filters
- Added exclusion rules during archive decompression to prevent third-party backup and cache folders from being extracted:
  - Excluded patterns: `updraft`, `wpvividbackups`, `wpvivid_staging`, `wpvivid_uploads`, `wpo-cache`, `wpo-cache-old`, `cache`, `litespeed`, `upgrade-temp-backup`, `maintenance`, `wflogs`, `nfwlog`, `backwpup`, `backupbuddy_backups`, `duplicator`.
- Guarantees that even if an incoming backup archive was created on a website containing nested backup files from other tools, they are skipped and never unpacked.

#### 5. Safety & System Shields
- **Active Plugin Protection:** The running migration plugin directory (`all-in-one-wp-migration*`, `basename(AI1WM_PATH)`, and active ServMask extensions) is strictly excluded from all deletion routines, preventing AJAX loopback interruptions.
- **Backup Storage Shield:** `AI1WM_BACKUPS_PATH` (`wp-content/ai1wm-backups`) and the archive being imported are strictly protected from deletion via realpath checks.
- **System Assets Preserved:** Host-required drop-ins (`mu-plugins/`), server security suites (`imunify-security/`), `languages/`, `index.php`, and `.htaccess` are preserved.

#### 6. Dynamic PHP Version Check
- Replaced hardcoded PHP version checks with dynamic major-version comparison in import confirmation.
- Displays clear, contextual notices when restoring across different major PHP versions (e.g. PHP 8.2 to PHP 8.5) without blocking execution.

---

### Files Modified in this Release

| Component | Path | Description |
|---|---|---|
| Plugin Header | `all-in-one-wp-migration.php` | Updated version header to `8.0.0`. |
| Version Constant | `constants.php` | Updated `AI1WM_VERSION` to `8.0.0`. |
| Archiver Engine | `lib/vendor/servmask/archiver/class-ai1wm-archiver.php` | Added 4,088-byte block format, dual-format EOF detection, and CRC data setters. |
| Extractor Engine | `lib/vendor/servmask/archiver/class-ai1wm-extractor.php` | Updated EOF checks to `is_eof_block()`; adjusted block format unpacking logic. |
| Compressor Engine | `lib/vendor/servmask/archiver/class-ai1wm-compressor.php` | Synchronized header block format unpacking with archiver specification. |
| Confirmation Model | `lib/model/import/class-ai1wm-import-confirm.php` | Added dynamic source vs. target PHP major version warning notice. |
| Content Import Model | `lib/model/import/class-ai1wm-import-content.php` | Implemented pre-extraction purge, root `wp-content` sweep, extraction exclusion filters, and directory cleaners. |
| Documentation | `README.md` | Restructured with comprehensive plugin overview and user documentation. |
| Changelog | `changeLog.md` | Created technical release notes for version 8.0.0. |
