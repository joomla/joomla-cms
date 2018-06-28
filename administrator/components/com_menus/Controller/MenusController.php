<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Menus\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;

/**
 * The Menu List Controller
 *
 * @since  1.6
 */
class MenusController extends BaseController
{
	/**
	 * Display the view
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static   This object to support chaining.
	 *
	 * @since   1.6
	 */
	public function display($cachable = false, $urlparams = false)
	{
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Menu', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Remove an item.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function delete()
	{
		// Check for request forgeries
		\JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$user = \JFactory::getUser();
		$app  = \JFactory::getApplication();
		$cids = (array) $this->input->get('cid', array(), 'array');

		if (count($cids) < 1)
		{
			$this->setMessage(Text::_('COM_MENUS_NO_MENUS_SELECTED'), 'warning');
		}
		else
		{
			// Access checks.
			foreach ($cids as $i => $id)
			{
				if (!$user->authorise('core.delete', 'com_menus.menu.' . (int) $id))
				{
					// Prune items that you can't change.
					unset($cids[$i]);
					$app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 'error');
				}
			}

			if (count($cids) > 0)
			{
				// Get the model.
				/* @var \Joomla\Component\Menus\Administrator\Model\MenuModel $model */
				$model = $this->getModel();

				// Make sure the item ids are integers
				$cids = ArrayHelper::toInteger($cids);

				// Remove the items.
				if (!$model->delete($cids))
				{
					$this->setMessage($model->getError(), 'error');
				}
				else
				{
					$this->setMessage(Text::plural('COM_MENUS_N_MENUS_DELETED', count($cids)));
				}
			}
		}

		$this->setRedirect('index.php?option=com_menus&view=menus');
	}

	/**
	 * Rebuild the menu tree.
	 *
	 * @return  bool    False on failure or error, true on success.
	 *
	 * @since   1.6
	 */
	public function rebuild()
	{
		\JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$this->setRedirect('index.php?option=com_menus&view=menus');

		/* @var \Joomla\Component\Menus\Administrator\Model\ItemModel $model */
		$model = $this->getModel('Item');

		if ($model->rebuild())
		{
			// Reorder succeeded.
			$this->setMessage(Text::_('JTOOLBAR_REBUILD_SUCCESS'));

			return true;
		}
		else
		{
			// Rebuild failed.
			$this->setMessage(Text::sprintf('JTOOLBAR_REBUILD_FAILED', $model->getError()), 'error');

			return false;
		}
	}

	/**
	 * Temporary method. This should go into the 1.5 to 1.6 upgrade routines.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function resync()
	{
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$parts = null;

		try
		{
			$query->select('element, extension_id')
				->from('#__extensions')
				->where('type = ' . $db->quote('component'));
			$db->setQuery($query);

			$components = $db->loadAssocList('element', 'extension_id');
		}
		catch (\RuntimeException $e)
		{
			$this->setMessage($e->getMessage(), 'warning');

			return;
		}

		// Load all the component menu links
		$query->select($db->quoteName('id'))
			->select($db->quoteName('link'))
			->select($db->quoteName('component_id'))
			->from('#__menu')
			->where($db->quoteName('type') . ' = ' . $db->quote('component.item'));
			$db->setQuery($query);

		try
		{
			$items = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			$this->setMessage($e->getMessage(), 'warning');

			return;
		}

		foreach ($items as $item)
		{
			// Parse the link.
			parse_str(parse_url($item->link, PHP_URL_QUERY), $parts);

			// Tease out the option.
			if (isset($parts['option']))
			{
				$option = $parts['option'];

				// Lookup the component ID
				if (isset($components[$option]))
				{
					$componentId = $components[$option];
				}
				else
				{
					// Mismatch. Needs human intervention.
					$componentId = -1;
				}

				// Check for mis-matched component id's in the menu link.
				if ($item->component_id != $componentId)
				{
					// Update the menu table.
					$log = "Link $item->id refers to $item->component_id, converting to $componentId ($item->link)";
					echo "<br>$log";

					$query->clear();
					$query->update('#__menu')
						->set('component_id = ' . $componentId)
						->where('id = ' . $item->id);

					try
					{
						$db->setQuery($query)->execute();
					}
					catch (\RuntimeException $e)
					{
						$this->setMessage($e->getMessage(), 'warning');

						return;
					}
				}
			}
		}
	}
}
