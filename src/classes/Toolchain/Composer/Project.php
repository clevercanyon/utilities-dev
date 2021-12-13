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
use Clever_Canyon\Utilities\OOP\Version_1_0_0\{ Base };

/**
 * Project.
 *
 * @since 1.0.0
 */
class Project extends Base {
	/**
	 * Directory.
	 *
	 * @since 1.0.0
	 */
	protected string $dir;

	/**
	 * JSON props.
	 *
	 * @since 1.0.0
	 */
	protected \StdClass $json;

	/**
	 * Name.
	 *
	 * @since 1.0.0
	 */
	protected string $name;

	/**
	 * Brand slug.
	 *
	 * @since 1.0.0
	 */
	protected string $brand_slug;

	/**
	 * Brand var.
	 *
	 * @since 1.0.0
	 */
	protected string $brand_var;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $dir Directory.
	 *
	 * @throws \Exception If missing project data.
	 */
	public function __construct( string $dir ) {
		parent::__construct();

		$this->dir  = U\Fs::normalize( $dir );
		$this->json = static::parse_json( $this->dir );
		$this->name = strval( $this->json->name ?? '' );

		$this->brand_slug = $this->json->extra->props->brand->data->slug ?? '';
		$this->brand_var  = str_replace( '-', '_', $this->brand_slug );

		if ( ! $this->dir || ! $this->json || ! $this->name ) {
			throw new \Exception( 'Missing `Project` dir|json|name.' );
		}
		if ( ! $this->brand_slug || ! $this->brand_var ) {
			throw new \Exception( 'Missing `Project` brand_slug|brand_var.' );
		}
		if ( $this->is_wp_plugin() && ! $this->wp_plugin_data() ) {
			throw new \Exception( 'Missing or incomplete `Project` WP plugin data.' );

		} elseif ( $this->is_wp_theme() && ! $this->wp_theme_data() ) {
			throw new \Exception( 'Missing or incomplete `Project` WP theme data.' );
		}
	}

	/**
	 * Parses a project's composer.json file.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $dir Directory path.
	 *
	 * @return \StdClass   Project's JSON properties.
	 */
	protected static function parse_json( string $dir ) : \StdClass {
		if ( ! is_file( $dir . '/composer.json' ) ) {
			return (object) []; // Not possible.
		}
		$json = json_decode( file_get_contents( $dir . '/composer.json' ) );
		$json = is_object( $json ) ? $json : (object) [];

		if ( ! isset( $json->extra ) && preg_match( '/\.trunk$/ui', $json->name ) && is_file( dirname( $dir ) . '/composer.json' ) ) {
			$parent_json = json_decode( file_get_contents( dirname( $dir ) . '/composer.json' ) );
			$parent_json = is_object( $parent_json ) ? $parent_json : (object) [];

			if ( ( $parent_json->name ?? '' ) . '.trunk' === $json->name && isset( $parent_json->extra ) ) {
				$json->extra = $parent_json->extra;
			}
		}
		if ( isset( $json->extra ) && is_object( $json->extra ) ) {
			$json->extra = U\Fs::expand_home( $json->extra );
		}
		return $json;
	}

	/**
	 * Has directory?
	 *
	 * @since 1.0.0
	 *
	 * @param  string $subpath Directory subpath.
	 *
	 * @return bool            True if has directory.
	 */
	public function has_dir( string $subpath ) : bool {
		return is_dir( $this->dir . '/' . ltrim( U\Fs::normalize( $subpath ), '/' ) );
	}

	/**
	 * Has file?
	 *
	 * @since 1.0.0
	 *
	 * @param  string $subpath File subpath.
	 *
	 * @return bool            True if has file.
	 */
	public function has_file( string $subpath ) : bool {
		return is_file( $this->dir . '/' . ltrim( U\Fs::normalize( $subpath ), '/' ) );
	}

	/**
	 * WordPress project?
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if WordPress project.
	 */
	public function is_wp_project() : bool {
		return $this->is_wp_plugin() || $this->is_wp_theme();
	}

	/**
	 * WordPress plugin?
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if WordPress plugin.
	 */
	public function is_wp_plugin() : bool {
		return $this->has_file( 'trunk/plugin.php' );
	}

