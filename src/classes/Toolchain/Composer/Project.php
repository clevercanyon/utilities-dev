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

// </editor-fold>

/**
 * Project.
 *
 * @since 2021-12-15
 *
 * @property-read $dir
 * @property-read $file
 *
 * @property-read $json
 * @property-read $dev_json
 *
 * @property-read $name
 * @property-read $name_hash
 *
 * @property-read $brand_slug
 * @property-read $brand_var
 */
class Project extends \Clever_Canyon\Utilities\OOP\Abstracts\A6t_Base {
	/**
	 * OOP traits.
	 *
	 * @since 2021-12-15
	 */
	use \Clever_Canyon\Utilities\OOP\Traits\I7e_Base\Magic\Readable_Members;

	/**
	 * Directory.
	 *
	 * @since 2021-12-15
	 */
	protected string $dir;

	/**
	 * `composer.json` file.
	 *
	 * @since 2021-12-15
	 */
	protected string $file;

	/**
	 * JSON props.
	 *
	 * @since 2021-12-15
	 */
	protected \stdClass $json;

	/**
	 * Dev.json props.
	 *
	 * @since 2021-12-15
	 */
	protected \stdClass $dev_json;

	/**
	 * Name.
	 *
	 * @since 2021-12-15
	 */
	protected string $name;

	/**
	 * Name hash.
	 *
	 * @since 2021-12-15
	 */
	protected string $name_hash;

	/**
	 * Brand slug.
	 *
	 * @since 2021-12-15
	 */
	protected string $brand_slug;

	/**
	 * Brand var.
	 *
	 * @since 2021-12-15
	 */
	protected string $brand_var;

	/**
	 * Constructor.
	 *
	 * @since 2021-12-15
	 *
	 * @param string $dir Directory.
	 *
	 * @throws Exception On any failure.
	 */
	public function __construct( string $dir ) {
		parent::__construct();

		// Validate directory.

		$this->dir  = U\Fs::normalize( $dir );
		$this->file = U\Dir::join( $this->dir, '/composer.json' );

		if ( ! $this->dir ) {
			throw new Exception( 'Missing `Project->dir`.' );
		}
		if ( ! is_file( $this->file ) ) {
			throw new Exception( 'Missing `Project->file`.' );
		}
		// Validate JSON data.

		$this->dev_json = T\Dev::json( null, 'clevercanyon' );
		$this->json     = T\Composer::json( $this->dir, 'clevercanyon' );

		if ( ! isset( $this->json->name, $this->json->extra ) ) {
			throw new Exception( 'Missing or extremely incomplete `Project->json` file.' );
		}
		// Validate name property.

		$this->name      = strval( $this->json->name );
		$this->name_hash = 'X' . mb_strtoupper( mb_substr( md5( mb_strtolower( $this->name ) ), 0, 15 ) );

		if ( ! $this->name || ! preg_match( T\Composer::PACKAGE_NAME_REGEXP, $this->name ) ) {
			throw new Exception( 'Missing or invalid characters in `Project->name`. Must match: `' . T\Composer::PACKAGE_NAME_REGEXP . '`.' );
		}
		// Validate brand properties.

		$this->brand_slug = strval( U\Obj::get_prop( $this->json->extra, '&.brand.data.slug' ) );
		$this->brand_var  = str_replace( '-', '_', $this->brand_slug );

		if ( ! $this->brand_slug || ! $this->brand_var ) {
			throw new Exception( 'Missing `Project->brand_slug|brand_var`.' );
		}
		// Validate WordPress plugin/theme data.

		if ( $this->is_wp_plugin() && ! $this->wp_plugin_data() ) {
			throw new Exception( 'Missing or incomplete `Project->wp_plugin_data()`.' );

		} elseif ( $this->is_wp_theme() && ! $this->wp_theme_data() ) {
			throw new Exception( 'Missing or incomplete `Project->wp_theme_data()`.' );
		}
	}

	/**
	 * Has directory?
	 *
	 * @since 2021-12-15
	 *
	 * @param string $subpath Directory subpath.
	 *
	 * @return bool True if has directory.
	 */
	public function has_dir( string $subpath ) : bool {
		return is_dir( U\Dir::join( $this->dir, '/' . $subpath ) );
	}

	/**
	 * Has file?
	 *
	 * @since 2021-12-15
	 *
	 * @param string $subpath File subpath.
	 *
	 * @return bool True if has file.
	 */
	public function has_file( string $subpath ) : bool {
		return is_file( U\Dir::join( $this->dir, '/' . $subpath ) );
	}

