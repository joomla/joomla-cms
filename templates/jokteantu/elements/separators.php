<?php
 /*
 * Jokte! Miscelaneas 
 * @package     Jokte.Site
 * @subpackage	jokteantu
 * @author 	    Equipo de desarrollo juuntos.
 * @copyleft    (comparte igual)  Jokte!
 * @license     GNU General Public License version 3 o superior.
*/

// Acceso directo prohibido
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

class JFormFieldSeparators extends JFormField
{
	/**
	 * Element name	
	 * @access	protected
	 * @var		string
	 */
	protected $type 	= 'Separators';	
	
	/**
	 * Clear space label 
	 */
	public function getLabel() 
	{
		$label = '';
		return $label; 
	}
	
	/**
	 * Otuput News Elements
	*/
	function getInput()
	{
		$class    	= $this->element['class'];		
		$value		= $this->element['value'];
		$title		= $this->element['default'];
		$color		= $this->element['fielName'];		
		// Get Rute and Images From Extension
		$paths 		= $this->element->xpath('//*[@addfieldpath]/@addfieldpath');
		$xml		= $paths[0]; 
		$xml 		= $xml[0]; 
		$rute 		= $xml->data($xml).'/img/';
		$urlback	= '../'.$rute.'back-tit-'.$color.'.png';		
		$urljlogo	= '../'.$rute.'jokte-tpl.png';
		$urlpos		= '../'.$rute.'posiciones.jpg';
		// Generate HTML output
		$html   ='<p style="clear:both;"></p>';
		switch ($class->data($class))
		{
			case "textdesc":
				$html .='<p style="width:100%;border:1px dotted blue;font-size:90%;text-align:center;">'.JText::_($value).'</p>';
				break;
			case "title":				
				$html .='<p style="width:100%;border:1px solid red;line-height:15px;background: url('.$urlback.')repeat-x;color:#FFF;font-weight:bold;font-variant:small-caps;padding:2px;text-align:center">'.JText::_($title).'</p>';
				break;
			case "fulltext":
				$html .='<p style="width:100%;margin:0;border-top:1px solid red;border-left:1px solid red;border-right:1px solid red;line-height:15px;background: url('.$urlback.') repeat-x;color:#FFF;font-weight:bold;font-variant:small-caps;padding:2px;text-align:center;text-shadow:1px 1px #808080">.: '.JText::_($title).' :.</p>';
				$html .='<p style="width:100%;border:1px solid red;background:#ECECEC;color:#000;padding:0 2px;text-align:justify;font-size:90%">'.JText::_($value).'</p>';
				break;
			case "about":
				$html .='<p style="color:#000053;text-align:center;text-shadow:1px 1px #CCC;">';
				$html .='<img src="'.$urljlogo.'" alt="Jokte!" title="Jokte! es software libre para latinoamerica" style="float:left;padding:4px;" />';
				$html .= JText::_('JOKTE_TEXT_FOOT');				
				$html .='</p>';	
				break;
			case "posimg":
				$html .='<p style="text-align:center;">';
				$html .='<img src="'.$urlpos.'" alt="JokteAntu posiciones" title="Jokte! es software libre para latinoamerica" style="padding:4px;" />';
				$html .='</p>';	
				break;		
		}		
		return $html;		
	}
	
}
?>
