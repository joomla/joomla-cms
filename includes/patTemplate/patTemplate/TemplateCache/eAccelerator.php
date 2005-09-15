<?PHP
/**
 * patTemplate Template cache that stores data in the eAccelerator Cache
 *
 * $Id: eAccelerator.php 138 2005-09-12 10:37:53Z eddieajau $
 *
 * @package		patTemplate
 * @subpackage	Caches
 * @author		Mike Valstar <mikevalstar@thrashcorp.com>
 */

/**
 * patTemplate Template cache that stores data in the eAccelerator Cache
 *
 * If the lifetime is set to auto, the cache files will be kept until
 * you delete them manually.
 *
 * $Id: eAccelerator.php 138 2005-09-12 10:37:53Z eddieajau $
 *
 * @package		patTemplate
 * @subpackage	Caches
 * @author		Mike Valstar <mikevalstar@thrashcorp.com>
 */
class patTemplate_TemplateCache_eAccelerator extends patTemplate_TemplateCache
{
   /**
	* parameters of the cache
	*
	* @access	private
	* @var		array
	*/
	var $_params = array( 'lifetime' => 'auto');

   /**
	* load template from cache
	*
	* @access   public
	* @param	string			cache key
	* @param	integer			modification time of original template
	* @return   array|boolean	either an array containing the templates or false cache could not be loaded
	*/
	function load( $key, $modTime = -1 )
	{
		if (!function_exists('eaccelerator_lock')) {
			return false;
		}
		$something = eaccelerator_get($key);
		if (is_null($something)){
			return false;
		}else{
			return unserialize($something);
		}
	}

   /**
	* write template to cache
	*
	* @access   public
	* @param	string		cache key
	* @param	array		templates to store
	* @return   boolean		true on success
	*/
	function write( $key, $templates )
	{
		if (!function_exists('eaccelerator_lock')) {
			return false;
		}

		eaccelerator_lock($key);
		if ($this->getParam( 'lifetime' ) == 'auto'){
			 eaccelerator_put($key, serialize( $templates ));
		}else{
			eaccelerator_put($key, serialize( $templates ), $this->getParam( 'lifetime' ) * 60);
		}
		 eaccelerator_unlock($key);

		 return true;
	}
}
?>