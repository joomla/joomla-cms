<?php
/**
 * @package     Joomla.API
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\MVC\Controller\Exception\InstallLanguage;
use Tobscure\JsonApi\Exception\InvalidParameterException;

/**
 * The installer controller
 *
 * @since  4.0.0
 */
class LanguagesController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'languages';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'languages';

	/**
	 * Install a language.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function install()
	{
		$data = $this->input->get('data', json_decode($this->input->json->getRaw(), true), 'array');

		if (!isset($data['package']))
		{
			throw new InvalidParameterException("Invalid param 'package'");
		}

		/** @var \Joomla\Component\Languages\Administrator\Model\LanguagesModel $model */
		$model = $this->getModel($this->contentType, '', ['ignore_request' => true]);

		if (!$model)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
		}

		$package = null;

		foreach ($model->getItems() as $item)
		{
			if ($item->element == $data['package'])
			{
				$package = $item;
				break;
			}
		}

		if ($package === null)
		{
			throw new InvalidParameterException("Invalid param 'package'. Package not found");
		}

		/** @var \Joomla\Component\Installer\Administrator\Model\InstallModel $model */
		$model = $this->getModel('install', '', ['ignore_request' => true]);

		if (!$model)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
		}

		$app = Factory::getApplication();
		$app->input->set('installtype', 'url');
		$app->input->set('install_url', $package->detailsurl);

		$result = $model->install();

		if ($result !== true)
		{
			$msg = Text::sprintf('COM_INSTALLER_INSTALL_ERROR', Text::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package->type)));

			throw new InstallLanguage($msg);
		}

		return $this;
	}
}
