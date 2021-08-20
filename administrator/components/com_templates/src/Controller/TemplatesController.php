<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Utilities\ArrayHelper;

/**
 * Template templates list controller class.
 *
 * @since  1.6
 */
class TemplatesController extends AdminController
{
	/**
	 * Method to clone and existing template style.
	 *
	 * @return  void
	 */
	public function duplicate()
	{
		// Check for request forgeries
		$this->checkToken();

		$pks = $this->input->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				throw new \Exception(Text::_('COM_TEMPLATES_NO_TEMPLATE_SELECTED'));
			}

			$pks = ArrayHelper::toInteger($pks);

			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Text::_('COM_TEMPLATES_SUCCESS_DUPLICATED'));
		}
		catch (\Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
		}

		$this->setRedirect('index.php?option=com_templates&view=templates');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  BaseDatabaseModel
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Style', $prefix = 'Administrator', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Method to set the home template for a client.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setDefault()
	{
		// Check for request forgeries
		$this->checkToken();

		$pks = $this->input->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				throw new \Exception(Text::_('COM_TEMPLATES_NO_TEMPLATE_SELECTED'));
			}

			$pks = ArrayHelper::toInteger($pks);

			// Pop off the first element.
			$id = array_shift($pks);

			/** @var \Joomla\Component\Templates\Administrator\Model\StyleModel $model */
			$model = $this->getModel();
			$model->setHome($id);
			$this->setMessage(Text::_('COM_TEMPLATES_SUCCESS_HOME_SET'));
		}
		catch (\Exception $e)
		{
			$this->setMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_templates&view=templates');
	}

	/**
	 * Method to unset the default template for a client and for a language
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function unsetDefault()
	{
		// Check for request forgeries
		$this->checkToken('request');

		$pks = $this->input->get->get('cid', array(), 'array');
		$pks = ArrayHelper::toInteger($pks);

		try
		{
			if (empty($pks))
			{
				throw new \Exception(Text::_('COM_TEMPLATES_NO_TEMPLATE_SELECTED'));
			}

			// Pop off the first element.
			$id = array_shift($pks);

			/** @var \Joomla\Component\Templates\Administrator\Model\StyleModel $model */
			$model = $this->getModel();
			$model->unsetHome($id);
			$this->setMessage(Text::_('COM_TEMPLATES_SUCCESS_HOME_UNSET'));
		}
		catch (\Exception $e)
		{
			$this->setMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_templates&view=templates');
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function allowEdit()
	{
		return $this->app->getIdentity()->authorise('core.admin');
	}

	/**
	 * Method for copying the template.
	 *
	 * @return  boolean     true on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @deprecated 5.0.0
	 */
	public function forkTemplate()
	{
		// Check for request forgeries
		$this->checkToken();

		$app = $this->app;
		$this->input->set('installtype', 'folder');
		$newName    = $this->input->get('new_name');
		$newNameRaw = $this->input->get('new_name', null, 'string');
		$templateID = $this->input->getInt('id', 0);
		$file       = $this->input->get('file');

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

		$this->setRedirect('index.php?option=com_templates&view=template&id=' . $templateID . '&file=' . $file);

		/* @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model = $this->getModel('Template', 'Administrator');
		$model->setState('new_name', $newName);
		$model->setState('tmp_prefix', uniqid('template_copy_'));
		$model->setState('to_path', $app->get('tmp_path') . '/' . $model->getState('tmp_prefix'));

		// Process only if we have a new name entered
		if (strlen($newName) > 0)
		{
			if (!$this->app->getIdentity()->authorise('core.create', 'com_templates'))
			{
				// User is not authorised to delete
				$this->setMessage(Text::_('COM_TEMPLATES_ERROR_CREATE_NOT_PERMITTED'), 'error');

				return false;
			}

			// Set FTP credentials, if given
			ClientHelper::setCredentialsFromRequest('ftp');

			// Check that new name is valid
			if (($newNameRaw !== null) && ($newName !== $newNameRaw))
			{
				$this->setMessage(Text::_('COM_TEMPLATES_ERROR_INVALID_TEMPLATE_NAME'), 'error');

				return false;
			}

			// Check that new name doesn't already exist
			if (!$model->checkNewName())
			{
				$this->setMessage(Text::_('COM_TEMPLATES_ERROR_DUPLICATE_TEMPLATE_NAME'), 'error');

				return false;
			}

			// Check that from name does exist and get the folder name
			$fromName = $model->getFromName();

			if (!$fromName)
			{
				$this->setMessage(Text::_('COM_TEMPLATES_ERROR_INVALID_FROM_NAME'), 'error');

				return false;
			}

			// Call model's copy method
			if (!$model->copy())
			{
				$this->setMessage(Text::_('COM_TEMPLATES_ERROR_COULD_NOT_COPY'), 'error');

				return false;
			}

			// Call installation model
			$this->input->set('install_directory', $app->get('tmp_path') . '/' . $model->getState('tmp_prefix'));

			/** @var \Joomla\Component\Installer\Administrator\Model\InstallModel $installModel */
			$installModel = $this->app->bootComponent('com_installer')
				->getMVCFactory()->createModel('Install', 'Administrator');
			$this->app->getLanguage()->load('com_installer');

			if (!$installModel->install())
			{
				$this->setMessage(Text::_('COM_TEMPLATES_ERROR_COULD_NOT_INSTALL'), 'error');

				return false;
			}

			$this->setMessage(Text::sprintf('COM_TEMPLATES_COPY_SUCCESS', $newName));
			$model->cleanup();

			return true;
		}

		return false;
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getExtensionLayouts()
	{
		// Check for request forgeries
		$this->checkToken();

		$app = $this->app;
		$templateID = $this->input->getInt('id', 0);

		// Access check.
//		if (!$this->allowEdit())
//		{
//			$app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');
//
//			return false;
//		}

//		$this->setRedirect('index.php?option=com_templates&view=template&id=' . $templateID . '&file=' . $file);

		/* @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model = $this->getModel('Templates', 'Administrator');

		$overrides = $model->getOverridesList($templateID);

		$app->mimeType = 'application/json';
		$app->charSet = 'utf-8';
		$app->setHeader('Content-Type', $app->mimeType . '; charset=' . $app->charSet);
		$app->sendHeaders();

		try
		{
			echo new JsonResponse($overrides);
		}
		catch (\Exception $e)
		{
			echo $e;
		}

		$this->app->close();
	}
}
