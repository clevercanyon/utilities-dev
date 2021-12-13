#!/usr/bin/env php
<?php
/** CLEVER CANYON™ <https://clevercanyon.com>
 *
 *  CCCCC  LL      EEEEEEE VV     VV EEEEEEE RRRRRR      CCCCC    AAA   NN   NN YY   YY  OOOOO  NN   NN ™
 * CC      LL      EE      VV     VV EE      RR   RR    CC       AAAAA  NNN  NN YY   YY OO   OO NNN  NN
 * CC      LL      EEEEE    VV   VV  EEEEE   RRRRRR     CC      AA   AA NN N NN  YYYYY  OO   OO NN N NN
 * CC      LL      EE        VV VV   EE      RR  RR     CC      AAAAAAA NN  NNN   YYY   OO   OO NN  NNN
 *  CCCCC  LLLLLLL EEEEEEE    VVV    EEEEEEE RR   RR     CCCCC  AA   AA NN   NN   YYY    OOOO0  NN   NN
 */
namespace Clever_Canyon\Utilities_Dev\Toolchain\Composer;

/**
 * Dependencies.
 *
 * @since 1.0.0
 */
use Clever_Canyon\Utilities\OOPs\Version_1_0_0 as U;
use Clever_Canyon\Utilities_Dev\Toolchain\Common\{ Utilities as Common };
use Clever_Canyon\Utilities_Dev\Toolchain\Composer\{ Project, Utilities };
use Clever_Canyon\Utilities_Dev\Toolchain\Composer\Hooks\{ Post_Update_Cmd_Handler };

/**
 * Dev mode only.
 *
 * @since 1.0.0
 */
if ( ! getenv( 'COMPOSER_DEV_MODE' ) ) {
	exit( 'Dev mode only.' );
}

/**
 * Handles `post-update-cmd` hook.
 *
 * @since 1.0.0
 */
require_once getcwd() . '/vendor/autoload.php';
new Post_Update_Cmd_Handler();