	/**
	 * WordPress theme?
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if WordPress theme.
	 */
	public function is_wp_theme() : bool {
		return $this->has_file( 'trunk/theme.php' );
	}

	/**
	 * Gets WordPress plugin data.
	 *
	 * @since 1.0.0
	 *
	 * @return bool|\StdClass Plugin data.
	 *
	 * @throws \Exception     When called and it fails in any way; e.g., incomplete data.
	 *
	 * @internal              See also {@link \WP_Groove\Framework\Plugin\Base::__construct()}
	 */
	public function wp_plugin_data() {
		if ( null !== ( $cache =& $this->oop_cache( __FUNCTION__ ) ) ) {
			return $cache; // Cached already.
		}
		if ( ! $this->is_wp_plugin() ) {
			return $cache = false; // Not possible.
		}
		$data = (object) [];

		$data->file        = U\Fs::normalize( $this->dir . '/trunk/plugin.php' );
		$data->readme_file = U\Fs::normalize( $this->dir . '/trunk/readme.txt' );
		$data->dir         = dirname( $data->file );

		if ( ! is_dir( $data->dir ) || ! is_readable( $data->dir )
				|| ! is_file( $data->file ) || ! is_readable( $data->file )
				|| ! is_file( $data->readme_file ) || ! is_readable( $data->readme_file ) ) {
			throw new \Exception(
				'Missing or unreadable file in `' . $data->dir . '`.' .
				' Must have `plugin.php` and `readme.txt`.'
			);
		}
		if ( count( $_p = explode( '/', $data->file ) ) < 3 ) {
			throw new \Exception( 'Unexpected plugin file path: `' . $data->file . '`.' );
		}
		$data->basename = $_p[ count( $_p ) - 3 ] . '/' . $_p[ count( $_p ) - 2 ];

		$data->slug = basename( dirname( $data->basename ) );
		$data->var  = str_replace( '-', '_', $data->slug );

		$data->brand_slug =& $this->brand_slug;
		$data->brand_var  =& $this->brand_var;

		$data->unbranded_slug = preg_replace( '/^' . preg_quote( $data->brand_slug . '-', '/' ) . '/ui', '', $data->slug );
		$data->unbranded_var  = str_replace( '-', '_', $data->unbranded_slug );

		$data->headers = $this->wp_plugin_file_headers();

		return $cache = $data;
	}

	/**
	 * Gets WordPress plugin file headers.
	 *
	 * @since 1.0.0
	 *
	 * @return null|\StdClass Plugin file headers.
	 *
	 * @throws \Exception     When called and it fails in any way; e.g., incomplete data.
	 *
	 * @internal              See <https://developer.wordpress.org/reference/functions/get_plugin_data/>
	 */
	protected function wp_plugin_file_headers() : ?\StdClass {
		if ( ! $this->is_wp_plugin() ) {
			return null; // Not possible.
		}
		$data = (object) [
			'_map' => [
				'name'                    => 'Plugin Name',
				'url'                     => 'Plugin URI',

				'description'             => 'Description',
				'tags'                    => 'Tags',

				'version'                 => 'Version',
				'stable_tag'              => 'Stable tag', // Custom addition.
				// ^ This header is not part of {@link get_plugin_data()}, but here for consistency.

				'author'                  => 'Author',
				'author_url'              => 'Author URI',
				'donate_url'              => 'Donate link',
				'contributors'            => 'Contributors', // Custom addition.
				// ^ This header is not part of {@link get_plugin_data()}, but here for consistency.

				'license'                 => 'License', // Custom addition.
				'license_url'             => 'License URI', // Custom addition.
				// ^ These headers are not part of {@link get_plugin_data()}, but here for consistency.

				'text_domain'             => 'Text Domain',
				'domain_path'             => 'Domain Path',

				'network'                 => 'Network',

				'requires_php_version'    => 'Requires PHP',
				'requires_wp_version'     => 'Requires at least',
				'tested_up_to_wp_version' => 'Tested up to', // Custom addition.
				// ^ This header is not part of {@link get_plugin_data()}, but here for consistency.

				'update_url'              => 'Update URI',
			],
		];
		$file = U\Fs::normalize( $this->dir . '/trunk/plugin.php' );

		$first_8kbs = file_get_contents( $file, false, null, 0, 8192 );
		$first_8kbs = str_replace( "\r", "\n", $first_8kbs );

		foreach ( $data->_map as $_prop => $_header ) {
			if ( preg_match( '/^(?:[ \t]*\<\?php)?[ \t\/*#@]*' . preg_quote( $_header, '/' ) . '\:(.*)$/mi', $first_8kbs, $_m ) && $_m[1] ) {
				$data->{$_prop} = trim( preg_replace( '/\s*(?:\*\/|\?\>).*/', '', $_m[1] ) );
			} else {
				$data->{$_prop} = '';
			}
			if ( ! $data->{$_prop} && ! in_array( $_prop, [ 'network', 'update_url' ], true ) ) {
				throw new \Exception( 'Missing `' . $_header . '` in plugin file headers.' );
			}
		}
		return $data;
	}

