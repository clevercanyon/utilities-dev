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
 * @since 2021-12-15
 */
use Clever_Canyon\Utilities\OOPs\Version_1_0_0 as U;
use Clever_Canyon\Utilities\OOP\Version_1_0_0\Exception;

use Clever_Canyon\Utilities_Dev\Toolchain\Common\{Utilities as Common};
use Clever_Canyon\Utilities_Dev\Toolchain\Composer\{Project, Utilities};
use Clever_Canyon\Utilities\OOP\Version_1_0_0\{CLI_Tool_Base as Base};

/**
 * On `post-update-cmd` hook.
 *
 * @since 2021-12-15
 */
class Post_Update_Cmd_Handler extends Base {
	/**
	 * Project.
	 *
	 * @since 2021-12-15
	 */
	protected Project $project;

	/**
	 * Version.
	 */
	protected const VERSION = '1.0.0';

	/**
	 * Tool name.
	 */
	protected const NAME = 'Hook/Post_Update_Cmd_Handler';

	/**
	 * Constructor.
	 *
	 * @since 2021-12-15
	 */
	public function __construct() {
		parent::__construct( 'update' );

		$this->add_commands( [ 'update' => [] ] );
		$this->route_request();
	}

	/**
	 * Command: `update`.
	 *
	 * @since 2021-12-15
	 *
	 * @throws Exception On any failure.
	 */
	protected function update() : void {
		if ( ! getenv( 'COMPOSER_DEV_MODE' ) ) {
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
	 * @since 2021-12-15
	 *
	 * @throws Exception On any failure.
	 */
	protected function maybe_symlink_local_repos() : void {
		$symlink_local_packages_prop = '&.post_update_cmd_handler.symlink_local_packages';
		$symlink_local_packages      = U\Obj::get_prop( $this->project->json->extra, $symlink_local_packages_prop );

		if ( null === $symlink_local_packages ) {
			return; // Nothing to do here.
		} elseif ( ! is_object( $symlink_local_packages ) ) {
			throw new Exception(
				'Unexpected extra prop: `' . $symlink_local_packages_prop . '` in: `' . $this->project->file . '`.' .
				' Must be an object with props matching pattern: `' . Common::PACKAGES_DIR_REGEXP . '`.'
			);
		}
		foreach ( $symlink_local_packages as $_packages_dir => $_package_names ) {
			if (
				! $_packages_dir
				|| ! is_string( $_packages_dir )
				|| ! preg_match( Common::PACKAGES_DIR_REGEXP, $_packages_dir )
			) {
				throw new Exception(
					'Unexpected extra prop: `' . $symlink_local_packages_prop . '` in: `' . $this->project->file . '`.' .
					' Each packages directory must match pattern: `' . Common::PACKAGES_DIR_REGEXP . '`.'
				);
			}
			if ( ! is_array( $_package_names ) ) {
				throw new Exception(
					'Unexpected extra prop: `' . $symlink_local_packages_prop . '` in: `' . $this->project->file . '`.' .
					' Package names must be an array, each matching a pattern appropriate for a given packages directory.'
				);
			}
			foreach ( $_package_names as $_package_name ) {
				switch ( $_packages_dir ) {
					case 'node_modules' :
						if ( ! $_package_name
							|| ! is_string( $_package_name )
							|| ! preg_match( Common::NPM_PACKAGE_NAME_REGEXP, $_package_name )
							|| strlen( $_package_name ) > Common::NPM_PACKAGE_NAME_MAX_BYTES
						) {
							throw new Exception(
								'Unexpected extra prop: `' . $symlink_local_packages_prop . '` with package name: `' . $_package_name . '`' .
								' in: `' . $this->project->file . '`. Package name must match pattern: `' . Common::NPM_PACKAGE_NAME_REGEXP . '`' .
								' and be <= `' . Common::NPM_PACKAGE_NAME_MAX_BYTES . '` bytes in length.'
							);
						}
						break;
					case 'vendor':
						if ( ! $_package_name
							|| ! is_string( $_package_name )
							|| ! preg_match( Common::COMPOSER_PACKAGE_NAME_REGEXP, $_package_name )
							|| strlen( $_package_name ) > Common::COMPOSER_PACKAGE_NAME_MAX_BYTES
						) {
							throw new Exception(
								'Unexpected extra prop: `' . $symlink_local_packages_prop . '` with package name: `' . $_package_name . '`' .
								' in: `' . $this->project->file . '`. Package name must match pattern: `' . Common::COMPOSER_PACKAGE_NAME_REGEXP . '`' .
								' and be <= `' . Common::COMPOSER_PACKAGE_NAME_MAX_BYTES . '` bytes in length.'
							);
						}
						break;
					default:
						throw new Exception(
							'Unexpected extra prop: `' . $symlink_local_packages_prop . '` in: `' . $this->project->file . '`.' .
							' Unable to properly validate package names for directory: `' . $_packages_dir . '`.'
						);
				}
				$_package_dir = U\Fs::normalize( $this->project->dir . '/' . $_packages_dir . '/' . $_package_name );

				if ( ! is_dir( $_package_dir ) ) {
					continue; // Not even the package path is available.
				}
				for ( $_i = 1; $_i <= 10; $_i++ ) { // Search 10 directories up.
					$_local_repo_dir = U\Fs::normalize( $this->project->dir . '/../' . str_repeat( '../', $_i ) . $_package_name );

					if ( ! is_dir( $_local_repo_dir ) ) {
						continue; // Not far enough up yet?
					}
					if ( ! U\Fs::delete( $_package_dir ) ) {
						throw new Exception( 'Prior to symlink creation, failed to delete: `' . $_package_dir . '`.' );
					}
					if ( ! symlink( $_local_repo_dir, $_package_dir ) ) {
						throw new Exception( 'Failed to symlink: `' . $_package_dir . '`.' );
					}
					break; // We can stop this loop.
				}
			}
		}
	}

	/**
	 * Maybe setup dotfiles.
	 *
	 * @since 2021-12-15
	 *
	 * @throws Exception On any failure.
	 */
	protected function maybe_setup_dotfiles() : void {
		$dotfiles_dir  = U\Fs::normalize( dirname( __FILE__, 5 ) . '/libraries/dotfiles' );
		$dotfiles_file = rtrim( $dotfiles_dir, '/' ) . '/.dotfiles.json';

		if ( ! is_dir( $dotfiles_dir ) || ! is_readable( $dotfiles_dir ) ) {
			throw new Exception( 'Missing readable directory: `' . $dotfiles_dir . '`.' );
		}
		if ( ! is_file( $dotfiles_file ) || ! is_readable( $dotfiles_file ) ) {
			throw new Exception( 'Missing readable file: `' . $dotfiles_file . '`.' );
		}
		$dotfiles_iterator = U\Dir::iterator( $dotfiles_dir );
		$dotfiles_json     = json_decode( file_get_contents( $dotfiles_file ) );

		if ( ! is_object( $dotfiles_json ) || ! is_array( $dotfiles_json->manifest ?? null ) ) {
			throw new Exception( 'Failed to parse `manifest` in `' . $dotfiles_json . '`.' );
		}
		foreach ( $dotfiles_iterator as $_resource ) {
			if ( ! $_resource->isFile() ) {
				continue; // Not applicable.
			}
			$_from_path    = U\Fs::normalize( $_resource->getPathname() );
			$_from_subpath = U\Fs::normalize( $_resource->getSubPathname() );
			$_to_path      = U\Fs::normalize( $this->project->dir . '/' . $_from_subpath );

			if ( ! in_array( $_from_subpath, $dotfiles_json->manifest, true ) ) {
				continue; // Not in the manifest; ignore.
			}
			if ( ! is_file( $_from_path ) || ! is_readable( $_from_path ) ) {
				throw new Exception( 'Unable to read dotfile: `' . $_from_path . '`.' );
			}
			if ( is_file( $_to_path ) && ( ! is_readable( $_to_path ) || ! is_writable( $_to_path ) ) ) {
				throw new Exception( 'Unable to update existing dotfile: `' . $_to_path . '`.' );
			}
			switch ( $_from_subpath ) {
				case 'package.json' : // If already exists, update `devDependencies` only.
					if ( is_file( $_to_path ) ) {
						$_from_path_json = json_decode( file_get_contents( $_from_path ) );
						$_to_path_json   = json_decode( file_get_contents( $_to_path ) );

						if ( ! is_object( $_from_path_json ) ) {
							throw new Exception( 'Unable to parse JSON in: `' . $_from_path . '`.' );
						}
						if ( ! is_object( $_from_path_json->devDependencies ?? null ) ) {
							throw new Exception( 'Unexpected `devDependencies` in: `' . $_from_path . '`.' );
						}
						if ( ! is_object( $_to_path_json ) ) {
							throw new Exception( 'Unable to parse JSON in: `' . $_to_path . '`.' );
						}
						if ( isset( $_to_path_json->devDependencies ) && ! is_object( $_to_path_json->devDependencies ) ) {
							throw new Exception( 'Unexpected `devDependencies` in: `' . $_to_path . '`.' );
						}
						foreach ( $_from_path_json->devDependencies as $_package => $_version ) {
							$_to_path_json->devDependencies ??= (object) [];

							if ( $this->project->name !== $_package && '@' . $this->project->name !== $_package ) {
								$_to_path_json->devDependencies->{$_package} = $_version;
							} // ^ Don't add a circular dependency on itself!
						}
						if ( isset( $_to_path_json->devDependencies ) ) {
							$_to_path_json->devDependencies = U\Obj::sort_by( 'prop', $_to_path_json->devDependencies );
						}
						if ( false === file_put_contents( $_to_path, json_encode( $_to_path_json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) ) ) {
							throw new Exception( 'Failed to update `devDependencies` in: `' . $_to_path . '`.' );
						}
						break;
					}
				default : // Everything falls through unless there's a `break` above.
					if ( ! U\Fs::copy( $_from_path, $_to_path ) ) {
						throw new Exception( 'Failed to setup dotfile: `' . $_to_path . '`.' );
					}
			}
		}
	}
}