	/**
	 * WordPress project?
	 *
	 * @since 2021-12-15
	 *
	 * @return bool True if WordPress project.
	 */
	public function is_wp_project() : bool {
		return $this->is_wp_plugin() || $this->is_wp_theme();
	}

	/**
	 * WordPress plugin?
	 *
	 * @since 2021-12-15
	 *
	 * @return bool True if WordPress plugin.
	 */
	public function is_wp_plugin() : bool {
		return $this->has_file( 'trunk/plugin.php' );
	}

	/**
	 * WordPress theme?
	 *
	 * @since 2021-12-15
	 *
	 * @return bool True if WordPress theme.
	 */
	public function is_wp_theme() : bool {
		return $this->has_file( 'trunk/theme.php' );
	}

	/**
	 * Gets WordPress plugin data.
	 *
	 * @since                 1.0.0
	 *
	 * @throws Exception On any failure.
	 * @return \stdClass|false Plugin data.
	 *
	 * @see                   \WP_Groove\Framework\Plugin\Abstracts\AA6t_Plugin::__construct()
	 */
	public function wp_plugin_data() /* : \stdClass|false */ {
		if ( null !== ( $cache = &$this->oop_cache( __FUNCTION__ ) ) ) {
			return $cache; // Cached already.
		}
		if ( ! $this->is_wp_plugin() ) {
			return $cache = false; // Not possible.
		}
		$data = (object) [];

		$data->file        = U\Dir::join( $this->dir, '/trunk/plugin.php' );
		$data->readme_file = U\Dir::join( $this->dir, '/trunk/readme.txt' );
		$data->dir         = U\Dir::name( $data->file );

		if ( ! is_dir( $data->dir )
			|| ! is_readable( $data->dir )

			|| ! is_file( $data->file )
			|| ! is_readable( $data->file )

			|| ! is_file( $data->readme_file )
			|| ! is_readable( $data->readme_file )
		) {
			throw new Exception(
				'Missing or unreadable file in `' . $data->dir . '`.' .
				' Must have `plugin.php` and `readme.txt`.'
			);
		}
		if ( count( $_p = explode( '/', $data->file ) ) < 3 ) {
			throw new Exception( 'Unexpected plugin file path: `' . $data->file . '`.' );
		}
		$data->basename = $_p[ count( $_p ) - 3 ] . '/' . $_p[ count( $_p ) - 2 ];

		$data->slug = basename( U\Dir::name( $data->basename ) );
		$data->var  = str_replace( '-', '_', $data->slug );

		$data->brand_slug = &$this->brand_slug;
		$data->brand_var  = &$this->brand_var;

		$data->unbranded_slug = preg_replace( '/^' . U\Str::esc_reg( $data->brand_slug . '-' ) . '/ui', '', $data->slug );
		$data->unbranded_var  = str_replace( '-', '_', $data->unbranded_slug );

		$data->headers = $this->wp_plugin_file_headers();

		return $cache = $data;
	}

	/**
	 * Gets WordPress plugin file headers.
	 *
	 * @since                 1.0.0
	 *
	 * @throws Exception On any failure.
	 * @return \stdClass|null Plugin file headers.
	 *
	 * @see                   https://developer.wordpress.org/reference/functions/get_plugin_data/
	 */
	protected function wp_plugin_file_headers() /* : \stdClass|null */ : ?\stdClass {
		if ( ! $this->is_wp_plugin() ) {
			return null; // Not possible.
		}
		$data = (object) [
			'_map' => [
				'name' => 'Plugin Name',
				'url'  => 'Plugin URI',

				'description' => 'Description',
				'tags'        => 'Tags',

				'version'    => 'Version',
				'stable_tag' => 'Stable tag', // Custom addition.
				// ^ This header is not part of {@see \get_plugin_data()}, but here for consistency.

				'author'       => 'Author',
				'author_url'   => 'Author URI',
				'donate_url'   => 'Donate link',
				'contributors' => 'Contributors', // Custom addition.
				// ^ This header is not part of {@see \get_plugin_data()}, but here for consistency.

				'license'     => 'License',     // Custom addition.
				'license_url' => 'License URI', // Custom addition.
				// ^ These headers are not part of {@see \get_plugin_data()}, but here for consistency.

				'text_domain' => 'Text Domain',
				'domain_path' => 'Domain Path',

				'network' => 'Network',

				'requires_php_version'    => 'Requires PHP',
				'requires_wp_version'     => 'Requires at least',
				'tested_up_to_wp_version' => 'Tested up to', // Custom addition.
				// ^ This header is not part of {@see \get_plugin_data()}, but here for consistency.

				'update_url' => 'Update URI',
			],
		];
		$file = U\Dir::join( $this->dir, '/trunk/plugin.php' );

		$first_8kbs = file_get_contents( $file, false, null, 0, 8192 );
		$first_8kbs = str_replace( "\r", "\n", $first_8kbs );

		foreach ( $data->_map as $_prop => $_header ) {
			if ( preg_match( '/^(?:[ \t]*\<\?php)?[ \t\/*#@]*' . U\Str::esc_reg( $_header ) . '\:(.*)$/mi', $first_8kbs, $_m ) && $_m[ 1 ] ) {
				$data->{$_prop} = trim( preg_replace( '/\s*(?:\*\/|\?\>).*/', '', $_m[ 1 ] ) );
			} else {
				$data->{$_prop} = '';
			}
			if ( ! $data->{$_prop} && ! in_array( $_prop, [ 'network', 'update_url' ], true ) ) {
				throw new Exception( 'Missing `' . $_header . '` in plugin file headers.' );
			}
		}
		return $data;
	}