	/**
	 * Gets a theme's data.
	 *
	 * @since 1.0.0
	 *
	 * @return bool|\StdClass Theme data.
	 *
	 * @throws \Exception     When called and it fails in any way; e.g., incomplete data.
	 *
	 * @internal              See also {@link \WP_Groove\Framework\Theme\Base::__construct()}
	 */
	public function wp_theme_data() {
		if ( null !== ( $cache =& $this->oop_cache( __FUNCTION__ ) ) ) {
			return $cache; // Cached already.
		}
		if ( ! $this->is_wp_theme() ) {
			return $cache = false; // Not possible.
		}
		$data = (object) [];

		$data->file           = U\Fs::normalize( $this->dir . '/trunk/theme.php' );
		$data->functions_file = U\Fs::normalize( $this->dir . '/trunk/functions.php' );
		$data->style_file     = U\Fs::normalize( $this->dir . '/trunk/style.css' );
		$data->readme_file    = U\Fs::normalize( $this->dir . '/trunk/readme.txt' );
		$data->dir            = dirname( $data->file );

		if ( ! is_dir( $data->dir ) || ! is_readable( $data->dir )
				|| ! is_file( $data->file ) || ! is_readable( $data->file )
				|| ! is_file( $data->functions_file ) || ! is_readable( $data->functions_file )
				|| ! is_file( $data->style_file ) || ! is_readable( $data->style_file )
				|| ! is_file( $data->readme_file ) || ! is_readable( $data->readme_file ) ) {
			throw new \Exception(
				'Missing or unreadable file in `' . $data->dir . '`.' .
				' Must have `theme.php`, `functions.php`, `style.css`, and `readme.txt`.'
			);
		}
		if ( count( $_p = explode( '/', $data->file ) ) < 3 ) {
			throw new \Exception( 'Unexpected theme file path: `' . $data->file . '`.' );
		}
		$data->basename = $_p[ count( $_p ) - 3 ] . '/' . $_p[ count( $_p ) - 2 ];

		$data->slug = basename( dirname( $data->basename ) );
		$data->var  = str_replace( '-', '_', $data->slug );

		$data->brand_slug =& $this->brand_slug;
		$data->brand_var  =& $this->brand_var;

		$data->unbranded_slug = preg_replace( '/^' . preg_quote( $data->brand_slug . '-', '/' ) . '/ui', '', $data->slug );
		$data->unbranded_var  = str_replace( '-', '_', $data->unbranded_slug );

		$data->headers = $this->wp_theme_file_headers();

		return $cache = $data;
	}

