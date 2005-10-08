<?PHP
/**
 * patTemplate Tidy output filter
 *
 * $Id$
 *
 * Used to tidy up your resulting HTML document,
 * requires ext/tidy.
 *
 * @package		patTemplate
 * @subpackage	Filters
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * requires tidy extension
 */
define( 'PATTEMPLATE_OUTPUTFILTER_TIDY_ERROR_NOT_SUPPORTED', 'patTemplate::Outputfilter::Tidy::1' );

/**
 * patTemplate Tidy output filter
 *
 * $Id$
 *
 * Used to tidy up your resulting HTML document,
 * requires ext/tidy.
 *
 * @package		patTemplate
 * @subpackage	Filters
 * @author		Stephan Schmidt <schst@php.net>
 */
class patTemplate_OutputFilter_Tidy extends patTemplate_OutputFilter
{
   /**
	* filter name
	*
	* This has to be set in the final
	* filter classes.
	*
	* @var	string
	*/
	var	$_name = 'Tidy';

   /**
	* tidy the data
	*
	* @access	public
	* @param	string		data
	* @return	string		compressed data
	*/
	function apply( $data )
	{
		if (!function_exists('tidy_parse_string')) {
			return $data;
		}

		/**
		 * tidy 1.0
		 */
		if (function_exists( 'tidy_setopt' ) && is_array( $this->_params)) {
			foreach ($this->_params as $opt => $value) {
				tidy_setopt( $opt, $value );
			}
			tidy_parse_string($data);
			tidy_clean_repair();
			$data = tidy_get_output();
		} else {
			$tidy = tidy_parse_string($data, $this->_params);
			tidy_clean_repair($tidy);
			$data = tidy_get_output($tidy);
		}

		return $data;
	}
}
?>