<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Association\AssociationServiceTrait;
use Joomla\CMS\Association\AssociationServiceInterface;
use Joomla\CMS\Categories\CategoriesServiceInterface;
use Joomla\CMS\Categories\CategoriesServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Fields\FieldsServiceInterface;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\Component\Content\Administrator\Service\HTML\AdministratorService;
use Joomla\Component\Content\Administrator\Service\HTML\Icon;
use Psr\Container\ContainerInterface;

/**
 * Component class for com_content
 *
 * @since  4.0.0
 */
class ContentComponent extends MVCComponent implements
	BootableExtensionInterface, CategoriesServiceInterface, FieldsServiceInterface, AssociationServiceInterface
{
	use CategoriesServiceTrait;
	use AssociationServiceTrait;
	use HTMLRegistryAwareTrait;

	/**
	 * Booting the extension. This is the function to set up the environment of the extension like
	 * registering new class loaders, etc.
	 *
	 * If required, some initial set up can be done from services of the container, eg.
	 * registering HTML services.
	 *
	 * @param   ContainerInterface  $container  The container
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function boot(ContainerInterface $container)
	{
		$this->getRegistry()->register('contentadministrator', new AdministratorService);
		$this->getRegistry()->register('contenticon', new Icon($container->get(SiteApplication::class)));

		// The layout joomla.content.icons does need a general icon service
		$this->getRegistry()->register('icon', $this->getRegistry()->getService('contenticon'));
	}

	/**
	 * Returns a valid section for the given section. If it is not valid then null
	 * is returned.
	 *
	 * @param   string  $section  The section to get the mapping for
	 * @param   object  $item     The item
	 *
	 * @return  string|null  The new section
	 *
	 * @since   4.0.0
	 */
	public function validateSection($section, $item = null)
	{
		if (\JFactory::getApplication()->isClient('site'))
		{
			// On the front end we need to map some sections
			switch ($section)
			{
				// Editing an article
				case 'form':

					// Category list view
				case 'featured':
				case 'category':
					$section = 'article';
			}
		}

		if ($section != 'article')
		{
			// We don't know other sections
			return null;
		}

		return $section;
	}

	/**
	 * Returns valid contexts
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function getContexts(): array
	{
		\JFactory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_content.article'    => \JText::_('COM_CONTENT'),
			'com_content.categories' => \JText::_('JCATEGORY')
		);

		return $contexts;
	}

	/**
	 * Returns the table for the count items functions for the given section.
	 *
	 * @param   string  $section  The section
	 *
	 * @return  string|null
	 *
	 * @since   4.0.0
	 */
	protected function getTableNameForSection(string $section = null)
	{
		return '#__content';
	}
}
