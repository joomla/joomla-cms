<?php
 /*
 ** @package     Jokte.Site
 * @subpackage	jokteantu
 * @author 	    Equipo de desarrollo juuntos.
 * @copyleft    (comparte igual)  Jokte!
 * @license     GNU General Public License version 3 o superior.
*/
// Acceso directo prohibido
defined('_JEXEC') or die;

jimport('joomla.form.formfield');
JHTML::_('behavior.modal');

class JFormFieldModules extends JFormField
{
	public function getLabel() 
	{
		$label = $this->element['label'];		
		$desc = $this->element['description'];
		return '<div class="jkte_label"><b>'.Jtext::_($label).'</b><br /><small>'.JText::_($desc).'</small></div>'; 
	}
		
	public function getInput()
	{
		// Estilos
		$doc = JFactory::getDocument();
		$style = ".jkte_label{width:100%;border:1px solid #CCC;clear:both;text-align:center;font-variant:small-caps;background:#fafafa;}
.mbox_jokteantu{clear:right;margin-bottom:20px;} .even,.odd{float:left;text-align:right;}";
		$doc -> addStyleDeclaration($style);
			
		// Propiedades de los elementos		
		$items 		= $this->element['items'];
		$default 	= explode('|', $this->element['default']);
		$values 	= is_array( $this->value ) ? $this->value : explode('|', $this->value);
				
		$size 		 = $this->element['size'];
		$css_class 	 = $this->element['class'];
		$labels 	 = explode('|', $this->element['labels']);
		$unique_id 	 = $this->element['name'];
				
		$div 	= array(); 
		$new_div= array();

		for ( $i=0; $i < $items; $i++ ){	
			$div[$i] = array();
			$cell_css = $i % 2 == 0 ? 'even':'odd';
			$div[$i][] = '<div class="'.$cell_css.'"><label for="'.$labels[$i].'">'.$labels[$i].'</label></div>';		
			$div[$i][] = '<div class="'.$cell_css.'"><input type="text" id="'.$labels[$i].'" class="'.$css_class.' jokteantu '.$unique_id.'" name="'.$this->name.'[]" value="'.( isset($values[$i]) ? $values[$i] : $default[$i] ).'" size="'.$size.'" '.$disableme.'/></div>';		
			
		}

		foreach($div as $div_row => $div_value){
			if(is_array($div_value)){
				$new_div[] = "<div class='box_".$div_row."'>".implode("\n", $div_value)."</div>";
			}else{
				$new_div[] = "<div class='box_".$div_row."'>".$div_value."</div>";
			}
		}

		$output = '<div class="mbox_jokteantu" sytle="clear:right;margin-bottom:20px;"><div class="jokteantu_multiple"><div id="'.$unique_id.'">';
		$output.= implode("\n", $new_div);		
		$output.= '</div>'.$disabletext.'</div></div>';
		
		// Retorna HTML
		return $output; 		
	}	
	
}

?>
