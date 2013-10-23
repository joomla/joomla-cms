<?php
/**
 * @package    PatchTester
 *
 * @copyright  Copyright (C) 2011 - 2012 Ian MacLennan, Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * View class for a list of pull requests.
 *
 * @package  PatchTester
 * @since    1.0
 */
class PatchtesterViewPulls extends JViewLegacy
{
	/**
	 * Array of open pull requests
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $items;

	/**
	 * Object containing data about applied patches
	 *
	 * @var    object
	 * @since  1.0
	 */
	protected $patches;

	/**
	 * State object
	 *
	 * @var    JRegistry
	 * @since  1.0
	 */
	protected $state;

	/**
	 * Pagination object
	 *
	 * @var    JPagination
	 * @since  2.0
	 */
	protected $pagination;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @see     fetch()
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		// TODO: move the check
		$checkErrs = array();

		if (!extension_loaded('openssl'))
		{
			$checkErrs[] = 'The OpenSSL extension must be installed and enabled in your php.ini';
		}

		if (!in_array('https', stream_get_wrappers()))
		{
			$checkErrs[] = 'https wrappers must be enabled';
		}

		if (count($checkErrs))
		{
			$application = JFactory::getApplication();

			$application->enqueueMessage('Your system does not meet the requirements to run the PullTester extension:', 'error');

			foreach ($checkErrs as $error)
			{
				$application->enqueueMessage($error, 'error');
			}

			return $this;
		}

		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->patches    = $this->get('AppliedPatches');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		$errors = $this->get('Errors');

		if (count($errors))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_PATCHTESTER'), 'patchtester');
		JToolbarHelper::custom('purge', 'delete.png', 'delete_f2.png', 'COM_PATCHTESTER_PURGE_CACHE', false);
		JToolBarHelper::preferences('com_patchtester');

		JFactory::getDocument()->addStyleDeclaration(
			'.icon-48-patchtester {background-image: url(components/com_patchtester/assets/images/icon-48-patchtester.png);}'
		);
	}
}
