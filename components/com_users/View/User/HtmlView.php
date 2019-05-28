<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\View\User;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Users\Site\Model\UserModel;

/**
 * View class for Single User
 *
 * @since  4.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The Author data
	 *
	 * @var    object
	 * @since  1.0.0
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var    Joomla\Registry\Registry
	 * @since  1.0.0
	 */
	protected $state;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  The template file to include
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		$authorId = Factory::getApplication()->input->getInt('id');

		/** @var UserModel $model */
		$model       = $this->getModel();
		$this->state = $model->getState();
		$this->item  = $model->getItem($authorId);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		parent::display($tpl);
	}
}
