<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\View\Users;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Registry\Registry;
use JPagination;

/**
 * View class for Users
 *
 * @since   __deploy_version__
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var     array
	 * @since   __deploy_version__
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var     JPagination
	 * @since   __deploy_version__
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var     object
	 * @since   __deploy_version__
	 */
	protected $state;

	/**
	 * The page parameters
	 *
	 * @var    Registry|null
	 * @since   __deploy_version__
	 */
	protected $params = null;

	/**
	 * @param   null  $tpl  Params
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   __deploy_version__
	 */
	public function display($tpl = null)
	{
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Get params for active menu
		$this->params = Factory::getApplication()->getMenu()->getActive()->getParams();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		parent::display($tpl);
	}
}
