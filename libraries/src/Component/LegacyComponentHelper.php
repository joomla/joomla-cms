<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Component\ComponentHelperInterface;
use Joomla\CMS\Dispatcher\DispatcherInterface;

/**
 * Access to component specific services.
 *
 * @since  __DEPLOY_VERSION__
 */
class LegacyComponentHelper implements ComponentHelperInterface
{
	/**
	 * The component name.
	 *
	 * @var string
	 */
	private $component;

	/**
	 * LegacyComponentHelper constructor.
	 *
	 * @param   string  $component  The component name
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(string $component)
	{
		$this->component = $component;
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   \stdClass[]  $items    The category objects
	 * @param   string       $section  The section
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function countItems(array $items, string $section)
	{
		$helper = $this->loadHelper();

		if (!$helper || !is_callable(array($helper, 'countTagItems')))
		{
			return;
		}

		$helper::countItems($items, $section);
	}

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   \stdClass[]  $items      The content objects
	 * @param   string       $extension  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function countTagItems(array $items, string $extension)
	{
		$helper = $this->loadHelper();

		if (!$helper || !is_callable(array($helper, 'countTagItems')))
		{
			return;
		}

		$helper::countTagItems($items, $extension);
	}

	/**
	 * Returns a valid section for articles. If it is not valid then null
	 * is returned.
	 *
	 * @param   string  $section  The section to get the mapping for
	 * @param   object  $item     The item
	 *
	 * @return  string|null  The new section
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function validateSection($section, $item = null)
	{
		$helper = $this->loadHelper();

		if (!$helper || !is_callable(array($helper, 'validateSection')))
		{
			return $section;
		}

		return $helper::validateSection($section, $item);
	}

	/**
	 * Returns valid contexts.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getContexts(): array
	{
		$helper = $this->loadHelper();

		if (!$helper || !is_callable(array($helper, 'getContexts')))
		{
			return [];
		}

		return $helper::getContexts();
	}

	/**
	 * Returns the classname of the legacy helper class. If none is found it returns false.
	 *
	 * @return  bool|string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function loadHelper()
	{
		$eName     = str_replace('com_', '', $this->component);
		$className = ucfirst($eName) . 'Helper';

		if (class_exists($className))
		{
			return $className;
		}

		$file = \JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $this->component . '/helpers/' . $eName . '.php');

		if (!file_exists($file))
		{
			return false;
		}

		\JLoader::register($className, $file);

		if (!class_exists($className))
		{
			return false;
		}

		return $className;
	}
}
