<?php
/**
 * CLEVER CANYON™ {@see https://clevercanyon.com}
 *
 *  CCCCC  LL      EEEEEEE VV     VV EEEEEEE RRRRRR      CCCCC    AAA   NN   NN YY   YY  OOOOO  NN   NN ™
 * CC      LL      EE      VV     VV EE      RR   RR    CC       AAAAA  NNN  NN YY   YY OO   OO NNN  NN
 * CC      LL      EEEEE    VV   VV  EEEEE   RRRRRR     CC      AA   AA NN N NN  YYYYY  OO   OO NN N NN
 * CC      LL      EE        VV VV   EE      RR  RR     CC      AAAAAAA NN  NNN   YYY   OO   OO NN  NNN
 *  CCCCC  LLLLLLL EEEEEEE    VVV    EEEEEEE RR   RR     CCCCC  AA   AA NN   NN   YYY    OOOO0  NN   NN
 */
// <editor-fold desc="Strict types, namespace, use statements, and other headers.">

/**
 * Declarations & namespace.
 *
 * @since 2021-12-25
 */
declare( strict_types = 1 ); // ｡･:*:･ﾟ★.
namespace Clever_Canyon\Utilities_Dev\Toolchain\Tools;

/**
 * Utilities.
 *
 * @since 2021-12-15
 */
use Clever_Canyon\Utilities\STC\{Version_1_0_0 as U};
use Clever_Canyon\Utilities\OOP\Version_1_0_0\{Offsets, Generic, Error, Exception, Fatal_Exception};
use Clever_Canyon\Utilities\OOP\Version_1_0_0\Abstracts\{A6t_Base, A6t_Offsets, A6t_Generic, A6t_Error, A6t_Exception};
use Clever_Canyon\Utilities\OOP\Version_1_0_0\Interfaces\{I7e_Base, I7e_Offsets, I7e_Generic, I7e_Error, I7e_Exception};

/**
 * Toolchain.
 *
 * @since 2021-12-15
 */
use Clever_Canyon\Utilities_Dev\Toolchain\{Tools as T};

// </editor-fold>

/**
 * Composer utilities.
 *
 * @since 2021-12-15
 */
class Composer extends \Clever_Canyon\Utilities\STC\Version_1_0_0\Abstracts\A6t_Stc_Base {
	/**
	 * Packages directory regexp.
	 *
	 * @since 2021-12-15
	 */
	public const PACKAGES_DIR_REGEXP = '/^(vendor|node_modules)$/u';

	/**
	 * Composer package name max bytes.
	 *
	 * @since 2021-12-15
	 *
	 * @note  Composer seemingly doesn't have or document a limit.
	 *        We'll use the same limit as NPM does, which is 214 characters.
	 */
	public const PACKAGE_NAME_MAX_BYTES = 214;

	/**
	 * Composer package name regexp.
	 *
	 * @since 2021-12-15
	 *
	 * @see   https://getcomposer.org/doc/04-schema.md#name
	 */
	public const PACKAGE_NAME_REGEXP = '/^([a-z0-9](?:[_.-]?[a-z0-9]+)*)\/([a-z0-9](?:(?:[_.]?|-{0,2})[a-z0-9]+)*)$/u';