	/**
	 * Gets a theme's data.
	 *
	 * @since                 1.0.0
	 *
	 * @throws Exception On any failure.
	 * @return \stdClass|false Theme data.
	 *
	 * @see                   \WP_Groove\Framework\Theme\Abstracts\AA6t_Theme::__construct()
	 */
	public function wp_theme_data() /* : \stdClass|false */ {
		if ( null !== ( $cache = &$this->oop_cache( __FUNCTION__ ) ) ) {
			return $cache; // Cached already.
		}
		if ( ! $this->is_wp_theme() ) {
			return $cache = false; // Not possible.
		}
		$data = (object) [];

		$data->file           = U\Dir::join( $this->dir, '/trunk/theme.php' );
		$data->functions_file = U\Dir::join( $this->dir, '/trunk/functions.php' );
		$data->style_file     = U\Dir::join( $this->dir, '/trunk/style.css' );
		$data->readme_file    = U\Dir::join( $this->dir, '/trunk/readme.txt' );
		$data->dir            = U\Dir::name( $data->file );

		if ( ! is_dir( $data->dir )
			|| ! is_readable( $data->dir )

			|| ! is_file( $data->file )
			|| ! is_readable( $data->file )

			|| ! is_file( $data->functions_file )
			|| ! is_readable( $data->functions_file )

			|| ! is_file( $data->style_file )
			|| ! is_readable( $data->style_file )

			|| ! is_file( $data->readme_file )
			|| ! is_readable( $data->readme_file )
		) {
			throw new Exception(
				'Missing or unreadable file in `' . $data->dir . '`.' .
				' Must have `theme.php`, `functions.php`, `style.css`, and `readme.txt`.'
			);
		}
		if ( count( $_p = explode( '/', $data->file ) ) < 3 ) {
			throw new Exception( 'Unexpected theme file path: `' . $data->file . '`.' );
		}
		$data->basename = $_p[ count( $_p ) - 3 ] . '/' . $_p[ count( $_p ) - 2 ];

		$data->slug = basename( U\Dir::name( $data->basename ) );
		$data->var  = str_replace( '-', '_', $data->slug );

		$data->brand_slug = &$this->brand_slug;
		$data->brand_var  = &$this->brand_var;

		$data->unbranded_slug = preg_replace( '/^' . U\Str::esc_reg( $data->brand_slug . '-' ) . '/ui', '', $data->slug );
		$data->unbranded_var  = str_replace( '-', '_', $data->unbranded_slug );

		$data->headers = $this->wp_theme_file_headers();

		return $cache = $data;
	}

