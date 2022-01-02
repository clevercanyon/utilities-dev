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
namespace Clever_Canyon\Utilities_Dev\Toolchain\Tools;

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
 * Benchmark tools.
 *
 * @since 2021-12-15
 */
class Bench extends \Clever_Canyon\Utilities\STC\Abstracts\A6t_Stc_Base {
	/**
	 * Runs a callable w/ scratch output; i.e., JSON data.
	 *
	 * @since 2021-12-20
	 *
	 * @param mixed ...$args {@see run()}.
	 *
	 * @throws Exception When not running from a CLI.
	 */
	public static function scratch_run( ...$args ) : void {
		if ( ! U\Env::is_cli() ) {
			throw new Exception( 'A scratch requires PHP’s command-line interface.' );
		}
		$details = T\Bench::run( ...$args );

		echo '-------------------------------------------' . "\n"; // phpcs:ignore -- ☜(▀̿ ͜▀̿ ̿) ok.
		echo $details->summary . "\n";                             // phpcs:ignore -- ☜(▀̿ ͜▀̿ ̿) ok.
		echo '-------------------------------------------' . "\n"; // phpcs:ignore -- ☜(▀̿ ͜▀̿ ̿) ok.
		echo U\Str::json_encode( $details, true );                 // phpcs:ignore -- ☜(▀̿ ͜▀̿ ̿) ok.
	}

	/**
	 * Runs and compares callables w/ scratch output; i.e., JSON data.
	 *
	 * @since 2021-12-20
	 *
	 * @param mixed ...$args {@see compare()}.
	 *
	 * @throws Exception When not running from a CLI.
	 */
	public static function scratch_compare( ...$args ) : void {
		if ( ! U\Env::is_cli() ) {
			throw new Exception( 'A scratch requires PHP’s command-line interface.' );
		}
		$comparison = T\Bench::compare( ...$args );

		echo '-------------------------------------------' . "\n"; // phpcs:ignore -- ☜(▀̿ ͜▀̿ ̿) ok.
		echo $comparison->summary . "\n";                          // phpcs:ignore -- ☜(▀̿ ͜▀̿ ̿) ok.
		echo '-------------------------------------------' . "\n"; // phpcs:ignore -- ☜(▀̿ ͜▀̿ ̿) ok.
		echo U\Str::json_encode( $comparison, true );              // phpcs:ignore -- ☜(▀̿ ͜▀̿ ̿) ok.
	}

	/**
	 * Runs and measures a callable.
	 *
	 * @since 2021-12-20
	 *
	 * @param callable $callable   What to measure.
	 * @param int|null $iterations Times to fire `$callable`.
	 *                             Default is `10000`.
	 *
	 * @return \stdClass Details including `elapsed_time` in seconds.
	 */
	public static function run( callable $callable, /* int|null */ ?int $iterations = null ) : \stdClass {
		$iterations ??= 10000;
		$start_time = microtime( true );

		for ( $_i = 1; $_i <= $iterations; $_i++ ) {
			$callable(); // What we are measuring.
		}
		$end_time     = microtime( true );
		$elapsed_time = round( $end_time - $start_time, 5 );

		return (object) [
			'iterations'   => $iterations,
			'elapsed_time' => $elapsed_time,
			'summary'      => $iterations . ' iterations' .
				' took ' . $elapsed_time . ' seconds.',
		];
	}

	/**
	 * Runs and compares callables.
	 *
	 * @since 2021-12-20
	 *
	 * @param callable[] $callables  Associative array of what to compare.
	 *                               Tip: Use an associative array with identifying keys,
	 *                               as they will be referenced in the final comparison.
	 *
	 * @param int|null   $iterations Times to fire `$callables`. See {@link run()}.
	 *
	 * @throws Exception If fewer than 2 callables are given.
	 * @return \stdClass Details including `summary` of comparison.
	 */
	public static function compare( array $callables, /* int|null */ ?int $iterations = null ) : \stdClass {
		$number_of_callables = count( $callables );

		if ( $number_of_callables <= 1 ) {
			throw new Exception(
				'Got `' . $number_of_callables . '`.' .
				' Must have 2 or more callables to compare.'
			);
		}
		$results = []; // Initialize.

		foreach ( $callables as $_key => $_callable ) {
			$results[ $_key ]      = (object) [ 'key' => $_key ];
			$results[ $_key ]->run = T\Bench::run( $_callable, $iterations );
		}
		usort( $results, function ( $a, $b ) {
			return $a->run->elapsed_time <=> $b->run->elapsed_time;
		} );

		$fastest        = $results[ 0 ];
		$slowest        = $results[ $number_of_callables - 1 ];
		$speed_increase = U\Math::percentage_change( $fastest->run->elapsed_time, $slowest->run->elapsed_time, 5, true );

		$iterations         = $fastest->run->iterations;
		$total_elapsed_time = round( // For all iterations.
			array_reduce( $results, function ( $total_elapsed_time, $result ) {
				return $total_elapsed_time + $result->run->elapsed_time;
			}, 0 ), 5
		);
		return (object) [
			'summary' => '[' . $fastest->key . '] wins.' . "\n" .
				' ' . $iterations . ' iterations took ' . $fastest->run->elapsed_time . ' seconds.' . "\n" .
				' ' . $speed_increase . ' faster than slowest [' . $slowest->key . '], which took ' . $slowest->run->elapsed_time . ' seconds.',

			'iterations' => $iterations,
			'callables'  => $number_of_callables,

			'results' => $results, // For detailed review.

			'total_iterations'   => $number_of_callables * $iterations,
			'total_elapsed_time' => $total_elapsed_time,
		];
	}
}
