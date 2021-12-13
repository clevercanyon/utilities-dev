<?php
/** CLEVER CANYON™ <https://clevercanyon.com>
 *
 *  CCCCC  LL      EEEEEEE VV     VV EEEEEEE RRRRRR      CCCCC    AAA   NN   NN YY   YY  OOOOO  NN   NN ™
 * CC      LL      EE      VV     VV EE      RR   RR    CC       AAAAA  NNN  NN YY   YY OO   OO NNN  NN
 * CC      LL      EEEEE    VV   VV  EEEEE   RRRRRR     CC      AA   AA NN N NN  YYYYY  OO   OO NN N NN
 * CC      LL      EE        VV VV   EE      RR  RR     CC      AAAAAAA NN  NNN   YYY   OO   OO NN  NNN
 *  CCCCC  LLLLLLL EEEEEEE    VVV    EEEEEEE RR   RR     CCCCC  AA   AA NN   NN   YYY    OOOO0  NN   NN
 */
namespace Clever_Canyon\Utilities_Dev\Toolchain\Common;

/**
 * Dependencies.
 *
 * @since 1.0.0
 */
use Clever_Canyon\Utilities\OOPs\Version_1_0_0 as U;
use Clever_Canyon\Utilities_Dev\Toolchain\Common\{ Utilities as Common };
use Clever_Canyon\Utilities\OOPs\Version_1_0_0\{ Base };

/**
 * Utilities.
 *
 * @since 1.0.0
 */
class Utilities extends Base {
	/**
	 * Parses `~/.dev.json`.
	 *
	 * @since 1.0.0
	 *
	 * @return \StdClass Properties.
	 */
	public static function dev_json() : \StdClass {
		if ( null !== ( $cache =& static::static_cache( __FUNCTION__ ) ) ) {
			return $cache; // Cached already.
		}
		$file = getenv( 'HOME' ) . '/.dev.json';

		if ( ! is_readable( $file ) ) {
			return $cache = (object) []; // Not possible.
		}
		if ( ! $json = json_decode( file_get_contents( $file ) ) ) {
			return $cache = (object) []; // Not possible.
		}
		if ( ! is_object( $json ) ) {
			return $cache = (object) []; // Not possible.
		}
		$json = U\Fs::expand_home( $json );

		return $cache = $json;
	}
}
