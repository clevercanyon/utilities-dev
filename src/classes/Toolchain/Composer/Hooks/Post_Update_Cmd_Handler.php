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
 * Lint configuration.
 *
 * @since 2021-12-15
 *
 * phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 */

/**
 * Declarations & namespace.
 *
 * @since 2021-12-25
 */
declare( strict_types = 1 ); // ｡･:*:･ﾟ★.
namespace Clever_Canyon\Utilities_Dev\Toolchain\Composer\Hooks;

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

// </editor-fold>

/**
 * On `post-update-cmd` hook.
 *
 * @since 2021-12-15
 */
class Post_Update_Cmd_Handler extends \Clever_Canyon\Utilities\OOP\Abstracts\A6t_CLI_Tool {
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
	 *
	 * @param string|array|null $args_to_parse Optional custom args to parse instead of `$_SERVER['argv']`.
	 *                                         If not given, defaults internally to `$_SERVER['argv']`.
	 */
	public function __construct( /* string|array|null */ $args_to_parse = 'update' ) {
		parent::__construct( $args_to_parse );
		$this->add_commands( [
			'symlink' => [
				'callback'    => [ $this, 'symlink' ],
				'synopsis'    => 'Updates project symlinks.',
				'description' => 'Updates project symlinks. See ' . __CLASS__ . '::symlink()',
				'options'     => [
					'project-dir' => [
						'optional'    => true,
						'description' => 'Project directory path.',
						'validator'   => fn( $value ) => is_dir( $value ),
						'default'     => getcwd(),
					],
				],
			],
			'update'  => [
				'callback'    => [ $this, 'update' ],
				'synopsis'    => 'Updates project symlinks, dotfiles, and NPM packages.',
				'description' => 'Updates project symlinks, dotfiles, and NPM packages. See ' . __CLASS__ . '::update()',
				'options'     => [
					'project-dir' => [
						'optional'    => true,
						'description' => 'Project directory path.',
						'validator'   => fn( $value ) => is_dir( $value ),
						'default'     => getcwd(),
					],
				],
			],
		] );
		if ( U\Env::var( 'COMPOSER_DEV_MODE' ) ) {
			U\Env::config_debugging_mode();
			$this->route_request();
		}
	}

	/**
	 * Command: `symlink`.
	 *
	 * @since 2021-12-15
	 */
	protected function symlink() : void {
		try {
			$this->project = new Project( $this->get_option( 'project-dir' ) );
			$this->maybe_symlink_local_repos();

		} catch ( \Throwable $throwable ) {
			U\CLI::error( $throwable->getMessage() );
			U\CLI::error( $throwable->getTraceAsString() );
			U\CLI::exit_status( 1 );
		}
	}

