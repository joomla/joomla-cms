<?PHP
/**
 * patTemplate function that returns a complete imagetag and
 * width and height are extracted from the image
 *
 * $Id: Img.php 138 2005-09-12 10:37:53Z eddieajau $
 *
 * @package		patTemplate
 * @subpackage	Functions
 * @author		Jens Strobel <strobel@pixeldreamz.com>
 */

/**
 * patTemplate function that returns a complete imagetag and
 * width and height are extracted from the image
 *
 * $Id: Img.php 138 2005-09-12 10:37:53Z eddieajau $
 *
 * @package		patTemplate
 * @subpackage	Functions
 * @author		Jens Strobel <strobel@pixeldreamz.com>
 */
class patTemplate_Function_Img extends patTemplate_Function {
   /**
	* name of the function
	* @access	private
	* @var		string
	*/
	var $_name	  =	'Img';

   /**
	* defaults for some tags
	*
	* @access	protected
	* @var	array
	*/
	var $_defaults = array();

   /**
	* call the function
	*
	* @access	public
	* @param	array	parameters of the function (= attributes of the tag)
	* @param	string	content of the tag
	* @return	string	content to insert into the template
	*/
	function call ($params, $content)
	{
		$src= $params['src'] ? $params['src'] : $content;
		list($width, $height, $type, $attr)= getimagesize($src);

		$this->_defaults= array(
		  'border' => 0,
		  'title'  => '',
		  'alt'    => '',
		  'width'  => $width,
		  'height' => $height
		);

		$params = array_merge($this->_defaults, $params);
		$tags= '';
		foreach ($params as $key => $value){
		  $tags.= sprintf('%s="%s" ', $key, htmlentities($value));
		}
		$imgstr= sprintf('<img %s/>', $tags);

		return $imgstr;
	}
}
?>
