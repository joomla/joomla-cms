<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Contact Component Controller
 *
 * @since  1.5
 */
class ContactController extends JControllerLegacy
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *                          Recognized key values include 'name', 'default_task', 'model_path', and
	 *                          'view_path' (this list is not meant to be comprehensive).
	 *
	 * @since   3.7.0
	 */
	public function __construct($config = array())
	{
		$this->input = JFactory::getApplication()->input;

		// Contact frontpage Editor contacts proxying:
		if ($this->input->get('view') === 'contacts' && $this->input->get('layout') === 'modal')
		{
			JHtml::_('stylesheet', 'system/adminlist.css', array(), true);
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}

		parent::__construct($config);
	}

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = array())
	{
		if (JFactory::getApplication()->getUserState('com_contact.contact.data') === null)
		{
			$cachable = true;
		}

		// Set the default view name and format from the Request.
		$vName = $this->input->get('view', 'categories');
		$this->input->set('view', $vName);

		$safeurlparams = array('catid' => 'INT', 'id' => 'INT', 'cid' => 'ARRAY', 'year' => 'INT', 'month' => 'INT',
			'limit' => 'UINT', 'limitstart' => 'UINT', 'showall' => 'INT', 'return' => 'BASE64', 'filter' => 'STRING',
			'filter_order' => 'CMD', 'filter_order_Dir' => 'CMD', 'filter-search' => 'STRING', 'print' => 'BOOLEAN',
			'lang' => 'CMD');

		parent::display($cachable, $safeurlparams);

		return $this;
	}
}
