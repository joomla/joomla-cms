<?php
/**
 * Element: Group Level
 * Displays a select box of backend group levels
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
 * Group Level Element
 *
 * Available extra parameters:
 * root				The user group to use as root (default = USERS)
 * showroot			Show the root in the list
 * multiple			Multiple options can be selected
 * notregistered	Add an option for 'Not Registered' users
 */
class nnFieldGroupLevel
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		$root = $this->def('root', 'USERS');
		$showroot = $this->def('showroot');
		if (strtoupper($root) == 'USERS' && $showroot == '') {
			$showroot = 0;
		}
		$size = (int) $this->def('size');
		$multiple = $this->def('multiple');
		$show_all = $this->def('show_all');

		$attribs = 'class="inputbox"';

		$groups = $this->getUserGroups();
		$options = array();
		if ($show_all) {
			$option = new stdClass();
			$option->value = -1;
			$option->text = '- '.JText::_('JALL').' -';
			$option->disable = '';
			$options[] = $option;
		}

		foreach ($groups as $group) {
			$option = new stdClass();
			$option->value = $group->id;
			$item_name = $group->title;

			$start = $show_all ? 0 : 1;
			for ($i = $start; $i <= $group->level; $i++) {
				$item_name = '&nbsp;&nbsp;'.$item_name;
			}

			$option->text = $item_name;
			$option->disable = '';
			$options[] = $option;
		}

		require_once JPATH_PLUGINS.'/system/nnframework/helpers/html.php';
		return nnHTML::selectlist($options, $name, $value, $id, $size, $multiple, $attribs, 0);
	}

	protected function getUserGroups()
	{
		// Get a database object.
		$db = JFactory::getDBO();

		// Get the user groups from the database.
		$db->setQuery(
			'SELECT a.id, a.title, a.parent_id AS parent, COUNT(DISTINCT b.id) AS level'.
				' FROM #__usergroups AS a'.
				' LEFT JOIN `#__usergroups` AS b ON a.lft > b.lft AND a.rgt < b.rgt'.
				' GROUP BY a.id'.
				' ORDER BY a.lft ASC'
		);
		$options = $db->loadObjectList();

		return $options;
	}

	function getInput15($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		$root = $this->def('root', 'USERS');
		$showroot = $this->def('showroot');
		if (strtoupper($root) == 'USERS' && $showroot == '') {
			$showroot = 0;
		}
		$multiple = $this->def('multiple');
		$notregistered = $this->def('notregistered');

		$control = $name.'';

		$acl = JFactory::getACL();
		$options = $acl->get_group_children_tree(null, $root, $showroot);
		if ($notregistered) {
			$option = new stdClass();
			$option->value = 0;
			$option->text = JText::_('NN_NOT_LOGGED_IN');
			$option->disable = '';
			array_unshift($options, $option);
		}
		foreach ($options as $i => $option) {
			$item_name = $option->text;

			$padding = 0;
			if (strpos($item_name, '&nbsp; ') === 0 || strpos($item_name, '-&nbsp; ') === 0) {
				$item_name = preg_replace('#^-?&nbsp; #', '', $item_name);
			} else if (strpos($item_name, '.&nbsp;') === 0 || strpos($item_name, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;') === 0) {
				$item_name = preg_replace('#^\.&nbsp;#', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $item_name);
				while (strpos($item_name, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;') === 0) {
					$padding++;
					$item_name = substr($item_name, 36);
				}
				$item_name = preg_replace('#^-&nbsp;#', '', $item_name);
			}
			for ($p = 0; $p < $padding; $p++) {
				$item_name = '&nbsp;&nbsp;'.$item_name;
			}

			$options[$i]->text = $item_name;
		}

		if ($multiple) {
			$control .= '[]';

			if (!is_array($value)) {
				$value = explode(',', $value);
			}

			if (in_array(29, $value)) {
				$value[] = 18;
				$value[] = 19;
				$value[] = 20;
				$value[] = 21;
			}
			if (in_array(30, $value)) {
				$value[] = 23;
				$value[] = 24;
				$value[] = 25;
			}
		}

		require_once JPATH_PLUGINS.'/system/nnframework/helpers/html.php';
		return nnHTML::selectlist($options, $control, $value, $id, 5, $multiple, 0, 1);
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_GroupLevel extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'GroupLevel';

	protected function getInput()
	{
		$this->_nnfield = new nnFieldGroupLevel();
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}