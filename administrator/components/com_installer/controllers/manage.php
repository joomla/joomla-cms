<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Installer Manage Controller
 *
 * @since  1.6
 */
class InstallerControllerManage extends JControllerLegacy
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('unpublish', 'publish');
		$this->registerTask('publish',   'publish');
	}

	/**
	 * Enable/Disable an extension (if supported).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function publish()
	{
		// Check for request forgeries.
		$this->checkToken();

		$ids    = (array) $this->input->get('cid', array(), 'int');
		$values = array('publish' => 1, 'unpublish' => 0);
		$task   = $this->getTask();
		$value  = ArrayHelper::getValue($values, $task, 0, 'int');

		// Remove zero values resulting from input filter
		$ids = array_filter($ids);

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('COM_INSTALLER_ERROR_NO_EXTENSIONS_SELECTED'));
		}
		else
		{
			// Get the model.
			/** @var InstallerModelManage $model */
			$model = $this->getModel('manage');

			// Change the state of the records.
			if (!$model->publish($ids, $value))
			{
				JError::raiseWarning(500, implode('<br />', $model->getErrors()));
			}
			else
			{
				if ($value == 1)
				{
					$ntext = 'COM_INSTALLER_N_EXTENSIONS_PUBLISHED';
				}
				elseif ($value == 0)
				{
					$ntext = 'COM_INSTALLER_N_EXTENSIONS_UNPUBLISHED';
				}

				$this->setMessage(JText::plural($ntext, count($ids)));
			}
		}

		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=manage', false));
	}

	/**
	 * Remove an extension (Uninstall).
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function remove()
	{
		// Check for request forgeries.
		$this->checkToken();

		$eid = (array) $this->input->get('cid', array(), 'int');

		// Remove zero values resulting from input filter
		$eid = array_filter($eid);

		if (!empty($eid))
		{
			/** @var InstallerModelManage $model */
			$model = $this->getModel('manage');

			$model->remove($eid);
		}

		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=manage', false));
	}

	/**
	 * Refreshes the cached metadata about an extension.
	 *
	 * Useful for debugging and testing purposes when the XML file might change.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function refresh()
	{
		// Check for request forgeries.
		$this->checkToken();

		$uid = (array) $this->input->get('cid', array(), 'int');

		// Remove zero values resulting from input filter
		$uid = array_filter($uid);

		if (!empty($uid))
		{
			/** @var InstallerModelManage $model */
			$model = $this->getModel('manage');

			$model->refresh($uid);
		}

		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=manage', false));
	}
}
