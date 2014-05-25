<?php 
/**
* @version		1.0.0
* @package		Jokte.element
* @copyright	Copyleft 2012 - 2014 Comunidad Juuntos, Proyecto Jokte!
* @license		GNU/GPL 3.0
*/
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldSeparador extends JFormField
{
	/**
	 * Element name	
	 * @access	protected
	 * @var		string
	 */
	protected $type 	= 'Separador';	
	
	/**
	 * Clear space label 
	 */
	public function getLabel() {
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
		$color		= $this->element['fielname'];
		$logo		= $this->element['logo'];
		$leyend		= $this->element['leyend'];
				
		// Ruta para skins
		$rute 		= '../media/separadores/';
		$urlback	= $rute.'back-tit-'.$color.'.png';
		
		// Ruta logo 
		$urllogo	= '../images/'.$logo;
		
		// Generate HTML output
		$html   ='<p style="clear:both;"></p>';
		switch ($class->data($class))
		{
			case "textdesc":
				$html .='<p class="separador-textdesc">'.JText::_($value).'</p>';
				break;
			case "title":
				$html .='<p class="separador-title" style="background: url("'.$urlback.'")repeat-x;">'.JText::_($title).'</p>';
				break;
			case "fulltext":
				$html .='<p class="separador-full-title" style="background: url('.$urlback.') repeat-x;">.: '.JText::_($title).' :.</p>';
				$html .='<p class="separador-full-textdesc">'.JText::_($value).'</p>';
				break;
			case "about":
				$html .='<p class="separador-about">';
				$html .='<img src="'.$urllogo.'" alt="" title="Jokte! Extension" class="separador-img" />';
				$html .= JText::_($leyend);
				$html .='</p>';
		}		
		return $html;
	}
	
}
?>