# All-in-One WP Migration (Unlimited Edition)

[![Plugin Version](https://img.shields.io/badge/version-8.0.0-blue.svg)](https://github.com/)
[![WordPress Compatibility](https://img.shields.io/badge/wordpress-4.7%2B-green.svg)](https://wordpress.org/)
[![PHP Compatibility](https://img.shields.io/badge/php-7.2--8.3%2B-purple.svg)](https://www.php.net/)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

> **Maintained & Enhanced by:** **Rahul Kumar** ([@rahulkcreation](https://github.com/rahulkcreation)) — **Art-Tech Fuzion Team**

---

## Overview

**All-in-One WP Migration (Unlimited Edition)** is a reliable, high-performance migration, backup, and website cloning solution for WordPress. It enables site owners, agencies, and developers to bundle their entire WordPress installation—including the database, media files, plugins, themes, and configuration—into a single `.wpress` archive and restore it onto any host or domain with a single click.

Unlike standard migration tools that restrict import file sizes or leave leftover files behind, this edition features **unlimited import capacity**, **chunked streaming to bypass server limits**, and a **clean-replace restoration architecture** that guarantees your restored website is clean, optimized, and conflict-free.

---

## What is it Used For?

- **Local to Live Deployment:** Build and test your website locally (e.g., LocalWP, XAMPP, Docker) and deploy it to a live production server with zero manual database imports or FTP hassle.
- **Host-to-Host Migration:** Move websites seamlessly between any hosting providers (cPanel, Cloudways, Kinsta, WP Engine, VPS, Shared Hosting) without worrying about server configurations.
- **Disaster Recovery & Snapshots:** Generate point-in-time full site backups that can be restored instantly if a site is compromised, broken by an update, or lost.
- **Staging & Site Cloning:** Clone existing production sites to staging domains or test environments in minutes for safe redesigns, plugin testing, or client demos.
- **Large-Scale Website Transfers:** Easily migrate enterprise-scale websites with tens of thousands of media uploads and large databases without running into upload ceilings or execution timeouts.

---

## How It Works

```
+-------------------------------------------------------------------------+
|                            EXPORT WORKFLOW                              |
|                                                                         |
|  [WordPress Database] + [Plugins] + [Themes] + [Uploads]                |
|                           |                                             |
|                           v                                             |
|        Serialized Search-and-Replace + Enumeration Engine                |
|                           |                                             |
|                           v                                             |
|            Single Compressed `.wpress` Archive Stream                   |
+-------------------------------------------------------------------------+

+-------------------------------------------------------------------------+
|                            IMPORT WORKFLOW                              |
|                                                                         |
|  1. Chunked Upload (bypasses upload_max_filesize & Cloudflare limits)    |
|  2. Archive Integrity Check (validates v1 & v2 CRC32 headers)           |
|  3. Pre-Extraction Clean Wipe:                                          |
|     - Sweeps obsolete plugins, inactive themes & old uploads            |
|     - Purges foreign folders (updraft, wpvivid, stale cache, old logs)  |
|  4. Streamed Archive Extraction (restores exact backup content)          |
|  5. Database Import with Automatic Domain & File Path Re-mapping        |
|  6. Cache Flush & Rewrite Finalization                                  |
+-------------------------------------------------------------------------+
```

1. **Chunked Streaming Engine:** Uploads files in small 5 MB chunks, completely bypassing PHP `upload_max_filesize`, `post_max_size`, and web server timeout restrictions.
2. **True Clean-Replace Restoration:** Before extracting files, the plugin sweeps away old plugins, themes, media files, and leftover third-party folders (such as previous Updraft or WPvivid directories), ensuring only the content from the backup remains.
3. **Serialized-Safe Database Re-mapping:** Automatically updates URLs and server directory paths throughout the database, properly recalculating PHP serialized string lengths and JSON structures to prevent corrupted widgets, menus, and theme options.
4. **Resilient Timeout Protection:** Restores databases and content in incremental, multi-second AJAX loops. If a server request pauses or reaches its time limit, the process seamlessly resumes from the exact file pointer.

---

## Core Capabilities

- **Unlimited Import Size:** No paywalls, artificial thresholds, or size restrictions.
- **Dual `.wpress` Format Support:** Compatible with both legacy v1 and modern v2 archives containing 4,088-byte paths and 8-byte CRC32 block checksums.
- **Clean Restoration Footprint:** Eliminates orphan files, obsolete caches, rollback folders, and old log files from previous installations.
- **Self-Preservation Shields:** Protects the active migration plugin, user backup files (`wp-content/ai1wm-backups`), host drop-ins (`mu-plugins`), and server security tools (`imunify-security`).
- **Cross-PHP Version Flexibility:** Restores smoothly across PHP 7.2 through PHP 8.3+ with intelligent cross-version notifications.
- **Zero Third-Party Dependencies:** Works entirely in native PHP with no external software or shell utilities required.

---

## System Requirements

| Requirement | Supported Versions | Recommended |
|---|---|---|
| **WordPress** | 4.7 or higher | 6.0+ |
| **PHP** | 7.2 through 8.3+ | 8.1 or 8.2 |
| **Database** | MySQL 5.6+ / MariaDB 10.1+ | MySQL 8.0+ / MariaDB 10.6+ |
| **Web Server** | Apache, Nginx, LiteSpeed, IIS | Nginx or Apache |
| **PHP Extensions** | `json`, `mbstring`, `pcre`, `zlib`, `curl` | Enabled |

---

## Installation

### Via WordPress Admin Panel
1. Download the plugin ZIP archive.
2. Log in to your WordPress admin dashboard and navigate to **Plugins > Add New > Upload Plugin**.
3. Choose the ZIP file and click **Install Now**.
4. Click **Activate Plugin**.

### Manual Installation (SFTP / FTP / SSH)
1. Extract the plugin folder into your WordPress plugins directory:
   ```
   wp-content/plugins/all-in-one-wp-migration/
   ```
2. Go to **Plugins** in your WordPress admin dashboard and click **Activate**.

---

## How to Use

### Exporting a Site
1. Navigate to **All-in-One WP Migration > Export** in the admin sidebar.
2. *(Optional)* Click **Advanced Options** to exclude post revisions, spam comments, or specific directories if desired.
3. Click **Export To > FILE**.
4. Once compilation finishes, click the download button to save your `.wpress` backup file.

### Importing & Restoring a Site
1. On the destination WordPress site, navigate to **All-in-One WP Migration > Import**.
2. Drag and drop your `.wpress` file into the upload area (or select **Import From > FILE**).
3. The plugin will upload the archive in streaming chunks and validate its checksums.
4. When prompted, click **PROCEED** to begin restoration. The plugin will:
   - Purge old plugins, themes, uploads, and foreign residual folders.
   - Extract the files from the archive.
   - Restore the database and update URLs/paths.
5. Once complete, navigate to **Settings > Permalinks** and click **Save Changes** twice to refresh your URL rewrite rules.

---

## Version History & Changelog

For detailed technical release notes, bug fixes, and architectural updates, please refer to [changeLog.md](changeLog.md).

---

## Credits & Maintainer

- **Enhanced & Maintained by:** **Rahul Kumar**
  - **Team:** Art-Tech Fuzion Team
  - **GitHub:** [@rahulkcreation](https://github.com/rahulkcreation)
- **Original Plugin:** ServMask Inc.

---

## License

This software is licensed under the **GNU General Public License, version 3 (GPL-3.0)**. You may redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation.
