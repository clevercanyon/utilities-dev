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
use Clever_Canyon\Utilities\{STC as U};
use Clever_Canyon\Utilities\OOP\{Offsets, Generic, Error, Exception, Fatal_Exception};
use Clever_Canyon\Utilities\OOP\Abstracts\{A6t_Base, A6t_Offsets, A6t_Generic, A6t_Error, A6t_Exception};
use Clever_Canyon\Utilities\OOP\Interfaces\{I7e_Base, I7e_Offsets, I7e_Generic, I7e_Error, I7e_Exception};

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
 * CLI mode only.
 *
 * @since 2021-12-15
 */
if ( 'cli' !== PHP_SAPI ) {
	exit( 'CLI mode only.' );
}

/**
 * Dev mode only.
 *
 * @since 2021-12-15
 */
if ( ! getenv( 'COMPOSER_DEV_MODE' ) ) {
	exit( 'Dev mode only.' );
}

/**
 * Gets current working dir.
 *
 * @since 2021-12-15
 */
${__FILE__}[ 'cwd' ] = getcwd();

/**
 * Requires autoloader.
 *
 * @since 2021-12-15
 */
require_once ${__FILE__}[ 'cwd' ] . '/vendor/autoload.php';

/**
 * Enables debugging mode.
 *
 * @since 2021-12-15
 */
U\Env::config_debugging_mode();

/**
 * Handles `post-update-cmd` hook.
 *
 * @since 2021-12-15
 */
if ( 'update' === ( $argv[ 1 ] ?? '' ) ) {
	new Post_Update_Cmd_Handler( [ 'update', '--project-dir', ${__FILE__}[ 'cwd' ] ] );
} else {
	new Post_Update_Cmd_Handler( [ 'symlink', '--project-dir', ${__FILE__}[ 'cwd' ] ] );
	U\CLI::run( [ $argv[ 0 ], 'update' ] ); // Separate process, after symlinks.
}

/**
 * Unsets `${__FILE__}`.
 *
 * @since 2021-12-15
 */
unset( ${__FILE__} );
