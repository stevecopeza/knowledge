<?php

namespace Knowledge\Infrastructure;

class FilesystemInitializer {

	public static function init(): void {
		$root = KNOWLEDGE_DATA_PATH;

		$dirs = [
			$root,
			$root . '/articles',
			$root . '/versions',
			$root . '/forks',
			$root . '/media',
			$root . '/ai',
			$root . '/temp',
		];

		foreach ( $dirs as $dir ) {
			if ( ! file_exists( $dir ) ) {
				wp_mkdir_p( $dir );
			}
		}

		self::ensure_security();
	}

	public static function ensure_security(): void {
		$root     = KNOWLEDGE_DATA_PATH;
		$htaccess = $root . '/.htaccess';

		if ( ! file_exists( $htaccess ) ) {
			file_put_contents( $htaccess, "Deny from all\n" );
		}
	}
}
