<?php
/**
 * Element: TypesFC
 * Displays a multiselectbox of available Flexicontent Types
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
 * TypesFC Element
 */
class nnFieldTypesFC
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_flexicontent/admin.flexicontent.php')) {
			return 'Flexicontent files not found...';
		}

		$db = JFactory::getDBO();
		$tables = $db->getTableList();
		if (!in_array($db->getPrefix().'flexicontent_cats_item_relations', $tables)) {
			return 'Flexicontent category-item relations table not found in database...';
		}

		$size = (int) $this->def('size');
		$multiple = $this->def('multiple');

		if (!is_array($value)) {
			$value = explode(',', $value);
		}

		$query = $db->getQuery(true);
		$query->select('t.id, t.name');
		$query->from('#__flexicontent_types AS t');
		$query->where('t.published = 1');
		$list = $db->loadObjectList();

		// assemble items to the array
		$options = array();
		foreach ($list as $item) {
			$item_name = preg_replace('#^((&nbsp;)*)- #', '\1', str_replace('&#160;', '&nbsp;', $item->name));
			$options[] = JHtml::_('select.option', $item->id, $item_name, 'value', 'text', 0);
		}

		require_once JPATH_PLUGINS.'/system/nnframework/helpers/html.php';
		return nnHTML::selectlist($options, $name, $value, $id, $size, $multiple, '');
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_TypesFC extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'TypesFC';

	protected function getInput()
	{
		$this->_nnfield = new nnFieldTypesFC();
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}