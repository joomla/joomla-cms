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
class Downloadkey extends Form
{
	/**
	 * Add the prefix and suffix of the download key before saving it into extra_query
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function save()
	{
		$prefix = $this->input->getString('dlidprefix');
		$sufix = $this->input->getString('dlidsufix');

		$input = $this->input->post->get('jform', array(), 'array');
		$input['extra_query'] = $prefix . $input['extra_query'] . $sufix;
		$this->input->post->set('jform', $input);

		parent::save();
	}
}
