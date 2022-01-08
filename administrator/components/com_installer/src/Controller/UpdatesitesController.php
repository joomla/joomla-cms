<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

/**
 * Installer Update Sites Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       3.4
 */
class UpdatesitesController extends AdminController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $text_prefix = 'COM_INSTALLER_UPDATESITES';

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The Application for the dispatcher
	 * @param   Input                $input    Input
	 *
	 * @since  1.6
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		$this->registerTask('unpublish', 'publish');
		$this->registerTask('publish',   'publish');
		$this->registerTask('delete',    'delete');
		$this->registerTask('rebuild',   'rebuild');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since   4.0.0
	 */
	public function getModel($name = 'Updatesite', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Enable/Disable an extension (if supported).
	 *
	 * @return  void
	 *
	 * @since   3.4
	 *
	 * @throws  \Exception on error
	 */
	public function publish()
	{
		// Check for request forgeries.
		$this->checkToken();

		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('publish' => 1, 'unpublish' => 0);
		$task   = $this->getTask();
		$value  = ArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids))
		{
			throw new \Exception(Text::_('COM_INSTALLER_ERROR_NO_UPDATESITES_SELECTED'), 500);
		}

		// Get the model.
		/** @var \Joomla\Component\Installer\Administrator\Model\UpdatesitesModel $model */
		$model = $this->getModel('Updatesites');

		// Change the state of the records.
		if (!$model->publish($ids, $value))
		{
			throw new \Exception(implode('<br>', $model->getErrors()), 500);
		}

		$ntext = ($value == 0) ? 'COM_INSTALLER_N_UPDATESITES_UNPUBLISHED' : 'COM_INSTALLER_N_UPDATESITES_PUBLISHED';

		$this->setMessage(Text::plural($ntext, count($ids)));

		$this->setRedirect(Route::_('index.php?option=com_installer&view=updatesites', false));
	}

	/**
	 * Deletes an update site (if supported).
	 *
	 * @return  void
	 *
	 * @since   3.6
	 *
	 * @throws  \Exception on error
	 */
	public function delete()
	{
		// Check for request forgeries.
		$this->checkToken();

		$ids = $this->input->get('cid', array(), 'array');

		if (empty($ids))
		{
			throw new \Exception(Text::_('COM_INSTALLER_ERROR_NO_UPDATESITES_SELECTED'), 500);
		}

		// Delete the records.
		$this->getModel('Updatesites')->delete($ids);

		$this->setRedirect(Route::_('index.php?option=com_installer&view=updatesites', false));
	}

	/**
	 * Rebuild update sites tables.
	 *
	 * @return  void
	 *
	 * @since   3.6
	 */
	public function rebuild()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Rebuild the update sites.
		$this->getModel('Updatesites')->rebuild();

		$this->setRedirect(Route::_('index.php?option=com_installer&view=updatesites', false));
	}
}
