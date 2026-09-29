<?php
/**
 * Copyright (C) 2014-2018 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

class Ai1wm_Import_Content {

	public static function execute( $params ) {

		// Set archive bytes offset
		if ( isset( $params['archive_bytes_offset'] ) ) {
			$archive_bytes_offset = (int) $params['archive_bytes_offset'];
		} else {
			$archive_bytes_offset = 0;
		}

		// Set file bytes offset
		if ( isset( $params['file_bytes_offset'] ) ) {
			$file_bytes_offset = (int) $params['file_bytes_offset'];
		} else {
			$file_bytes_offset = 0;
		}

		// Get processed files size
		if ( isset( $params['processed_files_size'] ) ) {
			$processed_files_size = (int) $params['processed_files_size'];
		} else {
			$processed_files_size = 0;
		}

		// Get total files size
		if ( isset( $params['total_files_size'] ) ) {
			$total_files_size = (int) $params['total_files_size'];
		} else {
			$total_files_size = 1;
		}

		// Get total files count
		if ( isset( $params['total_files_count'] ) ) {
			$total_files_count = (int) $params['total_files_count'];
		} else {
			$total_files_count = 1;
		}

		// Clean existing content before extracting files (only on the initial chunk)
		if ( empty( $params['content_cleaned'] ) && $archive_bytes_offset === 0 && $file_bytes_offset === 0 ) {
			Ai1wm_Status::info( __( 'Removing old plugins, themes, and uploads...', AI1WM_PLUGIN_NAME ) );
			self::clean_existing_content( $params );
			$params['content_cleaned'] = true;
		}

		// Read blogs.json file
		$handle = ai1wm_open( ai1wm_blogs_path( $params ), 'r' );

		// Parse blogs.json file
		$blogs = ai1wm_read( $handle, filesize( ai1wm_blogs_path( $params ) ) );
		$blogs = json_decode( $blogs, true );

		// Close handle
		ai1wm_close( $handle );

		// What percent of files have we processed?
		$progress = (int) min( ( $processed_files_size / $total_files_size ) * 100, 100 );

		// Set progress
		Ai1wm_Status::info( sprintf( __( 'Restoring %d files...<br />%d%% complete', AI1WM_PLUGIN_NAME ), $total_files_count, $progress ) );

		// Flag to hold if file data has been processed
		$completed = true;

		// Start time
		$start = microtime( true );

		// Open the archive file for reading
		$archive = new Ai1wm_Extractor( ai1wm_archive_path( $params ) );

		// Set the file pointer to the one that we have saved
		$archive->set_file_pointer( $archive_bytes_offset );

		$old_paths = array();
		$new_paths = array();

		// Set extract paths
		foreach ( $blogs as $blog ) {
			if ( ai1wm_main_site( $blog['Old']['BlogID'] ) === false ) {
				if ( defined( 'UPLOADBLOGSDIR' ) ) {
					// Old sites dir style
					$old_paths[] = ai1wm_files_path( $blog['Old']['BlogID'] );
					$new_paths[] = ai1wm_files_path( $blog['New']['BlogID'] );

					// New sites dir style
					$old_paths[] = ai1wm_sites_path( $blog['Old']['BlogID'] );
					$new_paths[] = ai1wm_files_path( $blog['New']['BlogID'] );
				} else {
					// Old sites dir style
					$old_paths[] = ai1wm_files_path( $blog['Old']['BlogID'] );
					$new_paths[] = ai1wm_sites_path( $blog['New']['BlogID'] );

					// New sites dir style
					$old_paths[] = ai1wm_sites_path( $blog['Old']['BlogID'] );
					$new_paths[] = ai1wm_sites_path( $blog['New']['BlogID'] );
				}
			}
		}

		// Set base site extract paths (should be added at the end of arrays)
		foreach ( $blogs as $blog ) {
			if ( ai1wm_main_site( $blog['Old']['BlogID'] ) === true ) {
				if ( defined( 'UPLOADBLOGSDIR' ) ) {
					// Old sites dir style
					$old_paths[] = ai1wm_files_path( $blog['Old']['BlogID'] );
					$new_paths[] = ai1wm_files_path( $blog['New']['BlogID'] );

					// New sites dir style
					$old_paths[] = ai1wm_sites_path( $blog['Old']['BlogID'] );
					$new_paths[] = ai1wm_files_path( $blog['New']['BlogID'] );
				} else {
					// Old sites dir style
					$old_paths[] = ai1wm_files_path( $blog['Old']['BlogID'] );
					$new_paths[] = ai1wm_sites_path( $blog['New']['BlogID'] );

					// New sites dir style
					$old_paths[] = ai1wm_sites_path( $blog['Old']['BlogID'] );
					$new_paths[] = ai1wm_sites_path( $blog['New']['BlogID'] );
				}
			}
		}

		while ( $archive->has_not_reached_eof() ) {
			$file_bytes_written = 0;

			// Exclude WordPress files
			$exclude_files = array_keys( _get_dropins() );

			// Exclude plugin files and third-party backup/cache folders
			$exclude_files = array_merge( $exclude_files, array(
				AI1WM_PACKAGE_NAME,
				AI1WM_MULTISITE_NAME,
				AI1WM_DATABASE_NAME,
				AI1WM_MUPLUGINS_NAME,
				'updraft',
				'wpvividbackups',
				'wpvivid_staging',
				'wpvivid_uploads',
				'wpo-cache',
				'wpo-cache-old',
				'cache',
				'litespeed',
				'upgrade-temp-backup',
				'maintenance',
				'wflogs',
				'nfwlog',
				'backwpup',
				'backupbuddy_backups',
				'duplicator',
			) );

			// Extract a file from archive to WP_CONTENT_DIR
			if ( ( $completed = $archive->extract_one_file_to( WP_CONTENT_DIR, $exclude_files, $old_paths, $new_paths, $file_bytes_written, $file_bytes_offset ) ) ) {
				$file_bytes_offset = 0;
			}

			// Get archive bytes offset
			$archive_bytes_offset = $archive->get_file_pointer();

			// Increment processed files size
			$processed_files_size += $file_bytes_written;

			// What percent of files have we processed?
			$progress = (int) min( ( $processed_files_size / $total_files_size ) * 100, 100 );

			// Set progress
			Ai1wm_Status::info( sprintf( __( 'Restoring %d files...<br />%d%% complete', AI1WM_PLUGIN_NAME ), $total_files_count, $progress ) );

			// More than 10 seconds have passed, break and do another request
			if ( ( $timeout = apply_filters( 'ai1wm_completed_timeout', 10 ) ) ) {
				if ( ( microtime( true ) - $start ) > $timeout ) {
					$completed = false;
					break;
				}
			}
		}

		// End of the archive?
		if ( $archive->has_reached_eof() ) {

			// Unset archive bytes offset
			unset( $params['archive_bytes_offset'] );

			// Unset file bytes offset
			unset( $params['file_bytes_offset'] );

			// Unset processed files size
			unset( $params['processed_files_size'] );

			// Unset total files size
			unset( $params['total_files_size'] );

			// Unset total files count
			unset( $params['total_files_count'] );

			// Unset completed flag
			unset( $params['completed'] );

			// Unset content cleaned flag
			unset( $params['content_cleaned'] );

		} else {

			// Set archive bytes offset
			$params['archive_bytes_offset'] = $archive_bytes_offset;

			// Set file bytes offset
			$params['file_bytes_offset'] = $file_bytes_offset;

			// Set processed files size
			$params['processed_files_size'] = $processed_files_size;

			// Set total files size
			$params['total_files_size'] = $total_files_size;

			// Set total files count
			$params['total_files_count'] = $total_files_count;

			// Set completed flag
			$params['completed'] = $completed;

			// Set content cleaned flag
			$params['content_cleaned'] = true;
		}

		// Close the archive file
		$archive->close();

		return $params;
	}

	/**
	 * Clean existing content (plugins, themes, uploads, cache) before restoring archive
	 *
	 * @param array $params
	 * @return void
	 */
	public static function clean_existing_content( $params ) {
		// Read package.json to know what components are present in the backup
		$package = array();
		if ( is_file( ai1wm_package_path( $params ) ) ) {
			if ( ( $handle = ai1wm_open( ai1wm_package_path( $params ), 'r' ) ) ) {
				$package_data = ai1wm_read( $handle, filesize( ai1wm_package_path( $params ) ) );
				$package = json_decode( $package_data, true );
				ai1wm_close( $handle );
			}
		}

		// 1. Clean existing plugins if backup contains plugins
		if ( empty( $package['NoPlugins'] ) ) {
			$plugins_dir = defined( 'WP_PLUGIN_DIR' ) ? WP_PLUGIN_DIR : ( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' );
			if ( is_dir( $plugins_dir ) ) {
				// Build list of plugins to exclude (MUST NEVER DELETE MIGRATION PLUGIN OR ITS EXTENSIONS)
				$excluded_plugins = array(
					'index.php',
					basename( AI1WM_PATH ),
				);

				if ( defined( 'AI1WM_PLUGIN_BASENAME' ) ) {
					$excluded_plugins[] = dirname( AI1WM_PLUGIN_BASENAME );
				}

				if ( function_exists( 'ai1wm_active_servmask_plugins' ) ) {
					foreach ( ai1wm_active_servmask_plugins() as $sm_plugin ) {
						$excluded_plugins[] = dirname( $sm_plugin );
					}
				}

				$excluded_plugins = array_unique( array_filter( $excluded_plugins ) );

				self::clean_directory_contents( $plugins_dir, function ( $item, $full_path ) use ( $excluded_plugins ) {
					// Protect any file/folder starting with '.'
					if ( strpos( $item, '.' ) === 0 ) {
						return false;
					}

					// Protect standard index.php
					if ( $item === 'index.php' ) {
						return false;
					}

					// Protect any plugin folder matching all-in-one-wp-migration*
					if ( strpos( $item, 'all-in-one-wp-migration' ) === 0 ) {
						return false;
					}

					// Protect current plugin path or active servmask extensions
					if ( in_array( $item, $excluded_plugins, true ) ) {
						return false;
					}

					if ( defined( 'AI1WM_PATH' ) && realpath( $full_path ) === realpath( AI1WM_PATH ) ) {
						return false;
					}

					return true;
				} );
			}
		}

		// 2. Clean existing themes if backup contains themes
		if ( empty( $package['NoThemes'] ) ) {
			$themes_dir = function_exists( 'get_theme_root' ) ? get_theme_root() : ( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'themes' );
			if ( is_dir( $themes_dir ) ) {
				self::clean_directory_contents( $themes_dir, function ( $item, $full_path ) {
					// Protect any file/folder starting with '.'
					if ( strpos( $item, '.' ) === 0 ) {
						return false;
					}

					// Protect standard index.php
					if ( $item === 'index.php' ) {
						return false;
					}

					return true;
				} );
			}
		}

		// 3. Clean existing media/uploads if backup contains media
		if ( empty( $package['NoMedia'] ) ) {
			$uploads_dirs = array();

			// WordPress upload dir
			if ( function_exists( 'wp_upload_dir' ) ) {
				$upload_data = wp_upload_dir();
				if ( ! empty( $upload_data['basedir'] ) && is_dir( $upload_data['basedir'] ) ) {
					$uploads_dirs[] = $upload_data['basedir'];
				}
			}

			// Fallback/standard uploads directory
			$standard_uploads = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads';
			if ( is_dir( $standard_uploads ) && ! in_array( $standard_uploads, $uploads_dirs ) ) {
				$uploads_dirs[] = $standard_uploads;
			}

			// Multisite blogs.dir
			$blogs_dir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'blogs.dir';
			if ( is_dir( $blogs_dir ) ) {
				$uploads_dirs[] = $blogs_dir;
			}

			$backups_path = defined( 'AI1WM_BACKUPS_PATH' ) ? AI1WM_BACKUPS_PATH : ( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'ai1wm-backups' );
			$backups_real = realpath( $backups_path );
			$archive_real = realpath( ai1wm_archive_path( $params ) );

			foreach ( $uploads_dirs as $uploads_dir ) {
				self::clean_directory_contents( $uploads_dir, function ( $item, $full_path ) use ( $backups_real, $archive_real ) {
					if ( in_array( $item, array( 'index.php', '.htaccess', 'web.config' ), true ) ) {
						return false;
					}

					if ( $item === 'ai1wm-backups' ) {
						return false;
					}

					$real = realpath( $full_path );
					if ( $real ) {
						if ( $backups_real && ( $real === $backups_real || strpos( $real, $backups_real . DIRECTORY_SEPARATOR ) === 0 ) ) {
							return false;
						}

						if ( $archive_real && $real === $archive_real ) {
							return false;
						}
					}

					return true;
				} );
			}
		}

		// 4. Clean all foreign and orphaned directories and files directly in WP_CONTENT_DIR
		if ( is_dir( WP_CONTENT_DIR ) ) {
			$whitelisted_dirs = array(
				'ai1wm-backups',
				'plugins',
				'themes',
				'uploads',
				'mu-plugins',
				'languages',
				'upgrade',
				'imunify-security',
				'blogs.dir',
				'fonts',
			);

			if ( defined( 'AI1WM_BACKUPS_PATH' ) ) {
				$whitelisted_dirs[] = basename( AI1WM_BACKUPS_PATH );
			}

			$whitelisted_dirs = array_unique( array_filter( $whitelisted_dirs ) );

			$whitelisted_files = array(
				'index.php',
				'.htaccess',
				'db.php',
			);

			$content_items = @scandir( WP_CONTENT_DIR );
			if ( false !== $content_items ) {
				$backups_real = defined( 'AI1WM_BACKUPS_PATH' ) ? realpath( AI1WM_BACKUPS_PATH ) : false;
				$archive_real = realpath( ai1wm_archive_path( $params ) );

				foreach ( $content_items as $item ) {
					if ( $item === '.' || $item === '..' ) {
						continue;
					}

					$full_path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $item;
					$real = realpath( $full_path );

					// NEVER delete AI1WM backups directory or archive
					if ( $item === 'ai1wm-backups' || ( $backups_real && ( $real === $backups_real || strpos( $real, $backups_real . DIRECTORY_SEPARATOR ) === 0 ) ) ) {
						continue;
					}

					if ( $archive_real && $real === $archive_real ) {
						continue;
					}

					if ( is_dir( $full_path ) ) {
						if ( ! in_array( $item, $whitelisted_dirs, true ) ) {
							self::delete_directory_recursive( $full_path );
						}
					} else {
						if ( ! in_array( $item, $whitelisted_files, true ) ) {
							@chmod( $full_path, 0666 );
							@unlink( $full_path );
						}
					}
				}
			}

			// Ensure a clean standard WordPress index.php exists in WP_CONTENT_DIR
			$index_file = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'index.php';
			if ( ! is_file( $index_file ) ) {
				@file_put_contents( $index_file, "<?php\n// Silence is golden.\n" );
			}
		}
	}

	/**
	 * Delete items inside a directory based on a filter callback
	 *
	 * @param string   $dir Directory path
	 * @param callable $filter_callback Callback receiving ($item_name, $full_path). Returns true if item should be deleted.
	 * @return void
	 */
	public static function clean_directory_contents( $dir, $filter_callback ) {
		if ( ! is_dir( $dir ) || ! is_readable( $dir ) ) {
			return;
		}

		$items = @scandir( $dir );
		if ( false === $items ) {
			return;
		}

		foreach ( $items as $item ) {
			if ( $item === '.' || $item === '..' ) {
				continue;
			}

			$full_path = $dir . DIRECTORY_SEPARATOR . $item;

			// Ask callback if this item should be deleted
			if ( is_callable( $filter_callback ) && ! call_user_func( $filter_callback, $item, $full_path ) ) {
				continue;
			}

			if ( is_dir( $full_path ) && ! is_link( $full_path ) ) {
				self::delete_directory_recursive( $full_path );
			} else {
				@chmod( $full_path, 0666 );
				@unlink( $full_path );
			}
		}
	}

	/**
	 * Recursively delete a directory and all its contents
	 *
	 * @param string $dir Directory path to delete
	 * @return void
	 */
	public static function delete_directory_recursive( $dir ) {
		if ( ! is_dir( $dir ) ) {
			if ( is_file( $dir ) || is_link( $dir ) ) {
				@chmod( $dir, 0666 );
				@unlink( $dir );
			}
			return;
		}

		$items = @scandir( $dir );
		if ( false === $items ) {
			return;
		}

		foreach ( $items as $item ) {
			if ( $item === '.' || $item === '..' ) {
				continue;
			}

			$full_path = $dir . DIRECTORY_SEPARATOR . $item;
			if ( is_dir( $full_path ) && ! is_link( $full_path ) ) {
				self::delete_directory_recursive( $full_path );
			} else {
				@chmod( $full_path, 0666 );
				@unlink( $full_path );
			}
		}

		@chmod( $dir, 0777 );
		@rmdir( $dir );
	}
}
