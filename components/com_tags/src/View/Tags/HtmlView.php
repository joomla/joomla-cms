<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\View\Tags;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/**
 * HTML View class for the Tags component
 *
 * @since  3.1
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The model state
	 *
	 * @var    \JObject
	 * @since  3.1
	 */
	protected $state;

	/**
	 * The list of tags
	 *
	 * @var    array|false
	 * @since  3.1
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    \Joomla\CMS\Pagination\Pagination
	 * @since  3.1
	 */
	protected $pagination;

	/**
	 * The page parameters
	 *
	 * @var    \Joomla\Registry\Registry|null
	 * @since  3.1
	 */
	protected $params = null;

	/**
	 * The page class suffix
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $pageclass_sfx = '';

	/**
	 * The logged in user
	 *
	 * @var    \JUser|null
	 * @since  4.0.0
	 */
	protected $user = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed   A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		// Get some data from the models
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params     = $this->state->get('params');
		$this->user       = Factory::getUser();

		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Flag indicates to not add limitstart=0 to URL
		$this->pagination->hideEmptyLimitstart = true;

		if (!empty($this->items))
		{
			foreach ($this->items as $itemElement)
			{
				// Prepare the data.
				$temp = new Registry($itemElement->params);
				$itemElement->params = clone $this->params;
				$itemElement->params->merge($temp);
				$itemElement->params = (array) json_decode($itemElement->params);
			}
		}

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$active = Factory::getApplication()->getMenu()->getActive();

		// Load layout from active query (in case it is an alternative menu item)
		if ($active && isset($active->query['option']) && $active->query['option'] === 'com_tags' && $active->query['view'] === 'tags')
		{
			if (isset($active->query['layout']))
			{
				$this->setLayout($active->query['layout']);
			}
		}
		else
		{
			// Load default All Tags layout from component
			if ($layout = $this->params->get('tags_layout'))
			{
				$this->setLayout($layout);
			}
		}

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 */
	protected function _prepareDocument()
	{
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = Factory::getApplication()->getMenu()->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_TAGS_DEFAULT_PAGE_TITLE'));
		}

		// Set metadata for all tags menu item
		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetaData('robots', $this->params->get('robots'));
		}

		// Respect configuration Sitename Before/After for TITLE in views All Tags.
		$this->setDocumentTitle($this->document->getTitle());

		// Add alternative feed link
		if ($this->params->get('show_feed_link', 1) == 1)
		{
			$link    = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(Route::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(Route::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
}
