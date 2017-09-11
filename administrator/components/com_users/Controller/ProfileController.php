<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Help\Help;
use Joomla\CMS\Response\JsonResponse;

/**
 * Profile controller class for Users.
 *
 * @since  3.5
 */
class ProfileController extends BaseController
{
	/**
	 * Returns the updated options for help site selector
	 *
	 * @return  void
	 *
	 * @since   3.5
	 * @throws  \Exception
	 */
	public function gethelpsites()
	{
		jimport('joomla.filesystem.file');

		// Set FTP credentials, if given
		\JClientHelper::setCredentialsFromRequest('ftp');

		if (($data = file_get_contents('https://update.joomla.org/helpsites/helpsites.xml')) === false)
		{
			throw new \Exception(\JText::_('COM_CONFIG_ERROR_HELPREFRESH_FETCH'), 500);
		}
		elseif (!\JFile::write(JPATH_ADMINISTRATOR . '/help/helpsites.xml', $data))
		{
			throw new \Exception(\JText::_('COM_CONFIG_ERROR_HELPREFRESH_ERROR_STORE'), 500);
		}

		$options = array_merge(
			array(
				\JHtml::_('select.option', '', \JText::_('JOPTION_USE_DEFAULT'))
			),
			Help::createSiteList(JPATH_ADMINISTRATOR . '/help/helpsites.xml')
		);

		echo new JsonResponse($options);

		$this->app->close();
	}
}
