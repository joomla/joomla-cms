<?php
/**
 * Element: Slide
 * Element to create a new slide pane
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

/**
 * Slide Element
 */
class nnFieldSlide
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		// Load common functions
		require_once JPATH_PLUGINS.'/system/nnframework/helpers/text.php';

		$this->params = $params;

		$label = $this->def('label');
		$description = $this->def('description');
		$lang_file = $this->def('language_file');
		$show_apply = $this->def('show_apply');

		$html = '</td></tr></table></div></div>';
		$html .= '<div class="panel"><h3 class="jpane-toggler title" id="advanced-page"><span>';
		$html .= NNText::html_entity_decoder(JText::_($label));
		$html .= '</span></h3>';
		$html .= '<div class="jpane-slider content"><table width="100%" class="paramlist admintable" cellspacing="1"><tr><td colspan="2" class="paramlist_value">';

		if ($description) {
			// variables
			$v1 = $this->def('var1');
			$v2 = $this->def('var2');
			$v3 = $this->def('var3');
			$v4 = $this->def('var4');
			$v5 = $this->def('var5');

			$description = NNText::html_entity_decoder(trim(JText::sprintf($description, $v1, $v2, $v3, $v4, $v5)));
		}

		if ($lang_file) {
			jimport('joomla.filesystem.file');

			// Include extra language file
			$language = JFactory::getLanguage();
			$lang = str_replace('_', '-', $language->getTag());

			$inc = '';
			$lang_path = 'language/'.$lang.'/'.$lang.'.'.$lang_file.'.inc.php';
			if (JFile::exists(JPATH_ADMINISTRATOR.'/'.$lang_path)) {
				$inc = JPATH_ADMINISTRATOR.'/'.$lang_path;
			} else if (JFile::exists(JPATH_SITE.'/'.$lang_path)) {
				$inc = JPATH_SITE.'/'.$lang_path;
			}
			if (!$inc && $lang != 'en-GB') {
				$lang = 'en-GB';
				$lang_path = 'language/'.$lang.'/'.$lang.'.'.$lang_file.'.inc.php';
				if (JFile::exists(JPATH_ADMINISTRATOR.'/'.$lang_path)) {
					$inc = JPATH_ADMINISTRATOR.'/'.$lang_path;
				} else if (JFile::exists(JPATH_SITE.'/'.$lang_path)) {
					$inc = JPATH_SITE.'/'.$lang_path;
				}
			}
			if ($inc) {
				include $inc;
			}
		}

		if ($description) {
			$description = str_replace('span style="font-family:monospace;"', 'span class="nn_code"', $description);
			if ($description['0'] != '<') {
				$description = '<p>'.$description.'</p>';
			}
			$class = 'panel nn_panel nn_panel_description';
			$html .= '<div class="'.$class.'"><div class="nn_block nn_title">';
			if ($show_apply) {
				$apply_button = '<a href="#" onclick="submitbutton( \'apply\' );" title="'.JText::_('Apply').'"><img align="right" border="0" alt="'.JText::_('Apply').'" src="images/tick.png"/></a>';
				$html .= $apply_button;
			}
			$html .= $description;
			$html .= '<div style="clear: both;"></div></div></div>';
		}

		return $html;
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_Slide extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'Slide';

	protected function getLabel()
	{
		return;
	}

	protected function getInput()
	{
		$this->_nnfield = new nnFieldSlide();
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}