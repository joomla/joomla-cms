<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Menus Menu Items View.
 *
 * @since  1.6
 */
class MenusViewItems extends JViewLegacy
{
	/**
	 * @var  array
	 */
	protected $f_levels;

	/**
	 * @var  mixed
	 */
	protected $items;

	/**
	 * @var  JPagination
	 */
	protected $pagination;

	/**
	 * @var  JObject
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$lang = JFactory::getLanguage();
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->total         = $this->get('Total');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			MenusHelper::addSubmenu('items');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->ordering = array();

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as $item)
		{
			$this->ordering[$item->parent_id][] = $item->id;

			// Item type text
			switch ($item->type)
			{
				case 'url':
					$value = JText::_('COM_MENUS_TYPE_EXTERNAL_URL');
					break;

				case 'alias':
					$value = JText::_('COM_MENUS_TYPE_ALIAS');
					break;

				case 'separator':
					$value = JText::_('COM_MENUS_TYPE_SEPARATOR');
					break;

				case 'heading':
					$value = JText::_('COM_MENUS_TYPE_HEADING');
					break;

				case 'container':
					$value = JText::_('COM_MENUS_TYPE_CONTAINER');
					break;

				case 'component':
				default:
					// Load language
						$lang->load($item->componentname . '.sys', JPATH_ADMINISTRATOR, null, false, true)
					|| $lang->load($item->componentname . '.sys', JPATH_ADMINISTRATOR . '/components/' . $item->componentname, null, false, true);

					if (!empty($item->componentname))
					{
						$titleParts   = array();
						$titleParts[] = JText::_($item->componentname);
						$vars         = null;

						parse_str($item->link, $vars);

						if (isset($vars['view']))
						{
							// Attempt to load the view xml file.
							$file = JPATH_SITE . '/components/' . $item->componentname . '/views/' . $vars['view'] . '/metadata.xml';

							if (!is_file($file))
							{
								$file = JPATH_SITE . '/components/' . $item->componentname . '/view/' . $vars['view'] . '/metadata.xml';
							}

							if (is_file($file) && $xml = simplexml_load_file($file))
							{
								// Look for the first view node off of the root node.
								if ($view = $xml->xpath('view[1]'))
								{
									// Add view title if present.
									if (!empty($view[0]['title']))
									{
										$viewTitle = trim((string) $view[0]['title']);

										// Check if the key is valid. Needed due to B/C so we don't show untranslated keys. This check should be removed with Joomla 4.
										if ($lang->hasKey($viewTitle))
										{
											$titleParts[] = JText::_($viewTitle);
										}
									}
								}
							}

							$vars['layout'] = isset($vars['layout']) ? $vars['layout'] : 'default';

							// Attempt to load the layout xml file.
							// If Alternative Menu Item, get template folder for layout file
							if (strpos($vars['layout'], ':') > 0)
							{
								// Use template folder for layout file
								$temp = explode(':', $vars['layout']);
								$file = JPATH_SITE . '/templates/' . $temp[0] . '/html/' . $item->componentname . '/' . $vars['view'] . '/' . $temp[1] . '.xml';

								// Load template language file
								$lang->load('tpl_' . $temp[0] . '.sys', JPATH_SITE, null, false, true)
								||	$lang->load('tpl_' . $temp[0] . '.sys', JPATH_SITE . '/templates/' . $temp[0], null, false, true);
							}
							else
							{
								// Get XML file from component folder for standard layouts
								$file = JPATH_SITE . '/components/' . $item->componentname . '/views/' . $vars['view'] . '/tmpl/' . $vars['layout'] . '.xml';

								if (!file_exists($file))
								{
									$file = JPATH_SITE . '/components/' . $item->componentname . '/view/' . $vars['view'] . '/tmpl/' . $vars['layout'] . '.xml';
								}
							}

							if (is_file($file) && $xml = simplexml_load_file($file))
							{
								// Look for the first view node off of the root node.
								if ($layout = $xml->xpath('layout[1]'))
								{
									if (!empty($layout[0]['title']))
									{
										$titleParts[] = JText::_(trim((string) $layout[0]['title']));
									}
								}

								if (!empty($layout[0]->message[0]))
								{
									$item->item_type_desc = JText::_(trim((string) $layout[0]->message[0]));
								}
							}

							unset($xml);

							// Special case if neither a view nor layout title is found
							if (count($titleParts) == 1)
							{
								$titleParts[] = $vars['view'];
							}
						}

						$value = implode(' » ', $titleParts);
					}
					else
					{
						if (preg_match("/^index.php\?option=([a-zA-Z\-0-9_]*)/", $item->link, $result))
						{
							$value = JText::sprintf('COM_MENUS_TYPE_UNEXISTING', $result[1]);
						}
						else
						{
							$value = JText::_('COM_MENUS_TYPE_UNKNOWN');
						}
					}
					break;
			}

			$item->item_type = $value;
			$item->protected = $item->menutype == 'main';
		}

		// Levels filter.
		$options   = array();
		$options[] = JHtml::_('select.option', '1', JText::_('J1'));
		$options[] = JHtml::_('select.option', '2', JText::_('J2'));
		$options[] = JHtml::_('select.option', '3', JText::_('J3'));
		$options[] = JHtml::_('select.option', '4', JText::_('J4'));
		$options[] = JHtml::_('select.option', '5', JText::_('J5'));
		$options[] = JHtml::_('select.option', '6', JText::_('J6'));
		$options[] = JHtml::_('select.option', '7', JText::_('J7'));
		$options[] = JHtml::_('select.option', '8', JText::_('J8'));
		$options[] = JHtml::_('select.option', '9', JText::_('J9'));
		$options[] = JHtml::_('select.option', '10', JText::_('J10'));

		$this->f_levels = $options;

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
		}
		else
		{
			// In menu associations modal we need to remove language filter if forcing a language.
			if ($forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
			{
				// If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
				$languageXml = new SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
				$this->filterForm->setField($languageXml, 'filter', true);

				// Also, unset the active language filter so the search tools is not open by default with this filter.
				unset($this->activeFilters['language']);
			}
		}

		// Allow a system plugin to insert dynamic menu types to the list shown in menus:
		JEventDispatcher::getInstance()->trigger('onBeforeRenderMenuItems', array($this));

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$menutypeId = (int) $this->state->get('menutypeid');

		$canDo = JHelperContent::getActions('com_menus', 'menu', (int) $menutypeId);
		$user  = JFactory::getUser();

		// Get the menu title
		$menuTypeTitle = $this->get('State')->get('menutypetitle');

		// Get the toolbar object instance
		$bar = JToolbar::getInstance('toolbar');

		if ($menuTypeTitle)
		{
			JToolbarHelper::title(JText::sprintf('COM_MENUS_VIEW_ITEMS_MENU_TITLE', $menuTypeTitle), 'list menumgr');
		}
		else
		{
			JToolbarHelper::title(JText::_('COM_MENUS_VIEW_ITEMS_ALL_TITLE'), 'list menumgr');
		}

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('item.add');
		}

		$protected = $this->state->get('filter.menutype') == 'main';

		if ($canDo->get('core.edit') && !$protected)
		{
			JToolbarHelper::editList('item.edit');
		}

		if ($canDo->get('core.edit.state') && !$protected)
		{
			JToolbarHelper::publish('items.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('items.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		}

		if (JFactory::getUser()->authorise('core.admin') && !$protected)
		{
			JToolbarHelper::checkin('items.checkin', 'JTOOLBAR_CHECKIN', true);
		}

		if ($canDo->get('core.edit.state') && $this->state->get('filter.client_id') == 0)
		{
			JToolbarHelper::makeDefault('items.setDefault', 'COM_MENUS_TOOLBAR_SET_HOME');
		}

		if (JFactory::getUser()->authorise('core.admin'))
		{
			JToolbarHelper::custom('items.rebuild', 'refresh.png', 'refresh_f2.png', 'JToolbar_Rebuild', false);
		}

		// Add a batch button
		if (!$protected && $user->authorise('core.create', 'com_menus')
			&& $user->authorise('core.edit', 'com_menus')
			&& $user->authorise('core.edit.state', 'com_menus'))
		{
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if (!$protected && $this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'items.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif (!$protected && $canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('items.trash');
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::preferences('com_menus');
		}

		JToolbarHelper::help('JHELP_MENUS_MENU_ITEM_MANAGER');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		$this->state = $this->get('State');

		if ($this->state->get('filter.client_id') == 0)
		{
			return array(
				'a.lft'       => JText::_('JGRID_HEADING_ORDERING'),
				'a.published' => JText::_('JSTATUS'),
				'a.title'     => JText::_('JGLOBAL_TITLE'),
				'a.home'      => JText::_('COM_MENUS_HEADING_HOME'),
				'a.access'    => JText::_('JGRID_HEADING_ACCESS'),
				'association' => JText::_('COM_MENUS_HEADING_ASSOCIATION'),
				'language'    => JText::_('JGRID_HEADING_LANGUAGE'),
				'a.id'        => JText::_('JGRID_HEADING_ID')
			);
		}
		else
		{
			return array(
				'a.lft'       => JText::_('JGRID_HEADING_ORDERING'),
				'a.published' => JText::_('JSTATUS'),
				'a.title'     => JText::_('JGLOBAL_TITLE'),
				'a.id'        => JText::_('JGRID_HEADING_ID')
			);
		}
	}
}
