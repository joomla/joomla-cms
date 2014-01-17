<?php
/**
 *  @package     FrameworkOnFramework
 *  @subpackage  config
 *  @copyright   Copyright (c)2010-2012 Nicholas K. Dionysopoulos
 *  @license     GNU General Public License version 2, or later
 */

defined('FOF_INCLUDED') or die();

/**
 * Configuration parser for the view-specific settings
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFConfigDomainViews implements FOFConfigDomainInterface
{
	/**
	 * Parse the XML data, adding them to the $ret array
	 *
	 * @param   SimpleXMLElement  $xml   The XML data of the component's configuration area
	 * @param   array             &$ret  The parsed data, in the form of a hash array
	 *
	 * @return  void
	 */
	public function parseDomain(SimpleXMLElement $xml, array &$ret)
	{
		// Initialise
		$ret['views'] = array();

		// Parse view configuration
		$viewData = $xml->xpath('view');

		// Sanity check

		if (empty($viewData))
		{
			return;
		}

		foreach ($viewData as $aView)
		{
			$key = (string) $aView['name'];

			// Parse ACL options
			$ret['views'][$key]['acl'] = array();
			$aclData = $aView->xpath('acl/task');

			if (!empty($aclData))
			{
				foreach ($aclData as $acl)
				{
					$k = (string) $acl['name'];
					$ret['views'][$key]['acl'][$k] = (string) $acl;
				}
			}

			// Parse taskmap
			$ret['views'][$key]['taskmap'] = array();
			$taskmapData = $aView->xpath('taskmap/task');

			if (!empty($taskmapData))
			{
				foreach ($taskmapData as $map)
				{
					$k = (string) $map['name'];
					$ret['views'][$key]['taskmap'][$k] = (string) $map;
				}
			}

			// Parse controller configuration
			$ret['views'][$key]['config'] = array();
			$optionData = $aView->xpath('config/option');

			if (!empty($optionData))
			{
				foreach ($optionData as $option)
				{
					$k = (string) $option['name'];
					$ret['views'][$key]['config'][$k] = (string) $option;
				}
			}

			// Parse the toolbar
			$ret['views'][$key]['toolbar'] = array();
			$toolBar = $aView->xpath('toolbar');

			if (!empty($toolBar))
			{
				$toolbarAttributes = $toolBar[0]->attributes();

				// If a toolbar title is specified, create a title element.
				if (isset($toolbarAttributes['title']))
				{
					$ret['views'][$key]['toolbar']['title'] = array(
						'value' => (string) $toolbarAttributes['title']
					);
				}

				// Parse the toolbar buttons data
				$toolbarData = $aView->xpath('toolbar/button');

				if (!empty($toolbarData))
				{
					foreach ($toolbarData as $button)
					{
						$k = (string) $button['type'];
						$ret['views'][$key]['toolbar'][$k] = current($button->attributes());
						$ret['views'][$key]['toolbar'][$k]['value'] = (string) $button;
					}
				}
			}
		}
	}

	/**
	 * Return a configuration variable
	 *
	 * @param   string  &$configuration  Configuration variables (hashed array)
	 * @param   string  $var             The variable we want to fetch
	 * @param   mixed   $default         Default value
	 *
	 * @return  mixed  The variable's value
	 */
	public function get(&$configuration, $var, $default)
	{
		$parts = explode('.', $var);

		$view = $parts[0];
		$method = 'get' . ucfirst($parts[1]);

		if (!method_exists($this, $method))
		{
			return $default;
		}

		array_shift($parts);
		array_shift($parts);

		$ret = $this->$method($view, $configuration, $parts, $default);

		return $ret;
	}

	/**
	 * Internal function to return the task map for a view
	 *
	 * @param   string  $view            The view for which we will be fetching a task map
	 * @param   array   &$configuration  The configuration parameters hash array
	 * @param   array   $params          Extra options (not used)
	 * @param   array   $default         ßDefault task map; empty array if not provided
	 *
	 * @return  array  The task map as a hash array in the format task => method
	 */
	protected function getTaskmap($view, &$configuration, $params, $default = array())
	{
		$taskmap = array();

		if (isset($configuration['views']['*']) && isset($configuration['views']['*']['taskmap']))
		{
			$taskmap = $configuration['views']['*']['taskmap'];
		}

		if (isset($configuration['views'][$view]) && isset($configuration['views'][$view]['taskmap']))
		{
			$taskmap = array_merge($taskmap, $configuration['views'][$view]['taskmap']);
		}

		if (empty($taskmap))
		{
			return $default;
		}

		return $taskmap;
	}

	/**
	 * Internal method to return the ACL mapping (privilege required to access
	 * a specific task) for the given view's tasks
	 *
	 * @param   string  $view            The view for which we will be fetching a task map
	 * @param   array   &$configuration  The configuration parameters hash array
	 * @param   array   $params          Extra options; key 0 defines the task we want to fetch
	 * @param   string  $default         Default ACL option; empty (no ACL check) if not defined
	 *
	 * @return  string  The privilege required to access this view
	 */
	protected function getAcl($view, &$configuration, $params, $default = '')
	{
		$aclmap = array();

		if (isset($configuration['views']['*']) && isset($configuration['views']['*']['acl']))
		{
			$aclmap = $configuration['views']['*']['acl'];
		}

		if (isset($configuration['views'][$view]) && isset($configuration['views'][$view]['acl']))
		{
			$aclmap = array_merge($aclmap, $configuration['views'][$view]['acl']);
		}

		$acl = $default;

		if (isset($aclmap['*']))
		{
			$acl = $aclmap['*'];
		}

		if (isset($aclmap[$params[0]]))
		{
			$acl = $aclmap[$params[0]];
		}

		return $acl;
	}

	/**
	 * Internal method to return the a configuration option for the view. These
	 * are equivalent to $config array options passed to the Controller
	 *
	 * @param   string  $view            The view for which we will be fetching a task map
	 * @param   array   &$configuration  The configuration parameters hash array
	 * @param   array   $params          Extra options; key 0 defines the option variable we want to fetch
	 * @param   mixed   $default         Default option; null if not defined
	 *
	 * @return  string  The setting for the requested option
	 */
	protected function getConfig($view, &$configuration, $params, $default = null)
	{
		$ret = $default;

		if (isset($configuration['views']['*'])
			&& isset($configuration['views']['*']['config'])
			&& isset($configuration['views']['*']['config'][$params[0]]))
		{
			$ret = $configuration['views']['*']['config'][$params[0]];
		}

		if (isset($configuration['views'][$view])
			&& isset($configuration['views'][$view]['config'])
			&& isset($configuration['views'][$view]['config'][$params[0]]))
		{
			$ret = $configuration['views'][$view]['config'][$params[0]];
		}

		return $ret;
	}

	/**
	 * Internal method to return the toolbar infos.
	 *
	 * @param   string  $view            The view for which we will be fetching buttons
	 * @param   array   &$configuration  The configuration parameters hash array
	 * @param   array   $params          Extra options
	 * @param   string  $default         Default option
	 *
	 * @return  string  The toolbar data for this view
	 */
	protected function getToolbar($view, &$configuration, $params, $default = '')
	{
		$toolbar = array();

		if (isset($configuration['views']['*']) && isset($configuration['views']['*']['toolbar']))
		{
			$toolbar = $configuration['views']['*']['toolbar'];
		}

		if (isset($configuration['views'][$view]) && isset($configuration['views'][$view]['toolbar']))
		{
			$toolbar = array_merge($toolbar, $configuration['views'][$view]['toolbar']);
		}

		if (empty($toolbar))
		{
			return $default;
		}

		return $toolbar;
	}
}
