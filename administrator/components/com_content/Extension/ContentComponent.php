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
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\CMS\Workflow\WorkflowServiceTrait;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;
use Joomla\Component\Content\Administrator\Service\HTML\AdministratorService;
use Joomla\Component\Content\Administrator\Service\HTML\Icon;
use Psr\Container\ContainerInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * Component class for com_content
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentComponent extends MVCComponent implements
	BootableExtensionInterface, MVCFactoryServiceInterface, CategoriesServiceInterface, FieldsServiceInterface,
	AssociationServiceInterface, WorkflowServiceInterface
{
	use CategoriesServiceTrait;
	use AssociationServiceTrait;
	use HTMLRegistryAwareTrait;
	use WorkflowServiceTrait;

	/**
	 * The trashed condition
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	const CONDITION_NAMES = [
		self::CONDITION_PUBLISHED   => 'JPUBLISHED',
		self::CONDITION_UNPUBLISHED => 'JUNPUBLISHED',
		self::CONDITION_ARCHIVED    => 'JARCHIVED',
		self::CONDITION_TRASHED     => 'JTRASHED',
	];

	/**
	 * The archived condition
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	const CONDITION_ARCHIVED = 2;

	/**
	 * The published condition
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	const CONDITION_PUBLISHED = 1;

	/**
	 * The unpublished condition
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	const CONDITION_UNPUBLISHED = 0;

	/**
	 * The trashed condition
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	const CONDITION_TRASHED = -2;

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
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function validateSection($section, $item = null)
	{
		if (Factory::getApplication()->isClient('site'))
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function getContexts(): array
	{
		Factory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_content.article'    => Text::_('COM_CONTENT'),
			'com_content.categories' => Text::_('JCATEGORY')
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
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getTableNameForSection(string $section = null)
	{
		return '#__content';
	}

	/**
	 * Method to filter transitions by given id of state.
	 *
	 * @param   array  $transitions  The Transitions to filter
	 * @param   int    $pk           Id of the state
	 *
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function filterTransitions($transitions, $pk): array
	{
		return ContentHelper::filterTransitions($transitions, $pk);
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
	 */
	public function countItems(array $items, string $section)
	{
		$db = Factory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed     = 0;
			$item->count_archived    = 0;
			$item->count_unpublished = 0;
			$item->count_published   = 0;

			$query = $db->getQuery(true);

			$query->select($db->quoteName('condition'))
				->select('COUNT(*) AS ' . $db->quoteName('count'))
				->from($db->quoteName('#__content', 'c'))
				->from($db->quoteName('#__workflow_stages', 's'))
				->from($db->quoteName('#__workflow_associations', 'a'))
				->where($db->quoteName('a.item_id') . ' = ' . $db->quoteName('c.id'))
				->where($db->quoteName('s.id') . ' = ' . $db->quoteName('a.stage_id'))
				->where($db->quoteName('catid') . ' = ' . (int) $item->id)
				->where($db->quoteName('a.extension') . '= ' . $db->quote('com_content'))
				->group($db->quoteName('condition'));

			$articles = $db->setQuery($query)->loadObjectList();

			foreach ($articles as $article)
			{
				if ($article->condition == self::CONDITION_PUBLISHED)
				{
					$item->count_published = $article->count;
				}

				if ($article->condition == self::CONDITION_UNPUBLISHED)
				{
					$item->count_unpublished = $article->count;
				}

				if ($article->condition == self::CONDITION_ARCHIVED)
				{
					$item->count_archived = $article->count;
				}

				if ($article->condition == self::CONDITION_TRASHED)
				{
					$item->count_trashed = $article->count;
				}
			}
		}
	}

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   \stdClass[]  $items      The content objects
	 * @param   string       $extension  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function countTagItems(array $items, string $extension)
	{
		$db      = Factory::getDbo();
		$parts   = explode('.', $extension);
		$section = null;

		if (count($parts) > 1)
		{
			$section = $parts[1];
		}

		$join  = $db->quoteName('#__content', 'c') . ' ON ct.content_item_id=c.id';
		$state = $db->quoteName('state');

		if ($section === 'category')
		{
			$join  = $db->quoteName('#__categories') . ' AS c ON ct.content_item_id=c.id';
			$state = $db->quoteName('published', 'state');
		}

		foreach ($items as $item)
		{
			$item->count_trashed     = 0;
			$item->count_archived    = 0;
			$item->count_unpublished = 0;
			$item->count_published   = 0;
			$query                   = $db->getQuery(true);
			$query->select($state . ', count(*) AS count')
				->from($db->quoteName('#__contentitem_tag_map', 'ct'))
				->where($db->quoteName('ct.tag_id') . ' = ' . (int) $item->id)
				->where($db->quoteName('ct.type_alias') . ' = ' . $db->quote($extension))
				->join('LEFT', $join)
				->group($db->quoteName('state'));
			$db->setQuery($query);
			$contents = $db->loadObjectList();

			foreach ($contents as $content)
			{
				if ($content->state == self::CONDITION_PUBLISHED)
				{
					$item->count_published = $content->count;
				}

				if ($content->state == self::CONDITION_UNPUBLISHED)
				{
					$item->count_unpublished = $content->count;
				}

				if ($content->state == self::CONDITION_ARCHIVED)
				{
					$item->count_archived = $content->count;
				}

				if ($content->state == self::CONDITION_TRASHED)
				{
					$item->count_trashed = $content->count;
				}
			}
		}
	}

	/**
	 * Prepares the category form
	 *
	 * @param   Form          $form  The form to prepare
	 * @param   array|object  $data  The form data
	 *
	 * @return void
	 */
	public function prepareForm(Form $form, $data)
	{
		ContentHelper::onPrepareForm($form, $data);
	}

	/**
	 * Method to change state of multiple ids
	 *
	 * @param   array  $pks        Array of IDs
	 * @param   int    $condition  Condition of the workflow state
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function updateContentState($pks, $condition): bool
	{
		return ContentHelper::updateContentState($pks, $condition);
	}
}
