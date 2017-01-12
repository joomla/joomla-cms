<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Content component
 *
 * @since  1.5
 */
class ContentViewArchive extends JViewLegacy
{
	protected $state = null;

	protected $item = null;

	protected $items = null;

	protected $pagination = null;

	protected $years = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$user       = JFactory::getUser();
		$state      = $this->get('State');
		$items      = $this->get('Items');
		$pagination = $this->get('Pagination');

		// Get the page/component configuration
		$params = &$state->params;

		JPluginHelper::importPlugin('content');

		foreach ($items as $item)
		{
			$item->catslug     = $item->category_alias ? ($item->catid . ':' . $item->category_alias) : $item->catid;
			$item->parent_slug = $item->parent_alias ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;

			// No link for ROOT category
			if ($item->parent_alias === 'root')
			{
				$item->parent_slug = null;
			}

			$item->event = new stdClass;

			$dispatcher = JEventDispatcher::getInstance();

			// Old plugins: Ensure that text property is available
			if (!isset($item->text))
			{
				$item->text = $item->introtext;
			}

			$dispatcher->trigger('onContentPrepare', array ('com_content.archive', &$item, &$item->params, 0));

			// Old plugins: Use processed text as introtext
			$item->introtext = $item->text;

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_content.archive', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_content.archive', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_content.archive', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}

		$form = new stdClass;

		// Month Field
		$months = array(
			'' => JText::_('COM_CONTENT_MONTH'),
			'01' => JText::_('JANUARY_SHORT'),
			'02' => JText::_('FEBRUARY_SHORT'),
			'03' => JText::_('MARCH_SHORT'),
			'04' => JText::_('APRIL_SHORT'),
			'05' => JText::_('MAY_SHORT'),
			'06' => JText::_('JUNE_SHORT'),
			'07' => JText::_('JULY_SHORT'),
			'08' => JText::_('AUGUST_SHORT'),
			'09' => JText::_('SEPTEMBER_SHORT'),
			'10' => JText::_('OCTOBER_SHORT'),
			'11' => JText::_('NOVEMBER_SHORT'),
			'12' => JText::_('DECEMBER_SHORT')
		);
		$form->monthField = JHtml::_(
			'select.genericlist',
			$months,
			'month',
			array(
				'list.attr' => 'size="1" class="inputbox"',
				'list.select' => $state->get('filter.month'),
				'option.key' => null
			)
		);

		// Year Field
		$this->years = $this->getModel()->getYears();
		$years = array();
		$years[] = JHtml::_('select.option', null, JText::_('JYEAR'));

		for ($i = 0, $iMax = count($this->years); $i < $iMax; $i++)
		{
			$years[] = JHtml::_('select.option', $this->years[$i], $this->years[$i]);
		}

		$form->yearField = JHtml::_(
			'select.genericlist',
			$years,
			'year',
			array('list.attr' => 'size="1" class="inputbox"', 'list.select' => $state->get('filter.year'))
		);
		$form->limitField = $pagination->getLimitBox();

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->filter     = $state->get('list.filter');
		$this->form       = &$form;
		$this->items      = &$items;
		$this->params     = &$params;
		$this->user       = &$user;
		$this->pagination = &$pagination;
		$this->pagination->setAdditionalUrlParam('month', $state->get('filter.month'));
		$this->pagination->setAdditionalUrlParam('year', $state->get('filter.year'));

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void.
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
