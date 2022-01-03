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
namespace Clever_Canyon\Utilities_Dev\Toolchain\Scoper;

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
use PhpParser;

// </editor-fold>

/**
 * Scoper config file generator.
 *
 * @since 2021-12-15
 */
class Config_File extends \Clever_Canyon\Utilities\OOP\Abstracts\A6t_CLI_Tool {
	/**
	 * Version.
	 */
	protected const VERSION = '1.0.0';

	/**
	 * Tool name.
	 */
	protected const NAME = 'Scoper/Config_File';

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
			'update' => [
				'callback'    => [ $this, 'update' ],
				'synopsis'    => 'Updates `src/libraries/dotfiles/.scoper.php` config file.',
				'description' => 'Updates `src/libraries/dotfiles/.scoper.php` config file. See ' . __CLASS__ . '::update()',
				'options'     => [],
			],
		] );
		$this->route_request();
	}

	/**
	 * Command: `update`.
	 *
	 * @since 2021-12-15
	 */
	protected function update() : void {
		try {
			$this->update_file();

		} catch ( \Throwable $throwable ) {
			U\CLI::error( $throwable->getMessage() );
			U\CLI::error( $throwable->getTraceAsString() );
			U\CLI::exit_status( 1 );
		}
	}

	/**
	 * Updates config file.
	 *
	 * @since 2022-01-02
	 *
	 * @throws Fatal_Exception On any error.
	 */
	protected function update_file() : void {
		U\Env::raise_memory_limit();
		$file = dirname( __FILE__, 4 ) . '/libraries/dotfiles/.scoper.php';

		if ( false === file_put_contents( $file, $this->generate_config_file_contents() ) ) {
			throw new Fatal_Exception( 'Failed to update PHP Scoper config file: `' . $file . '`.' );
		}
	}

	/**
	 * Generates config file contents.
	 *
	 * @since 2022-01-02
	 *
	 * @return string Config file contents.
	 */
	protected function generate_config_file_contents() : string {
		$names = $this->php_parser_compile_names();

		sort( $names->constants, SORT_NATURAL );
		sort( $names->classes, SORT_NATURAL );
		sort( $names->functions, SORT_NATURAL );

		$header = <<<'HEADER'
		<?php
		/**
		 * PHP Scoper config file.
		 *
		 * @since 1.0.0
		 *
		 * @note PHP Scoper doesn't automatically know about this file.
		 *       You must pass it using the `--config` option.
		 *
		 * @note PLEASE DO NOT EDIT THIS FILE!
		 * This file and the contents of it are updated automatically.
		 * Instead of editing, please see source repository @ <https://git.io/JD8Zo>.
		 */
		 
		/**
		 * Lint configuration.
		 *
		 * @since        2021-12-15
		 *
		 * @noinspection ALL
		 * phpcs:disableFile
		 */

		/**
		 * Declarations & namespace.
		 *
		 * @since 2021-12-25
		 */
		declare( strict_types = 1 ); // ｡･:*:･ﾟ★.
		namespace Clever_Canyon\Scoper\Config_File;
		HEADER;

		$body = <<<'BODY'
		/**
		 * Configuration.
		 *
		 * @since 2021-12-25
		 */
		BODY;
		$body .= "\n" . 'return ' .
			var_export( [
				'exclude-constants' => $names->constants,
				'exclude-classes'   => $names->classes,
				'exclude-functions' => $names->functions,
			], true ) . ';';

		return $header . "\n\n" . $body;
	}

	/**
	 * Compiles
	 *
	 * @since 2022-01-02
	 *
	 * @throws Fatal_Exception On any error.
	 *
	 * @return object Object w/ properties containing names of class, functions, etc.
	 *                {@see php_parser_node_visitor()} for further details.
	 */
	protected function php_parser_compile_names() : object {
		try {
			$visitor   = $this->php_parser_node_visitor();
			$traverser = $this->php_parser_node_traverser( $visitor );

			foreach ( [
				'php_stubs_wordpress_globals',
				'php_stubs_wordpress_stubs',
				'php_stubs_woocommerce_stubs',
			] as $_stubs
			) {
				$_parser = $this->php_parser();
				U\CLI::log( 'Parsing: ' . $_stubs . '()' );
				$traverser->traverse( $_parser->parse( $this->{$_stubs}() ) );
			}
			return (object) (array) $visitor;
		} catch ( \Throwable $throwable ) {
			throw new Fatal_Exception( $throwable->getMessage() );
		}
	}

	/**
	 * PhpParser instance.
	 *
	 * @since 2022-01-02
	 *
	 * @throws \LogicException On invalid args.
	 *
	 * @return PhpParser\Parser PhpParser instance.
	 */
	protected function php_parser() : PhpParser\Parser {
		return ( new PhpParser\ParserFactory() )->create( PhpParser\ParserFactory::PREFER_PHP7 );
	}

	/**
	 * Produces and returns a node traverser for PhpParser.
	 *
	 * @since 2022-01-02
	 *
	 * @param PhpParser\NodeVisitorAbstract $visitor Visitor; {@see php_parser_node_visitor()}.
	 *
	 * @return PhpParser\NodeTraverser Node traverser.
	 */
	protected function php_parser_node_traverser( PhpParser\NodeVisitorAbstract $visitor ) : PhpParser\NodeTraverser {
		$traverser = new PhpParser\NodeTraverser();
		$traverser->addVisitor( new PhpParser\NodeVisitor\NameResolver() );
		$traverser->addVisitor( $visitor );

		return $traverser;
	}

	/**
	 * Produces and returns a node visitor for PhpParser.
	 *
	 * @since 2022-01-02
	 *
	 * @return PhpParser\NodeVisitorAbstract Node visitor.
	 */
	protected function php_parser_node_visitor() : PhpParser\NodeVisitorAbstract {
		return new class extends PhpParser\NodeVisitorAbstract {
			/**
			 * Consts array.
			 *
			 * @since 2022-01-02
			 */
			public array $constants = [];

			/**
			 * Classes array.
			 *
			 * @since 2022-01-02
			 */
			public array $classes = [];

			/**
			 * Functions array.
			 *
			 * @since 2022-01-02
			 */
			public array $functions = [];

			/**
			 * Consts array.
			 *
			 * @since 2022-01-02
			 *
			 * @param PhpParser\Node $node Node.
			 */
			public function leaveNode( PhpParser\Node $node ) : void {
				parent::leaveNode( $node );

				if ( $node instanceof PhpParser\Node\Stmt\Const_ ) {
					foreach ( $node->consts as $_const ) {
						$_fqn = $_const->namespacedName->toString();

						$this->constants[] = $_fqn;
						U\CLI::log( 'Constant: ' . $_fqn );
					}
				} elseif ( $node instanceof PhpParser\Node\Expr\FuncCall && $node->name instanceof PhpParser\Node\Name ) {
					if ( 'define' === $node->name->toString() && count( $node->args ) >= 2 ) {
						if ( $node->args[ 0 ]->value instanceof PhpParser\Node\Scalar\String_ ) {
							$_fqn = $node->args[ 0 ]->value->value;

							$this->constants[] = $_fqn;
							U\CLI::log( 'Constant: ' . $_fqn );
						}
					}
				} elseif ( $node instanceof PhpParser\Node\Stmt\Class_ ) {
					$_fqn = $node->namespacedName->toString();

					$this->classes[] = $_fqn;
					U\CLI::log( 'Class: ' . $_fqn );

				} elseif ( $node instanceof PhpParser\Node\Stmt\Interface_ ) {
					$_fqn = $node->namespacedName->toString();

					$this->classes[] = $_fqn;
					U\CLI::log( 'Interface: ' . $_fqn );

				} elseif ( $node instanceof PhpParser\Node\Stmt\Trait_ ) {
					$_fqn = $node->namespacedName->toString();

					$this->classes[] = $_fqn;
					U\CLI::log( 'Trait: ' . $_fqn );

				} elseif ( $node instanceof PhpParser\Node\Stmt\Function_ ) {
					$_fqn = $node->namespacedName->toString();
					$_fqn = 'x_stubfix_readonly' === $_fqn ? 'readonly' : $_fqn;

					$this->functions[] = $_fqn;
					U\CLI::log( 'Function: ' . $_fqn );
				}
			}
		};
	}

	/**
	 * WordPress globals.
	 *
	 * @since 2022-01-02
	 *
	 * @return string WordPress global stubs.
	 */
	protected function php_stubs_wordpress_globals() : string {
		return file_get_contents( dirname( __FILE__, 5 ) . '/vendor/php-stubs/wordpress-globals/wordpress-globals.php' );
	}

	/**
	 * WordPress stubs.
	 *
	 * @since 2022-01-02
	 *
	 * @return string WordPress stubs.
	 */
	protected function php_stubs_wordpress_stubs() : string {
		$stubs = file_get_contents( dirname( __FILE__, 5 ) . '/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php' );
		// Works around an unexpected T_READONLY token. Likely due to a bug in PhpParser.
		return str_replace( 'function readonly(', 'function x_stubfix_readonly(', $stubs );
	}

	/**
	 * WooCommerce stubs.
	 *
	 * @since 2022-01-02
	 *
	 * @return string WooCommerce stubs.
	 */
	protected function php_stubs_woocommerce_stubs() : string {
		return file_get_contents( dirname( __FILE__, 5 ) . '/vendor/php-stubs/woocommerce-stubs/woocommerce-stubs.php' );
	}
}