	/**
	 * Gets theme file headers.
	 *
	 * @since 1.0.0
	 *
	 * @return null|\StdClass Theme file headers.
	 *
	 * @throws \Exception     When called and it fails in any way; e.g., incomplete data.
	 *
	 * @internal              See <https://developer.wordpress.org/reference/classes/wp_theme/>
	 */
	protected function wp_theme_file_headers() : ?\StdClass {
		if ( ! $this->is_wp_theme() ) {
			return null; // Not possible.
		}
		$data = (object) [
			'_map' => [
				'name'                    => 'Theme Name',
				'url'                     => 'Theme URI',

				'description'             => 'Description',
				'tags'                    => 'Tags',

				'template'                => 'Template',

				'version'                 => 'Version',
				'stable_tag'              => 'Stable tag', // Custom addition.
				// ^ This header is not part of {@link \WP_Theme}, but here for consistency.
				'status'                  => 'Status', // Deprecated? Defaults to `publish` in core <https://git.io/JMwZo>.

				'author'                  => 'Author',
				'author_url'              => 'Author URI',
				'donate_url'              => 'Donate link', // Custom addition.
				// ^ This header is not part of {@link \WP_Theme}, but here for consistency.
				'contributors'            => 'Contributors', // Custom addition.
				// ^ This header is not part of {@link \WP_Theme}, but here for consistency.

				'license'                 => 'License', // Custom addition.
				'license_url'             => 'License URI', // Custom addition.
				// ^ These headers are not part of {@link \WP_Theme}, but here for consistency.

				'text_domain'             => 'Text Domain',
				'domain_path'             => 'Domain Path',

				'requires_php_version'    => 'Requires PHP',
				'requires_wp_version'     => 'Requires at least',
				'tested_up_to_wp_version' => 'Tested up to', // Custom addition.
				// ^ This header is not part of {@link \WP_Theme}, but here for consistency.

				'update_url'              => 'Update URI', // Custom addition.
				// ^ This header is not part of {@link \WP_Theme}, but here for consistency.
			],
		];
		$file = U\Fs::normalize( $this->dir . '/trunk/theme.php' );

		$first_8kbs = file_get_contents( $file, false, null, 0, 8192 );
		$first_8kbs = str_replace( "\r", "\n", $first_8kbs );

		foreach ( $data->_map as $_prop => $_header ) {
			if ( preg_match( '/^(?:[ \t]*\<\?php)?[ \t\/*#@]*' . preg_quote( $_header, '/' ) . '\:(.*)$/mi', $first_8kbs, $_m ) && $_m[1] ) {
				$data->{$_prop} = trim( preg_replace( '/\s*(?:\*\/|\?\>).*/', '', $_m[1] ) );
			} else {
				$data->{$_prop} = '';
			}
			if ( ! $data->{$_prop} && ! in_array( $_prop, [ 'template', 'status', 'update_url' ], true ) ) {
				throw new \Exception( 'Missing `' . $_header . '` in theme file headers.' );
			}
		}
		return $data;
	}

	/**
	 * Gets s3 bucket name.
	 *
	 * @since 1.0.0
	 *
	 * @return string     Bucket name.
	 *
	 * @throws \Exception When called and it fails in any way; e.g., missing bucket.
	 */
	public function s3_bucket() : string {
		if ( ! $bucket = strval( $this->json->extra->props->brand->aws->s3->bucket ?? '' ) ) {
			throw new \Exception( 'Missing `brand.aws.s3.bucket`.' );
		}
		return $bucket;
	}

	/**
	 * Gets s3 bucket config.
	 *
	 * @since 1.0.0
	 *
	 * @return array       Bucket config suitable for {@link Aws\S3\S3Client}.
	 *
	 * @throws \Exception  When called and it fails in any way; e.g., missing credentials.
	 */
	public function s3_bucket_config() : array {
		$dev_json = Common::dev_json();

		$aws_access_key = U\Obj::get_prop( $dev_json, 'clevercanyon.aws.credentials.access_key' );
		$aws_secret_key = U\Obj::get_prop( $dev_json, 'clevercanyon.aws.credentials.secret_key' );

		if ( ! $aws_access_key || ! $aws_secret_key ) {
			throw new \Exception( 'Missing `clevercanyon.aws.credentials.access_key|secret_key`.' );
		}
		return [
			'version'           => '2006-03-01',
			'region'            => 'us-east-1',
			'signature_version' => 'v4',
			'credentials'       => [
				'key'    => $aws_access_key,
				'secret' => $aws_secret_key,
			],
		];
	}

