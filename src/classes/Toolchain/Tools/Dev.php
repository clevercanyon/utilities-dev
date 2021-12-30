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
 * Dev utilities.
 *
 * @since 2021-12-15
 */
class Dev extends \Clever_Canyon\Utilities\STC\Version_1_0_0\Abstracts\A6t_Stc_Base {
	/**
	 * Parses `~/.dev.json`.
	 *
	 * @since 2021-12-15
	 *
	 * @param string|null $dir       Default is {@see U\Env::var() 'HOME')}.
	 * @param string|null $namespace Optional namespace. Defaults to `null`.
	 *                               If set, we extract a specific top-level namespace property from the `.dev.json` file.
	 *                               The `$namespace` is bumped up and becomes the entirety of the return object.
	 *
	 * @throws Exception On any failure, except if file does not exist, that's ok.
	 * @return \stdClass Object with `.dev.json` properties from the given `$dir` parameter.
	 */
	public static function json( /* string|null */ ?string $dir = null, /* string|null */ ?string $namespace = null ) : \stdClass {
		$dir  ??= U\Env::var( 'HOME' );
		$dir  = U\Fs::normalize( $dir );
		$file = U\Dir::join( $dir, '/.dev.json' );

		if ( null !== ( $cache = &static::stc_cache( [ __FUNCTION__, $dir, $namespace ] ) ) ) {
			return $cache; // Cached already.
		}
		if ( ! $dir || ! $file ) {
			throw new Exception( 'Missing dir: `' . $dir . '` or file: `' . $file . '`.' );
		}
		if ( ! is_file( $file ) ) {      // Special case, we allow this to slide.
			return $cache = (object) []; // Not possible. Consistent with {@see composer_json()}.
		}
		if ( ! is_readable( $file ) ) {
			throw new Exception( 'Unable to read file: `' . $file . '`.' );
		}
		if ( ! is_object( $json = U\Str::json_decode( file_get_contents( $file ) ) ) ) {
			throw new Exception( 'Unable to decode file: `' . $file . '`.' );
		}
		if ( $namespace ) {
			$json->{$namespace} = is_object( $json->{$namespace} ?? null ) ? $json->{$namespace} : (object) [];
			$json->{$namespace} = U\Ctn::super_merge( $json->{$namespace} );
			$json->{$namespace} = U\Ctn::resolve_env_vars( $json->{$namespace} );
			$json               = $json->{$namespace};
		}
		return $cache = $json;
	}
}
