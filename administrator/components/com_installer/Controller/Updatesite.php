<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Controller\Form;

/**
 * Controller for a single contact
 *
 * @since  __DEPLOY_VERSION__
 */
class Updatesite extends Form
{
	/**
	 * Edit update site.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function edit()
	{
		$model = $this->getModel('updatesites');

		// Get the id of the UpdateSite that we are trying to edit
		$recordId = $this->input->post->get('cid', array(), 'array')[0];

		// Get the list of the Joomla Core UpdateSites
		$joomlaUpdateSitesIds = $model->getJoomlaUpdateSitesIds(0);

		if (in_array($recordId, $joomlaUpdateSitesIds))
		{
			$this->setMessage(
				\JText::sprintf(
					'COM_INSTALLER_MSG_UPDATESITES_DELETE_CANNOT_EDIT',
					array_shift(
						$model->getJoomlaUpdateSitesNames(
							array($recordId)
						)
					)->name
				),
				'error'
			);

			$this->setRedirect(
				\JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		parent::edit();
	}
}
