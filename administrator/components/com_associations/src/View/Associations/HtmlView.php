<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Associations\Administrator\View\Associations;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Associations\Administrator\Helper\AssociationsHelper;

/**
 * View class for a list of articles.
 *
 * @since  3.7.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var   array
	 *
	 * @since  3.7.0
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    \Joomla\CMS\Pagination\Pagination
	 *
	 * @since  3.7.0
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    object
	 *
	 * @since  3.7.0
	 */
	protected $state;

	/**
	 * Selected item type properties.
	 *
	 * @var    \Joomla\Registry\Registry
	 *
	 * @since  3.7.0
	 */
	public $itemType = null;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since  3.7.0
	 */
	public function display($tpl = null)
	{
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (!Associations::isEnabled())
		{
			$link = Route::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . AssociationsHelper::getLanguagefilterPluginId());
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_ASSOCIATIONS_ERROR_NO_ASSOC', $link), 'warning');
		}
		elseif ($this->state->get('itemtype') != '' && $this->state->get('language') != '')
		{
			$type = null;

			list($extensionName, $typeName) = explode('.', $this->state->get('itemtype'), 2);

			$extension = AssociationsHelper::getSupportedExtension($extensionName);

			$types = $extension->get('types');

			if (\array_key_exists($typeName, $types))
			{
				$type = $types[$typeName];
			}

			$this->itemType = $type;

			if (\is_null($type))
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_ASSOCIATIONS_ERROR_NO_TYPE'), 'warning');
			}
			else
			{
				$this->extensionName = $extensionName;
				$this->typeName      = $typeName;
				$this->typeSupports  = array();
				$this->typeFields    = array();

				$details = $type->get('details');

				if (\array_key_exists('support', $details))
				{
					$support = $details['support'];
					$this->typeSupports = $support;
				}

				if (\array_key_exists('fields', $details))
				{
					$fields = $details['fields'];
					$this->typeFields = $fields;
				}

				// Dynamic filter form.
				// This selectors doesn't have to activate the filter bar.
				unset($this->activeFilters['itemtype']);
				unset($this->activeFilters['language']);

				// Remove filters options depending on selected type.
				if (empty($support['state']))
				{
					unset($this->activeFilters['state']);
					$this->filterForm->removeField('state', 'filter');
				}

				if (empty($support['category']))
				{
					unset($this->activeFilters['category_id']);
					$this->filterForm->removeField('category_id', 'filter');
				}

				if ($extensionName !== 'com_menus')
				{
					unset($this->activeFilters['menutype']);
					$this->filterForm->removeField('menutype', 'filter');
				}

				if (empty($support['level']))
				{
					unset($this->activeFilters['level']);
					$this->filterForm->removeField('level', 'filter');
				}

				if (empty($support['acl']))
				{
					unset($this->activeFilters['access']);
					$this->filterForm->removeField('access', 'filter');
				}

				// Add extension attribute to category filter.
				if (empty($support['catid']))
				{
					$this->filterForm->setFieldAttribute('category_id', 'extension', $extensionName, 'filter');

					if ($this->getLayout() == 'modal')
					{
						// We need to change the category filter to only show categories tagged to All or to the forced language.
						if ($forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
						{
							$this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
						}
					}
				}

				$this->items      = $this->get('Items');
				$this->pagination = $this->get('Pagination');

				$linkParameters = array(
					'layout'     => 'edit',
					'itemtype'   => $extensionName . '.' . $typeName,
					'task'       => 'association.edit',
				);

				$this->editUri = 'index.php?option=com_associations&view=association&' . http_build_query($linkParameters);
			}
		}

		// Check for errors.
		if (\count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since  3.7.0
	 */
	protected function addToolbar()
	{
		$user = Factory::getUser();

		if (isset($this->typeName) && isset($this->extensionName))
		{
			$helper = AssociationsHelper::getExtensionHelper($this->extensionName);
			$title  = $helper->getTypeTitle($this->typeName);

			$languageKey = strtoupper($this->extensionName . '_' . $title . 'S');

			if ($this->typeName === 'category')
			{
				$languageKey = strtoupper($this->extensionName) . '_CATEGORIES';
			}

			ToolbarHelper::title(
				Text::sprintf(
					'COM_ASSOCIATIONS_TITLE_LIST', Text::_($this->extensionName), Text::_($languageKey)
				), 'language assoc'
			);
		}
		else
		{
			ToolbarHelper::title(Text::_('COM_ASSOCIATIONS_TITLE_LIST_SELECT'), 'language assoc');
		}

		if ($user->authorise('core.admin', 'com_associations') || $user->authorise('core.options', 'com_associations'))
		{
			if (!isset($this->typeName))
			{
				ToolbarHelper::custom('associations.purge', 'purge', '', 'COM_ASSOCIATIONS_PURGE', false, false);
				ToolbarHelper::custom('associations.clean', 'refresh', '', 'COM_ASSOCIATIONS_DELETE_ORPHANS', false, false);
			}

			ToolbarHelper::preferences('com_associations');
		}

		ToolbarHelper::help('Multilingual_Associations');
	}
}
