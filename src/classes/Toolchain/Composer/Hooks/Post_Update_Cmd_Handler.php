<?php
/** CLEVER CANYON™ <https://clevercanyon.com>
 *
 *  CCCCC  LL      EEEEEEE VV     VV EEEEEEE RRRRRR      CCCCC    AAA   NN   NN YY   YY  OOOOO  NN   NN ™
 * CC      LL      EE      VV     VV EE      RR   RR    CC       AAAAA  NNN  NN YY   YY OO   OO NNN  NN
 * CC      LL      EEEEE    VV   VV  EEEEE   RRRRRR     CC      AA   AA NN N NN  YYYYY  OO   OO NN N NN
 * CC      LL      EE        VV VV   EE      RR  RR     CC      AAAAAAA NN  NNN   YYY   OO   OO NN  NNN
 *  CCCCC  LLLLLLL EEEEEEE    VVV    EEEEEEE RR   RR     CCCCC  AA   AA NN   NN   YYY    OOOO0  NN   NN
 */
namespace Clever_Canyon\Utilities_Dev\Toolchain\Composer\Hooks;

/**
 * Dependencies.
 *
 * @since 1.0.0
 */
use Clever_Canyon\Utilities\OOPs\Version_1_0_0 as U;
use Clever_Canyon\Utilities_Dev\Toolchain\Common\{ Utilities as Common };
use Clever_Canyon\Utilities_Dev\Toolchain\Composer\{ Project, Utilities };
use Clever_Canyon\Utilities\OOP\Version_1_0_0\{ CLI_Tool_Base as Base };

/**
 * On `post-update-cmd` hook.
 *
 * @since 1.0.0
 */
class Post_Update_Cmd_Handler extends Base {
	/**
	 * Project.
	 *
	 * @since 1.0.0
	 */
	protected Project $project;

	/**
	 * Version.
	 */
	const VERSION = '1.0.0';

	/**
	 * Tool name.
	 */
	const NAME = 'Hook/Post_Update_Cmd_Handler';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct( 'update' );
		$this->add_commands( [ 'update' => [] ] );
		$this->route_request();
	}

	/**
	 * Command: `update`.
	 *
	 * @since 1.0.0
	 *
	 * @throws \Exception On any failure.
	 */
	protected function update() : void {
		if ( ! Utilities::is_dev_mode() ) {
			return; // Not applicable.
		}
		try {
			$this->project = new Project( getcwd() );
			$this->maybe_symlink_local_repos();
			$this->maybe_setup_dotfiles();
			U\CLI::exit_status( 0 );

		} catch ( \Throwable $exception ) {
			U\CLI::error( $exception->getMessage() );
			U\CLI::exit_status( 1 );
		}
	}

	/**
	 * Maybe symlink local repos.
	 *
	 * @since 1.0.0
	 *
	 * @throws \Exception Whenever any failure occurs.
	 */
	protected function maybe_symlink_local_repos() : void {
		if ( ! $this->project->has_dir( 'vendor' ) ) {
			return; // Not applicable.
		}
		foreach ( [
			'clevercanyon/php-standards',
			'clevercanyon/php-utilities',
			'clevercanyon/php-utilities-dev',
			'clevercanyon/wpgroove-framework',
			'clevercanyon/wpgroove-framework-dev',
		] as $_project_name ) {
			$_vendor_path = U\Fs::normalize( $this->project->dir . '/vendor/' . $_project_name );

			if ( ! U\Fs::path_exists( $_vendor_path ) ) {
				continue; // Not required by Composer config.
			}
			for ( $_i = 1; $_i <= 10; $_i++ ) { // Search 10 directories up.

				$_local_path = U\Fs::normalize( $this->project->dir . '/' . str_repeat( '../', $_i ) . $_project_name );

				if ( ! U\Fs::path_exists( $_local_path ) ) {
					continue; // Not far enough up yet?
				}
				if ( ! U\Fs::delete( $_vendor_path ) ) {
					throw new \Exception( 'Prior to symlink creation, failed to delete: `' . $_vendor_path . '`.' );
				}
				if ( ! symlink( $_local_path, $_vendor_path ) ) {
					throw new \Exception( 'Failed to symlink: `' . $_vendor_path . '`.' );
				}
				break; // We can stop here.
			}
		}
	}

	/**
	 * Maybe setup dotfiles.
	 *
	 * @since 1.0.0
	 *
	 * @throws \Exception Whenever any failure occurs.
	 */
	protected function maybe_setup_dotfiles() : void {
		// Applies to all projects.

		$dotfiles_path     = dirname( __FILE__, 5 ) . '/libraries/dotfiles';
		$dotfiles_iterator = U\Dir::iterator( dirname( __FILE__, 5 ) . '/libraries/dotfiles' );

		foreach( $dotfiles_iterator as $_resource ) {
			if ( ! $_resource->isFile() ) {
				continue; // Not applicable.
			}
			$_from_path = U\Fs::normalize( $_resource->getPathname() );
			$_subpath   = U\Fs::normalize( preg_replace( '/\.x$/ui', '', $_resource->getSubPathname() ) );
			$_to_path   = U\Fs::normalize( $this->project->dir . '/' . $_subpath );

			if ( ! is_file( $_from_path ) || ! is_readable( $_from_path ) ) {
				throw new \Exception( 'Unable to read dotfile: `' . $_from_path . '`.' );
			}
			if ( is_file( $_to_path ) && ( ! is_readable( $_to_path ) || ! is_writable( $_to_path ) ) ) {
				throw new \Exception( 'Unable to read and/or write existing dotfile: `' . $_to_path . '`.' );
			}
			switch( $_subpath ) {
				case 'package.json' : // If already exists, update `devDependencies` only.
					if ( is_file( $_to_path ) ) {
						$_from_path_json = json_decode( file_get_contents( $_from_path ) );
						$_to_path_json   = json_decode( file_get_contents( $_to_path ) );

						if ( ! is_object( $_from_path_json ) ) {
							throw new \Exception( 'Unable to parse JSON in: `' . $_from_path . '`.' );
						}
						if ( ! is_object( $_from_path_json->devDependencies ?? null ) ) {
							throw new \Exception( 'Unexpected `devDependencies` in: `' . $_from_path . '`.' );
						}
						if ( ! is_object( $_to_path_json ) ) {
							throw new \Exception( 'Unable to parse JSON in: `' . $_to_path . '`.' );
						}
						if ( isset( $_to_path_json->devDependencies ) && ! is_object( $_to_path_json->devDependencies ) ) {
							throw new \Exception( 'Unexpected `devDependencies` in: `' . $_to_path . '`.' );
						}
						foreach( $_from_path_json->devDependencies as $_package => $_version ) {
							$_to_path_json->devDependencies              ??= (object) [];
							$_to_path_json->devDependencies->{ $_package } = $_version;
						}
						if ( false === file_put_contents( $_to_path, json_encode( $_to_path_json, JSON_PRETTY_PRINT ) ) ) {
							throw new \Exception( 'Failed to update `devDependencies` in: `' . $_to_path . '`.' );
						}
						break;
					}
				default : // Everything falls through unless there's a `break` above.
					if ( ! U\Fs::copy( $_from_path, $_to_path ) ) {
						throw new \Exception( 'Failed to setup dotfile: `' . $_to_path . '`.' );
					}
			}
		}
	}
}