	/**
	 * Gets an HMAC SHA256 keyed hash.
	 *
	 * @param  string $string String to hash.
	 *
	 * @return string         HMAC SHA256 keyed hash. 64 bytes in length.
	 *
	 * @throws \Exception     When called and it fails in any way; e.g., missing HMAC key.
	 */
	public function s3_hash_hmac_sha256( string $string ) : string {
		$dev_json = Common::dev_json();

		$dev_json_map     = $this->json->extra->{'props-dev'}->dev_json_map ?? null;
		$s3_hash_hmac_key = U\Obj::get_prop( $dev_json, $dev_json_map->brand->aws->s3->hash_hmac_key_path ?? '' );

		if ( ! $s3_hash_hmac_key ) {
			throw new \Exception( 'Missing `brand.aws.s3.hash_hmac_key`.' );
		}
		return hash_hmac( 'sha256', $string, $s3_hash_hmac_key );
	}

	/**
	 * Gets local WordPress versions.
	 *
	 * @since 1.0.0
	 *
	 * @return string Local WordPress version, else empty string.
	 *
	 * @throws \Exception   When called and it fails in unexpected ways; e.g., unreadable file.
	 */
	public function local_wp_version() : string {
		$dev_json                 = Common::dev_json();
		$local_wp_public_html_dir = U\Fs::normalize( U\Obj::get_prop( $dev_json, 'clevercanyon.local.wordpress.public_html_dir' ) ?: '' );
		$local_wp_versions_file   = $local_wp_public_html_dir . '/wp-includes/version.php';

		if ( ! $local_wp_public_html_dir || ! is_dir( $local_wp_public_html_dir ) ) {
			return ''; // Let this pass, as it's not vital to our needs right now.
		}
		if ( ! is_readable( $local_wp_versions_file ) ) {
			throw new \Exception( 'Missing or unreadable local WP core file: `' . $local_wp_versions_file . '`.' );
		}
		return (string) ( function() use ( $local_wp_versions_file ) {
			include $local_wp_versions_file;

			if ( empty( $wp_version ) || ! is_string( $wp_version ) ) {
				throw new \Exception( 'Missing or unexpected local `$wp_version` in: `' . $local_wp_versions_file . '`.' );
			}
			return $wp_version;
		} )();
	}

	/**
	 * Gets distro prune configuration.
	 *
	 * @since 1.0.0
	 *
	 * @return array Prune configuration.
	 */
	public function distro_prune_config() : array {
		$config = [
			'prune'            => [
				// Any of these dot paths.
				'/(^|.+?\/)\./ui',

				// Any of these project config paths.
				'/(^|.+?\/)[^\/]+?\.(cjs|cts|xml|yml|yaml|json5?|neon|dist|lock)$/ui',
				'/(^|.+?\/)(babel|gulpfile|gruntfile)(\.(esm))?\.(jsx?|tsx?)$/ui',
				'/(^|.+?\/)[^\/]+?\.(cfg|config|babel)\.(jsx?|tsx?)$/ui',

				// Any of these project source-only paths.
				'/(^|.+?\/)[^\/]+?\.(jsx|tsx?)$/ui',

				// Any of these project bin paths.
				'/(^|.+?\/)(bin)$/ui',
				'/(^|.+?\/)[^\/]+?\.(exe|bat|sh|bash|zsh)$/ui',

				// Any of these arbitrary archive paths.
				'/(^|.+?\/)[^\/]+?\.(iso|dmg|bz2|7z|zip|tar|tgz|gz|phar)$/ui',

				// Any of these project test paths.
				'/(^|.+?\/)(tests?|test[_\-]files?|phpunit([_\-]tests?)?)$/ui',

				// Any of these project doc paths.
				'/(^|.+?\/)(docs?|api[_\-]docs?|examples?|benchmarks?)$/ui',

				// Any of these project build paths.
				'/(^|.+?\/)(builds?|make(files?)?)$/ui',

				// Any of these project dev paths.
				'/(^|.+?\/)(dev|devops?)$/ui',

				// Any of these project package paths.
				'/(^|.+?\/)(node[_\-]modules|bower[_\-]components|jspm[_\-]packages)$/ui',
			],
			'prune_exceptions' => [
				'/(^|.+?\/)vendor\/composer\/installed\.json$/ui',
			],
		];
		if ( $this->is_wp_project() ) {
			$config['prune'] = array_merge( $config['prune'], [
				'/^(vendor)$/ui',
				'/^(readme)\.(md|txt|rtf)$/ui',
			] );
		}
		return $config;
	}
}
