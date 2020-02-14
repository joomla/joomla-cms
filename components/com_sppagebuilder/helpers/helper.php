<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2015 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderHelperSite {

	public static function loadLanguage() {
        $lang = JFactory::getLanguage();

				$app = JFactory::getApplication();
				$template = $app->getTemplate();

        // Load component language
        $lang->load('com_sppagebuilder', JPATH_ADMINISTRATOR, null, true);

        // Load template language file
        $lang->load('tpl_' . $template, JPATH_SITE, null, true);

        require_once JPATH_ROOT .'/administrator/components/com_sppagebuilder/helpers/language.php';
    }

	public static function getPaddingMargin($main_value, $type){
		$css = '';
		$pos = array( 'top', 'right', 'bottom', 'left' );
		if(trim($main_value) != ""){
				$values = explode(' ',  $main_value);
				foreach($values as $key => $value){
						if(!empty(trim($value))){
								$css .= $type.'-'.$pos[$key].': '.$value.';';
						}
				}
		}

		return $css;
	}
}
