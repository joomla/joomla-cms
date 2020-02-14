<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR . '/components/com_sppagebuilder/models/page.php';

/**
 * Content Component Article Model
 *
 * @since  1.5
 */
class SppagebuilderModelForm extends SppagebuilderModelPage
{
	protected $_context = 'com_sppagebuilder.page';
	protected $_item = array();

	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		$pageId = $app->input->getInt('id');
		$this->setState('page.id', $pageId);

		$user = JFactory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_sppagebuilder')) && (!$user->authorise('core.edit', 'com_sppagebuilder')))
		{
			$this->setState('filter.published', 1);
		}

		$this->setState('filter.language', JLanguageMultilang::isEnabled());
	}

	public function getForm($data = array(), $loadData = true) {
			return parent::getForm();
	}

	public function getItem( $pageId = null )
	{
		$user = JFactory::getUser();

		$pageId = (!empty($pageId))? $pageId : (int)$this->getState('page.id');

		if (!isset($this->_item[$pageId]))
		{
			try
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select('a.*')
					->from('#__sppagebuilder as a')
					->where('a.id = ' . (int) $pageId);

				$query->select('l.title AS language_title')
					->leftJoin( $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

				$query->select('ua.name AS author_name')
					->leftJoin('#__users AS ua ON ua.id = a.created_by');

				// Filter by published state.
				$published = $this->getState('filter.published');

				if (is_numeric($published))
				{
					$query->where('a.published = ' . (int) $published);
				}
				elseif ($published === '')
				{
					$query->where('(a.published IN (0, 1))');
				}

				if ($this->getState('filter.language'))
				{
					$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
				}

				$db->setQuery($query);
				$data = $db->loadObject();

				if (empty($data)) {
					return JError::raiseError(404, JText::_('COM_SPPAGEBUILDER_ERROR_PAGE_NOT_FOUND'));
				}

				if ($access = $this->getState('filter.access')) {
					$data->access_view = true;
				}else{
					$user = JFactory::getUser();
					$groups = $user->getAuthorisedViewLevels();

					 $data->access_view = in_array($data->access, $groups);
				}

				$this->_item[$pageId] = $data;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404 )
				{
					JError::raiseError(404, $e->getMessage());
				}
				else
				{
					$this->setError($e);
					$this->_item[$pageId] = false;
				}
			}
		}

		return $this->_item[$pageId];
	}
}
