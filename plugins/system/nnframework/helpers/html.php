<?php
/**
 * nnHTML
 * extra JHTML functions
 *
 * @package			NoNumber Framework
 * @version			12.6.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$scripts = array();
$scripts[] = "if ( typeof( window['nn_texts'] ) == \"undefined\" ) { nn_texts = new Array(); }";
$scripts[] = "nn_texts['selectall'] = '".addslashes(JText::_('NN_SELECT_ALL'))."';";
$scripts[] = "nn_texts['unselectall'] = '".addslashes(JText::_('NN_UNSELECT_ALL'))."';";
$scripts[] = "nn_texts['total'] = '".addslashes(JText::_('NN_TOTAL'))."';";
$scripts[] = "nn_texts['selected'] = '".addslashes(JText::_('NN_SELECTED'))."';";
$scripts[] = "nn_texts['unselected'] = '".addslashes(JText::_('NN_UNSELECTED'))."';";
$scripts[] = "nn_texts['maximize'] = '".addslashes(JText::_('NN_MAXIMIZE'))."';";
$scripts[] = "nn_texts['minimize'] = '".addslashes(JText::_('NN_MINIMIZE'))."';";
$document->addScriptDeclaration(implode('', $scripts));

/**
 * nnHTML
 */
class nnHTML
{
	public static $_version = '12.6.4';

	static function selectlist(&$options, $name, $value, $id, $size = 0, $multiple = 0, $attribs = '', $j15 = 0)
	{
		if (!$size) {
			$size = ((count($options) > 10) ? 10 : count($options));
		}
		$attribs .= ' size="'.$size.'"';
		if ($multiple) {
			if (!is_array($value)) {
				$value = explode(',', $value);
			}
			$attribs .= ' multiple="multiple"';
			if (substr($name, -2) != '[]') {
				$name .= '[]';
			}
		}

		foreach ($options as $i => $option) {
			$option = (object) $option;
			if (isset($option->text)) {
				$option->text = str_replace(array('&nbsp;', '&#160;'), '___', $option->text);
				$options[$i] = $option;
			}
		}

		$class = 'inputbox';
		if ($multiple) {
			$class .= ' nn_multiselect';
		}

		$html = JHtml::_('select.genericlist', $options, $name, 'class="'.trim($class).'" '.trim($attribs), 'value', 'text', $value, $id);
		$html = str_replace('___', '&nbsp;', $html);

		$links = array();
		if ($multiple) {
			$document = JFactory::getDocument();
			$document->addScript(JURI::root(true).'/plugins/system/nnframework/fields/multiselect/script.js?v='.self::$_version);
			$document->addStyleSheet(JURI::root(true).'/plugins/system/nnframework/fields/multiselect/style.css?v='.self::$_version);
		} else if ($size && count($options) > $size) {
			$links[] = '<a href="javascript://" onclick="nnScripts.toggleSelectListSize(\''.$id.'\');" id="toggle_'.$id.'">'
				.'<span class="show">'.JText::_('NN_MAXIMIZE').'</span>'
				.'<span class="hide" style="display:none";>'.JText::_('NN_MINIMIZE').'</span>'
				.'</a>';
		}
		if (!empty($links)) {
			JHtml::_('behavior.mootools');
			$document = JFactory::getDocument();
			$document->addScript(JURI::root(true).'/plugins/system/nnframework/js/script.js?v='.self::$_version);
			$html = implode(' - ', $links).'<br />'.$html;
		}
		if (!$j15) {
			$html = '<fieldset class="radio" id="'.$id.'_fieldset">'.$html.'</fieldset>';
		}

		return preg_replace('#>\[\[\:(.*?)\:\]\]#si', ' style="\1">', $html);
	}
}