	/**
	 * Parses a `composer.json` file.
	 *
	 * @since 2021-12-15
	 *
	 * @param string      $dir        Directory path.
	 * @param string|null $namespace  Namespace. Defaults to `null`.
	 *                                If set, we extract a specific top-level namespace property from the `extra` props section in a
	 *                                `composer.json` file. The `$namespace` is bumped up and becomes the only `extra` props.
	 *
	 * @param object|null $_r         For internal recursive use only.
	 *
	 * @throws Exception On any failure, except if file does not exist. That's ok ... unless the file is associated with an `@extends-packages`
	 *                    directive, in which case an exception *will* be thrown, as that would be unexpected behavior and likely problematic.
	 *
	 * @return object Object with `composer.json` properties from the given `$dir` parameter.
	 */
	public static function json( string $dir, /* string|null */ ?string $namespace = null, /* \stdClass|null */ ?object $_r = null ) : object {
		// Setup variables.

		$is_recursive = isset( $_r );
		$_r           ??= (object) [];

		$dir  = U\Fs::normalize( $dir );
		$file = U\Dir::join( $dir, '/composer.json' );

		if ( ! $is_recursive ) {
			$_r->dir = $dir; // Top-level project dir.
		}
		// Check the cache.

		if ( ! $is_recursive ) {
			$cache_key = [ __FUNCTION__, $dir, $namespace ];
			if ( null !== ( $cache = &static::stc_cache( $cache_key ) ) ) {
				return $cache; // Cached already.
			}
		}
		// Validate, setup, early returns.

		if ( ! $dir || ! $file ) {
			throw new Exception( 'Missing dir: `' . $dir . '` or file: `' . $file . '`.' );
		}
		if ( ! is_file( $file ) ) {      // Special case, we allow this to slide.
			return $cache = (object) []; // Not possible. Consistent with {@see dev_json()}.
		}
		if ( ! is_readable( $file ) ) {
			throw new Exception( 'Unable to read file: `' . $file . '`.' );
		}
		if ( ! is_object( $json = U\Str::json_decode( file_get_contents( $file ) ) ) ) {
			throw new Exception( 'Unable to decode file: `' . $file . '`.' );
		}
		if ( ! is_object( $json->extra ?? null ) ) {
			$json->extra = (object) [];
		}
		if ( $namespace && ! is_object( $json->extra->{$namespace} ?? null ) ) {
			$json->extra->{$namespace} = (object) [];
		}
		// Maybe handles `@extends-packages` directive(s) recursively.

		if ( $namespace && property_exists( $json->extra->{$namespace}, '@extends-packages' ) ) {
			// Validate `@extends-packages` directive.

			if ( ! is_array( $json->extra->{$namespace}->{'@extends-packages'} ) ) {
				throw new Exception( 'Unexpected `@extends-packages` directive in: `' . $file . '`. Must be array.' );
			}
			// Compile packages that we need to extend, recursively.

			$_extends_json_extra_namespace = (object) []; // Initialize.

			foreach ( $json->extra->{$namespace}->{'@extends-packages'} as $_package_name ) {
				if ( ! $_package_name
					|| ! is_string( $_package_name )
					|| ! preg_match( T\Composer::PACKAGE_NAME_REGEXP, $_package_name )
					|| strlen( $_package_name ) > T\Composer::PACKAGE_NAME_MAX_BYTES
				) {
					throw new Exception(
						'Unexpected `@extends-packages` entry: `' . $_package_name . '` in: `' . $file . '`.' .
						' Must match pattern: `' . T\Composer::PACKAGE_NAME_REGEXP . '`' .
						' and be <= `' . T\Composer::PACKAGE_NAME_MAX_BYTES . '` bytes in length.'
					);
				}
				$_package_dir  = U\Dir::join( $_r->dir, '/vendor/' . $_package_name );
				$_package_file = U\Dir::join( $_package_dir, '/composer.json' );

				if ( ! is_file( $_package_file ) ) { // Report the case of missing dependency.
					throw new Exception(
						'Missing `composer.json` file for `@extends-packages` entry: `' . $_package_name . '`' .
						' in: `' . $file . '`. The missing location is: `' . $_package_file . '`.'
					);
				}
				$_package_json = T\Composer::json( $_package_dir, $namespace, $_r );

				if ( is_object( $_package_json->extra ?? null ) && is_object( $_package_json->extra->{$namespace} ?? null ) ) {
					$_extends_json_extra_namespace = U\Ctn::super_merge( $_extends_json_extra_namespace, $_package_json->extra->{$namespace} );
				}
			}
			// Merge into everything we're extending.

			if ( $_extends_json_extra_namespace ) {
				$json->extra->{$namespace} = U\Ctn::super_merge( $_extends_json_extra_namespace, $json->extra->{$namespace} );
			}
			// Drop the `@extends-packages` directive now.

			unset( $json->extra->{$namespace}->{'@extends-packages'} );
		}
		// Maybe bump namespace up into extra props.

		if ( ! $is_recursive && $namespace ) {
			$extra_env_vars = [
				'PROJECT_DIR'  => $dir,
				'PROJECT_NAME' => $json->name ?? '',
			];
			$extra_env_vars = array_map( 'strval', $extra_env_vars );

			$json->extra->{$namespace} = U\Ctn::super_merge( $json->extra->{$namespace} );
			$json->extra->{$namespace} = U\Ctn::resolve_env_vars( $json->extra->{$namespace}, $extra_env_vars );
			$json->extra               = $json->extra->{$namespace};
		}
		// Return cache.

		return $cache = $json;
	}
}
