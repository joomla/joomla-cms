<?php 
/**
* @package     Jokte.Site
* @subpackage	jokteantu
* @author 	    Equipo de desarrollo juuntos.
* @copyleft    (comparte igual)  Jokte!
* @license     GNU General Public License version 3 o superior.
*/
defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class JFormFieldEskins extends JFormField
{
	/**
	 * Element  Sistema de skins	
	 * @access	protected
	 * @var		string
	 */
	protected $type 	= 'Eskins';	
	
	protected function getInput() {
		$doc 	 = JFactory::getDocument();
		$preview = "function doPreviewE(skin){								
    					document.getElementById('previewe').src = \"".JURI::root()."templates/jokteantu/css/skins/\"+skin+\"/previewSkin.png\";
					}";
		$doc->addScriptDeclaration($preview);
		$options = (array) $this->getOptions();		
		$selected = $this->value;				
		$html  = JHtml::_('select.genericlist', $options, 'jform[params][errorskin]','onchange=doPreviewE(this.value)', 'value', 'text', $selected);
		$html .= '<div><img id="previewe" src="/templates/jokteantu/css/skins/'.$selected.'/previewSkin.png" /></div>';		
		return $html;
	}

	protected function getOptions() {
		$options = array();
		$path = (string) $this->element['directory'];
		if (!is_dir($path)) $path = JPATH_ROOT.'/'.$path;
		$folders = JFolder::folders($path, null);
		if (is_array($folders)) {
			foreach($folders as $folder) {
				$folder = JFile::stripExt($folder);
				$options[] = JHtml::_('select.option', $folder, $folder);
			}
		}

		return array_merge($options);
	}
	
}
?>


