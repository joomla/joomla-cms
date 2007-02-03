<?php
/**
 * patTemplate modifier Truncate
 *
 * Truncate a string variable to fixed length and add a suffix if it was truncated.
 * It can also start from an offset and add a prefix.
 *
 * @package     patTemplate
 * @subpackage  Modifiers
 * @author      Rafa Couto <rafacouto@yahoo.com>
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * patTemplate modifier Truncate
 *
 * Truncate a string variable to fixed length and add a suffix if it was truncated.
 * It can also start from an offset and add a prefix.
 *
 * Possible attributes are:
 * - length (integer)
 * - suffix (string)
 * - start
 * - prefix (string)
 *
 * @package     patTemplate
 * @subpackage  Modifiers
 * @author      Rafa Couto <rafacouto@yahoo.com>
 */
class patTemplate_Modifier_Truncate extends patTemplate_Modifier
{

   /**
	* modify the value
	*
	* @access  public
	* @param  string    value
	* @return  string    modified value
	*/
	function modify($value, $params = array())
	{
		// length
		if (!isset( $params['length'])) {
			return $value;
		}
		settype($params['length'], 'integer');

    	$decode = isset( $params['htmlsafe'] );
   		if (function_exists( 'html_entity_decode' ) && $decode) {
	    	$value = html_entity_decode( $value );
    	}

        // start
		if (isset($params['start'])) {
			settype( $params['start'], 'integer' );
		} else {
			$params['start'] = 0;
		}

		// prefix
		if (isset($params['prefix'])) {
			$prefix = ($params['start'] == 0 ? '' : $params['prefix']);
		} else {
			$prefix = '';
		}

		// suffix
		if (isset($params['suffix'])) {
			$suffix = $params['suffix'];
		} else {
			$suffix = '';
		}

		$initial_len = strlen($value);
		$value = substr($value, $params['start'], $params['length']);

		if ($initial_len <= strlen($value)) {
			$suffix = '';
		}

        $value = $prefix.$value.$suffix;

        return $decode ? htmlspecialchars( $value, ENT_QUOTES ) : $value;
	}
}
?>
