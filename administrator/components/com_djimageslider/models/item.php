<?php
/**
 * @version $Id$
 * @package DJ-ImageSlider
 * @subpackage DJ-ImageSlider Component
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 *
 * DJ-ImageSlider is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-ImageSlider is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-ImageSlider. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

class DJImageSliderModelItem extends JModelAdmin
{
	public function getTable($type = 'Item', $prefix = 'DJImageSliderTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true)
	{
		jimport('joomla.form.form');
		JForm::addFieldPath('JPATH_ADMINISTRATOR/components/com_djcatalog2/models/fields');

		// Get the form.
		$form = $this->loadForm('com_djimageslider.item', 'item', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		/* not implemented yet
		// Modify the form based on access controls.
		if (!$this->canEditState((object) $data)) {
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}*/

		return $form;
	}
	
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_djimageslider.edit.item.data', array());

		if (empty($data)) {
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('item.id') == 0) {
				$app = JFactory::getApplication();
				$data->set('catid', $app->input->getInt('catid', $app->getUserState('com_djimageslider.items.filter.category')));
			}
		}

		return $data;
	}
	
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= JFilterOutput::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = JFilterOutput::stringURLSafe($table->title);
		}
		
		// Set the publish date to now
		if($table->published == 1 && intval($table->publish_up) == 0) {
			$table->publish_up = $date->toSql();
		}
		
		if(empty($table->publish_down)) {
			$table->publish_down = JFactory::getDbo()->getNullDate();
		}
		
		/*
		if (empty($table->id)) {

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__djimageslider');
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
		*/
	}
	
	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'catid = '.(int) $table->catid;

		return $condition;
	}
	
}
