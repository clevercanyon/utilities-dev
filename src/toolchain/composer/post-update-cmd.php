#!/usr/bin/env php
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
namespace Clever_Canyon\Utilities_Dev\Toolchain\Composer;

/**
 * Utilities.
 *
 * @since 2021-12-15
 */
use Clever_Canyon\Utilities\OOPs\{Version_1_0_0 as U};
use Clever_Canyon\Utilities\OOP\Version_1_0_0\{Exception};

/**
 * Toolchain.
 *
 * @since 2021-12-15
 */
use Clever_Canyon\Utilities_Dev\Toolchain\{Tools as T};
use Clever_Canyon\Utilities_Dev\Toolchain\Composer\{Project};

/**
 * File-specific.
 *
 * @since 2021-12-15
 */
use Clever_Canyon\Utilities_Dev\Toolchain\Composer\Hooks\{Post_Update_Cmd_Handler};

// </editor-fold>

/**
 * Dev mode only.
 *
 * @since 2021-12-15
 */
if ( ! getenv( 'COMPOSER_DEV_MODE' ) ) {
	exit( 'Dev mode only.' );
}

/**
 * Handles `post-update-cmd` hook.
 *
 * @since 2021-12-15
 */
require_once dirname( __FILE__, 4 ) . '/vendor/autoload.php';
new Post_Update_Cmd_Handler();