	/**
	 * Command: `update`.
	 *
	 * @since 2021-12-15
	 */
	protected function update() : void {
		try {
			$this->project = new Project( $this->get_option( 'project-dir' ) );
			$this->maybe_setup_dotfiles();
			$this->maybe_run_npm_update();

		} catch ( \Throwable $throwable ) {
			U\CLI::error( $throwable->getMessage() );
			U\CLI::error( $throwable->getTraceAsString() );
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
		U\CLI::log( ': ' . __FUNCTION__ . '()' );

		$symlink_local_packages_prop = '&.post_update_cmd_handler.symlink_local_packages';
		$symlink_local_packages      = U\Obj::get_prop( $this->project->json->extra, $symlink_local_packages_prop );

		if ( null === $symlink_local_packages ) {
			return; // Nothing to do here.
		} elseif ( ! is_object( $symlink_local_packages ) ) {
			throw new Exception(
				'Unexpected extra prop: `' . $symlink_local_packages_prop . '` in: `' . $this->project->file . '`.' .
				' Must be an object with props matching pattern: `' . T\Composer::PACKAGES_DIR_REGEXP . '`.'
			);
		}
		foreach ( $symlink_local_packages as $_packages_dir => $_package_names ) {
			if (
				! $_packages_dir
				|| ! is_string( $_packages_dir )
				|| ! preg_match( T\Composer::PACKAGES_DIR_REGEXP, $_packages_dir )
			) {
				throw new Exception(
					'Unexpected extra prop: `' . $symlink_local_packages_prop . '` in: `' . $this->project->file . '`.' .
					' Each packages directory must match pattern: `' . T\Composer::PACKAGES_DIR_REGEXP . '`.'
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
					case 'node_modules':
						if ( ! $_package_name
							|| ! is_string( $_package_name )
							|| ! preg_match( T\NPM::PACKAGE_NAME_REGEXP, $_package_name )
							|| strlen( $_package_name ) > T\NPM::PACKAGE_NAME_MAX_BYTES
						) {
							throw new Exception(
								'Unexpected extra prop: `' . $symlink_local_packages_prop . '` with package name: `' . $_package_name . '`' .
								' in: `' . $this->project->file . '`. Package name must match pattern: `' . T\NPM::PACKAGE_NAME_REGEXP . '`' .
								' and be <= `' . T\NPM::PACKAGE_NAME_MAX_BYTES . '` bytes in length.'
							);
						}
						break;
					case 'vendor':
						if ( ! $_package_name
							|| ! is_string( $_package_name )
							|| ! preg_match( T\Composer::PACKAGE_NAME_REGEXP, $_package_name )
							|| strlen( $_package_name ) > T\Composer::PACKAGE_NAME_MAX_BYTES
						) {
							throw new Exception(
								'Unexpected extra prop: `' . $symlink_local_packages_prop . '` with package name: `' . $_package_name . '`' .
								' in: `' . $this->project->file . '`. Package name must match pattern: `' . T\Composer::PACKAGE_NAME_REGEXP . '`' .
								' and be <= `' . T\Composer::PACKAGE_NAME_MAX_BYTES . '` bytes in length.'
							);
						}
						break;
					default:
						throw new Exception(
							'Unexpected extra prop: `' . $symlink_local_packages_prop . '` in: `' . $this->project->file . '`.' .
							' Unable to properly validate package names for directory: `' . $_packages_dir . '`.'
						);
				}
				$_package_dir = U\Dir::join( $this->project->dir, '/' . $_packages_dir . '/' . $_package_name );

				if ( ! is_dir( $_package_dir ) ) {
					continue; // Not even the package path is available.
				}
				for ( $_i = 1; $_i <= 10; $_i++ ) { // Search 10 directories up.
					$_local_repo_dir = U\Dir::join( $this->project->dir, '/../' . str_repeat( '../', $_i ) . $_package_name );

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
		U\CLI::log( ': ' . __FUNCTION__ . '()' );

		$dotfiles_dir  = U\Dir::name( __FILE__, 5, '/libraries/dotfiles' );
		$dotfiles_file = U\Dir::join( $dotfiles_dir, '/.dotfiles.json' );

		if ( ! is_dir( $dotfiles_dir ) || ! is_readable( $dotfiles_dir ) ) {
			throw new Exception( 'Missing readable directory: `' . $dotfiles_dir . '`.' );
		}
		if ( ! is_file( $dotfiles_file ) || ! is_readable( $dotfiles_file ) ) {
			throw new Exception( 'Missing readable file: `' . $dotfiles_file . '`.' );
		}
		$dotfiles_iterator = U\Dir::iterator( $dotfiles_dir );
		$dotfiles_json     = U\Str::json_decode( file_get_contents( $dotfiles_file ) );

		if ( ! is_object( $dotfiles_json ) || ! is_array( $dotfiles_json->manifest ?? null ) ) {
			throw new Exception( 'Failed to parse `manifest` in `' . $dotfiles_json . '`.' );
		}
		foreach ( $dotfiles_iterator as $_resource ) {
			if ( ! $_resource->isFile() ) {
				continue; // Not applicable.
			}
			$_from_path    = U\Fs::normalize( $_resource->getPathname() );
			$_from_subpath = U\Fs::normalize( $_resource->getSubPathname() );
			$_to_path      = U\Dir::join( $this->project->dir, '/' . $_from_subpath );

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
				case 'package.json': // If exists, update. Do NOT overwrite.
					if ( is_file( $_to_path ) ) {
						// Parse JSON objects.

						$_from_path_json = U\Str::json_decode( file_get_contents( $_from_path ) );
						$_to_path_json   = U\Str::json_decode( file_get_contents( $_to_path ) );

						// Validate `$_from_path_json`.

						if ( ! is_object( $_from_path_json ) ) {
							throw new Exception( 'Unable to parse JSON in: `' . $_from_path . '`.' );
						}
						$_from_path_json->devDependencies      ??= (object) [];
						$_from_path_json->config               ??= (object) [];
						$_from_path_json->config->clevercanyon ??= (object) [];

						if ( ! is_object( $_from_path_json->devDependencies ) ) {
							throw new Exception( 'Unexpected `devDependencies` in: `' . $_from_path . '`.' );
						}
						if ( ! is_object( $_from_path_json->config ) ) {
							throw new Exception( 'Unexpected `config` in: `' . $_from_path . '`.' );
						}
						if ( ! is_object( $_from_path_json->config->clevercanyon ) ) {
							throw new Exception( 'Unexpected `config->clevercanyon` in: `' . $_from_path . '`.' );
						}
						// Validate `$_to_path_json`.

						if ( ! is_object( $_to_path_json ) ) {
							throw new Exception( 'Unable to parse JSON in: `' . $_to_path . '`.' );
						}
						$_to_path_json->devDependencies      ??= (object) [];
						$_to_path_json->config               ??= (object) [];
						$_to_path_json->config->clevercanyon ??= (object) [];

						if ( ! is_object( $_to_path_json->devDependencies ) ) {
							throw new Exception( 'Unexpected `devDependencies` in: `' . $_to_path . '`.' );
						}
						if ( ! is_object( $_to_path_json->config ) ) {
							throw new Exception( 'Unexpected `config` in: `' . $_to_path . '`.' );
						}
						if ( ! is_object( $_to_path_json->config->clevercanyon ) ) {
							throw new Exception( 'Unexpected `config->clevercanyon` in: `' . $_to_path . '`.' );
						}
						// Update `$_to_path_json`.

						foreach ( $_from_path_json->devDependencies as $_package => $_version ) {
							if ( '@' . $this->project->name !== $_package ) {
								$_to_path_json->devDependencies->{$_package} = $_version;
							} // Package should NOT depend on itself ^.
						}
						$_to_path_json->devDependencies      = U\Obj::sort_by( 'prop', $_to_path_json->devDependencies );
						$_to_path_json->config->clevercanyon = U\Ctn::merge( $_to_path_json->config->clevercanyon, $_from_path_json->config->clevercanyon );

						if ( false === file_put_contents( $_to_path, U\Str::json_encode( $_to_path_json, true ) ) ) {
							throw new Exception( 'Failed to update `devDependencies` in: `' . $_to_path . '`.' );
						}
						break;
					} // PLEASE NOTE THE FALLTHROUGH FROM ABOVE!
				default: // Everything falls through unless there's a `break` above.
					if ( ! U\Fs::copy( $_from_path, $_to_path ) ) {
						throw new Exception( 'Failed to setup dotfile: `' . $_to_path . '`.' );
					}
			}
		}
	}

	/**
	 * Maybe run NPM updates.
	 *
	 * @since 2021-12-15
	 */
	protected function maybe_run_npm_update() : void {
		U\CLI::log( ': ' . __FUNCTION__ . '()' );

		if ( $this->project->has_file( 'package.json' ) ) {
			U\CLI::run( [ 'npm', 'update' ], $this->project->dir );
		}
	}
}
