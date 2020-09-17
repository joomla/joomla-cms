<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Controller;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Controller\Mixin\CustomACL;
use FOF30\Controller\DataController;
use Joomla\CMS\Language\Text;
use RuntimeException;

class Profiles extends DataController
{
	use CustomACL;

	/**
	 * Imports an exported profile .json file
	 */
	public function import()
	{
		$this->csrfProtection();

		if (!$this->container->platform->authorise('akeeba.configure', 'com_akeeba'))
		{
			throw new RuntimeException(\Joomla\CMS\Language\Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		/** @var \Akeeba\Backup\Admin\Model\Profiles $model */
		$model       = $this->getModel();

		// Get some data from the request
		$file = $this->input->files->get('importfile', array(), 'array');

		if (!isset($file['name']))
		{
			$this->setRedirect('index.php?option=com_akeeba&view=Profiles', \Joomla\CMS\Language\Text::_('MSG_UPLOAD_INVALID_REQUEST'), 'error');

			return;
		}

		// Load the file data
		$data = @file_get_contents($file['tmp_name']);
		@unlink($file['tmp_name']);

		// JSON decode
		$data = json_decode($data, true);

		// Import
		$message     = \Joomla\CMS\Language\Text::_('COM_AKEEBA_PROFILES_MSG_IMPORT_COMPLETE');
		$messageType = null;

		try
		{
			$model->reset()->import($data);
		}
		catch (RuntimeException $e)
		{
			$message     = $e->getMessage();
			$messageType = 'error';
		}

		// Redirect back to the main page
		$this->setRedirect('index.php?option=com_akeeba&view=Profiles', $message, $messageType);
	}

	/**
	 * Enable the Quick Icon for a record
	 *
	 * @since   6.1.2
	 * @throws  \Exception
	 */
	public function quickicon_publish()
	{
		$this->setQuickIcon(1);
	}

	/**
	 * Disable the Quick Icon for a record
	 *
	 * @since   6.1.2
	 * @throws  \Exception
	 */
	public function quickicon_unpublish()
	{
		$this->setQuickIcon(0);
	}

	/**
	 * Sets the Quick Icon status for the record.
	 *
	 * @param   int|bool  $published  Should this profile have a Quick Icon?
	 *
	 * @return  void
	 * @throws  \Exception
	 *
	 * @since   6.1.2
	 */
	private function setQuickIcon($published)
	{
		// CSRF prevention
		$this->csrfProtection();

		/** @var \Akeeba\Backup\Admin\Model\Profiles $model */
		$model = $this->getModel()->savestate(false);
		$ids   = $this->getIDsFromRequest($model, false);
		$error = false;

		try
		{
			$status = true;

			foreach ($ids as $id)
			{
				$model->find($id);
				$model->save([
					'quickicon' => $published ? 1 : 0
				]);
			}
		}
		catch (\Exception $e)
		{
			$status = false;
			$error  = $e->getMessage();
		}

		// Redirect
		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $error, 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
	}
}
