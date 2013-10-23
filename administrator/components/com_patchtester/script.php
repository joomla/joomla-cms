<?php
/**
 * @package    PatchTester
 *
 * @copyright  Copyright (C) 2011 - 2012 Ian MacLennan, Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later
 */

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @package  Patchtester
 * @since    2.0
 */
class Com_PatchtesterInstallerScript
{
	/**
	 * Minimum supported version of the CMS
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $minCmsVersion = '3.2.0';

	/**
	 * Array of templates with supported overrides
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $templateOverrides = array('hathor');

	/**
	 * Function to act prior to installation process begins
	 *
	 * @param   string               $type    The action being performed
	 * @param   JInstallerComponent  $parent  The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.0
	 */
	public function preflight($type, $parent)
	{
		// After releasing CMS 3.2.0, enable the below check
		return true;

		if (version_compare(JVERSION, $this->minCmsVersion, 'lt'))
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PATCHTESTER_ERROR_INSTALL_JVERSION', $this->minCmsVersion));

			return false;
		}

		return true;
	}

	/**
	 * Function to perform changes during install
	 *
	 * @param   JInstallerComponent  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function install($parent)
	{
		$this->copyLayouts();
	}

	/**
	 * Function to perform changes during update
	 *
	 * @param   JInstallerComponent  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function update($parent)
	{
		$this->copyLayouts();
	}

	/**
	 * Function to perform changes during uninstall
	 *
	 * @param   JInstallerComponent  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function uninstall($parent)
	{
		jimport('joomla.filesystem.folder');

		// Initialize the error array
		$errorTemplates = array();

		// Loop the supported templates
		foreach ($this->templateOverrides as $template)
		{
			// Set the file paths
			$tmplRoot       = JPATH_ADMINISTRATOR . '/templates/' . $template;
			$overrideFolder = JPATH_ADMINISTRATOR . '/templates/' . $template . '/html/com_patchtester';

			// Make sure the template is actually installed
			if (is_dir($tmplRoot))
			{
				// If there's a failure in copying the overrides, log it to the error array
				if (!JFolder::delete($overrideFolder))
				{
					$errorTemplates[] = ucfirst($template);
				}
			}
		}

		// If we couldn't remove any overrides, notify the user
		if (count($errorTemplates) > 0)
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PATCHTESTER_COULD_NOT_REMOVE_OVERRIDES', implode(', ', $errorTemplates)));
		}
	}

	/**
	 * Function to copy layout overrides for core templates at install or update
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	private function copyLayouts()
	{
		jimport('joomla.filesystem.folder');

		// Initialize the error array
		$errorTemplates = array();

		// Loop the supported templates
		foreach ($this->templateOverrides as $template)
		{
			// Set the file paths
			$source      = __DIR__ . '/' . $template;
			$tmplRoot    = JPATH_ADMINISTRATOR . '/templates/' . $template;
			$destination = JPATH_ADMINISTRATOR . '/templates/' . $template . '/html/com_patchtester';

			// Make sure the template is actually installed
			if (is_dir($tmplRoot))
			{
				// If there's a failure in copying the overrides, log it to the error array
				if (!JFolder::copy($source, $destination))
				{
					$errorTemplates[] = ucfirst($template);
				}
			}
		}

		// If we couldn't remove any overrides, notify the user
		if (count($errorTemplates) > 0)
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PATCHTESTER_COULD_NOT_INSTALL_OVERRIDES', implode(', ', $errorTemplates)));
		}
	}
}
