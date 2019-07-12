<?php declare(strict_types=1);
/**
 * WordPress records processing
 *
 * Adds WordPress specific records with respect to privacy settings.
 *
 * @package Processors
 * @author  Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since   1.0.0
 */

namespace Decalog\Processor;

use Monolog\Processor\ProcessorInterface;
use Decalog\System\User;
use Decalog\System\Blog;

/**
 * Define the WordPress processor functionality.
 *
 * Adds WordPress specific records with respect to privacy settings.
 *
 * @package Processors
 * @author  Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since   1.0.0
 */
class WordpressProcessor implements ProcessorInterface {

	/**
	 * Pseudonymization switch.
	 *
	 * @since  1.0.0
	 * @var    boolean    $pseudonymize    Is pseudonymization activated?
	 */
	private static $pseudonymize = false;

	/**
	 * Obfuscation switch.
	 *
	 * @since  1.0.0
	 * @var    boolean    $obfuscation    Is obfuscation activated?
	 */
	private static $obfuscation = false;

	/**
	 * Initializes the class and set its properties.
	 *
	 * @since   1.0.0
	 * @param   boolean $pseudonymize Optional. Is pseudonymization activated?
	 * @param   boolean $obfuscation Optional. Is obfuscation activated?
	 */
	public function __construct( $pseudonymize = false, $obfuscation = false ) {
		self::$pseudonymize = $pseudonymize;
		self::$obfuscation  = $obfuscation;
	}

	/**
	 * Invocation of the processor.
	 *
	 * @since   1.0.0
	 * @param   array $record  Array or added records.
	 * @@return array   The modified records.
	 */
	public function __invoke( array $record ): array {
		$record['extra']['siteid']   = Blog::get_current_blog_id( 0 );
		$record['extra']['sitename'] = Blog::get_current_blog_name();
		$record['extra']['userid']   = User::get_current_user_id( 0 );
		$record['extra']['username'] = User::get_current_user_name();
		$ip                          = filter_input( INPUT_SERVER, 'REMOTE_ADDR' );
		if ( $ip ) {
			$record['extra']['ip'] = $ip;
		}
		if ( self::$obfuscation ) {
			if ( array_key_exists( 'ip', $record['extra'] ) ) {
				$record['extra']['ip'] = 'obf:' . md5( (string) $record['extra']['ip'] );
			}
		}
		if ( self::$pseudonymize ) {
			if ( array_key_exists( 'userid', $record['extra'] ) ) {
				if ( 0 !== $record['extra']['userid'] ) {
					$record['extra']['userid'] = 'obf:' . md5( (string) $record['extra']['userid'] );
				}
				if ( array_key_exists( 'username', $record['extra'] ) ) {
					if ( 0 !== $record['extra']['userid'] ) {
						$record['extra']['username'] = 'obf:' . md5( (string) $record['extra']['username'] );
					}
				}
			}
		}
		return $record;
	}
}