	/**
	 * Gets theme file headers.
	 *
	 * @since                 1.0.0
	 *
	 * @throws Exception On any failure.
	 * @return \stdClass|null Theme file headers.
	 *
	 * @see                   https://developer.wordpress.org/reference/classes/wp_theme/
	 */
	protected function wp_theme_file_headers() /* : \stdClass|null */ : ?\stdClass {
		if ( ! $this->is_wp_theme() ) {
			return null; // Not possible.
		}
		$data = (object) [
			'_map' => [
				'name' => 'Theme Name',
				'url'  => 'Theme URI',

				'description' => 'Description',
				'tags'        => 'Tags',

				'template' => 'Template',

				'version'    => 'Version',
				'stable_tag' => 'Stable tag', // Custom addition.
				// ^ This header is not part of {@see \WP_Theme}, but here for consistency.
				'status'     => 'Status',     // Deprecated? Defaults to `publish` in core <https://git.io/JMwZo>.

				'author'       => 'Author',
				'author_url'   => 'Author URI',
				'donate_url'   => 'Donate link',  // Custom addition.
				// ^ This header is not part of {@see \WP_Theme}, but here for consistency.
				'contributors' => 'Contributors', // Custom addition.
				// ^ This header is not part of {@see \WP_Theme}, but here for consistency.

				'license'     => 'License',     // Custom addition.
				'license_url' => 'License URI', // Custom addition.
				// ^ These headers are not part of {@see \WP_Theme}, but here for consistency.

				'text_domain' => 'Text Domain',
				'domain_path' => 'Domain Path',

				'requires_php_version'    => 'Requires PHP',
				'requires_wp_version'     => 'Requires at least',
				'tested_up_to_wp_version' => 'Tested up to', // Custom addition.
				// ^ This header is not part of {@see \WP_Theme}, but here for consistency.

				'update_url' => 'Update URI', // Custom addition.
				// ^ This header is not part of {@see \WP_Theme}, but here for consistency.
			],
		];
		$file = U\Dir::join( $this->dir, '/trunk/theme.php' );

		$first_8kbs = file_get_contents( $file, false, null, 0, 8192 );
		$first_8kbs = str_replace( "\r", "\n", $first_8kbs );

		foreach ( $data->_map as $_prop => $_header ) {
			if ( preg_match( '/^(?:[ \t]*\<\?php)?[ \t\/*#@]*' . U\Str::esc_reg( $_header ) . '\:(.*)$/mi', $first_8kbs, $_m ) && $_m[ 1 ] ) {
				$data->{$_prop} = trim( preg_replace( '/\s*(?:\*\/|\?\>).*/', '', $_m[ 1 ] ) );
			} else {
				$data->{$_prop} = '';
			}
			if ( ! $data->{$_prop} && ! in_array( $_prop, [ 'template', 'status', 'update_url' ], true ) ) {
				throw new Exception( 'Missing `' . $_header . '` in theme file headers.' );
			}
		}
		return $data;
	}

	/**
	 * Gets s3 bucket name.
	 *
	 * @since 2021-12-15
	 *
	 * @throws Exception On any failure.
	 * @return string AWS S3 bucket name.
	 */
	public function s3_bucket() : string {
		$bucket_prop = '&.brand.aws.s3.bucket';

		if ( ! $bucket = strval( U\Obj::get_prop( $this->json->extra, $bucket_prop ) ) ) {
			throw new Exception( 'Missing extra prop: `' . $bucket_prop . '` in: `' . $this->file . '`.' );
		}
		return $bucket;
	}

	/**
	 * Gets s3 bucket config.
	 *
	 * @since 2021-12-15
	 *
	 * @throws Exception On any failure.
	 * @return array Bucket config suitable for {@see \Aws\S3\S3Client}.
	 */
	public function s3_bucket_config() : array {
		$access_key_prop = $this->brand_var . '.aws.credentials.access_key';
		$access_key      = U\Obj::get_prop( $this->dev_json, $access_key_prop );

		$secret_key_prop = $this->brand_var . '.aws.credentials.secret_key';
		$secret_key      = U\Obj::get_prop( $this->dev_json, $secret_key_prop );

		if ( ! $access_key || ! $secret_key ) {
			throw new Exception(
				'Missing prop: `' . $access_key_prop . '` and/or `' . $secret_key_prop . '` in: `~/.dev.json`.' .
				' Please contact support for help with AWS access. ' .
				' We’ll also help you set up `~/.dev.json`.'
			);
		}
		return [
			'version'           => '2006-03-01',
			'region'            => 'us-east-1',
			'signature_version' => 'v4',
			'credentials'       => [
				'key'    => $access_key,
				'secret' => $secret_key,
			],
		];
	}

	/**
	 * Gets an HMAC SHA256 keyed hash.
	 *
	 * @param string $string String to hash.
	 *
	 * @throws Exception On any failure.
	 * @return string HMAC SHA256 keyed hash. 64 bytes in length.
	 */
	public function s3_hash_hmac_sha256( string $string ) : string {
		$hash_hmac_key_prop = $this->brand_var . '.aws.s3.hash_hmac_key';
		$hash_hmac_key      = U\Obj::get_prop( $this->dev_json, $hash_hmac_key_prop );

		if ( ! $hash_hmac_key ) {
			throw new Exception( 'Missing prop: `' . $hash_hmac_key_prop . '` in: `~/.dev.json`.' );
		}
		return hash_hmac( 'sha256', $string, $hash_hmac_key );
	}

	/**
	 * Gets local WordPress public HTML directory.
	 *
	 * @since 2021-12-15
	 *
	 * @return string Local WordPress public HTML directory,
	 *                else empty string if not available in `~/.dev.json`.
	 */
	public function local_wp_public_html_dir() : string {
		$public_html_dir_prop = '&.local.wordpress.public_html_dir';
		$public_html_dir      = U\Obj::get_prop( $this->dev_json, $public_html_dir_prop );
		$public_html_dir      = U\Fs::normalize( $public_html_dir ?: '' );

		if ( ! $public_html_dir || ! is_dir( $public_html_dir ) ) {
			return ''; // Let this pass, as it's not vital to our needs right now.
		}
		return $public_html_dir;
	}

	/**
	 * Gets local WordPress versions.
	 *
	 * @since 2021-12-15
	 *
	 * @throws Exception When it fails in unexpected ways; e.g., unreadable file.
	 * @return string Local WordPress version, else empty string if not available in `.dev.json`.
	 */
	public function local_wp_version() : string {
		$public_html_dir = $this->local_wp_public_html_dir();
		$___version_file = U\Dir::join( $public_html_dir, '/wp-includes/version.php' );

		if ( ! $public_html_dir || ! is_dir( $public_html_dir ) ) {
			return ''; // Let this pass, as it's not vital to our needs right now.
		}
		if ( ! is_readable( $___version_file ) ) {
			throw new Exception( 'Missing or unreadable local WP core file: `' . $___version_file . '`.' );
		}
		return ( function () use ( $___version_file ) : string {
			include $___version_file;

			if ( empty( $wp_version ) || ! is_string( $wp_version ) ) {
				throw new Exception( 'Missing or unexpected local `$wp_version` in: `' . $___version_file . '`.' );
			}
			return $wp_version;
		} )();
	}

	/**
	 * Gets comp directory copy configuration, applies to all projects.
	 *
	 * @since 2021-12-15
	 *
	 * @return array Comp directory copy configuration.
	 */
	public function comp_dir_copy_config() : array {
		return [
			'ignore'     => [
				U\Fs::gitignore_regexp( 'positive' ),
			],
			'exceptions' => [],
		];
	}

	/**
	 * Gets distro directory prune configuration, applies to all projects.
	 *
	 * @since 2021-12-15
	 *
	 * @return array Distro directory prune configuration.
	 */
	public function distro_dir_prune_config() : array {
		$config = [
			'prune'      => [
				// `.gitignore`, except `/vendor`.
				U\Fs::gitignore_regexp( 'positive', '.*', [ 'vendor' => false ] ),

				// All dotfiles.
				'/(?:^|.+?\/)\./ui',

				// All of these project config paths.
				'/(?:^|.+?\/)[^\/]+?\.(?:cjs|cts|xml|yml|yaml|json5?|neon|dist|lock)$/ui',
				'/(?:^|.+?\/)(?:babel|gulpfile|gruntfile)(?:\.(?:esm))?\.(?:jsx?|tsx?)$/ui',
				'/(?:^|.+?\/)[^\/]+?\.(?:cfg|config|babel)\.(?:jsx?|tsx?)$/ui',

				// All of these project source-only paths.
				'/(?:^|.+?\/)[^\/]+?\.(?:jsx|tsx?)$/ui',

				// All of these project bin paths.
				'/(?:^|.+?\/)(?:bin)$/ui',
				'/(?:^|.+?\/)[^\/]+?\.(?:exe|bat|sh|bash|zsh)$/ui',

				// All of these arbitrary archive paths.
				'/(?:^|.+?\/)[^\/]+?\.(?:iso|dmg|bz2|7z|zip|tar|tgz|gz|phar)$/ui',

				// All of these project test paths.
				'/(?:^|.+?\/)(?:tests?|test[_\-]files?|phpunit([_\-]tests?)?)$/ui',

				// All of these project doc paths.
				'/(?:^|.+?\/)(?:docs?|api[_\-]docs?|examples?|benchmarks?)$/ui',

				// All of these project build paths.
				'/(?:^|.+?\/)(?:builds?|make(files?)?)$/ui',

				// All of these project dev paths.
				'/(?:^|.+?\/)(?:dev|devops?)$/ui',

				// All of these project package paths.
				'/(?:^|.+?\/)(?:node[_\-]modules|jspm[_\-]packages|bower[_\-]components)$/ui',
			],
			'exceptions' => [
				'/(?:^|.+?\/)\.htaccess$/ui',
			],
		];
		if ( $this->is_wp_project() ) {
			$config[ 'prune' ] = array_merge( $config[ 'prune' ], [
				// Also all of these in root directory.
				'/^(?:vendor)$/ui',
				'/^(?:readme)\.(?:md|txt|rtf)$/ui',
			] );
		}
		return $config;
	}
}
