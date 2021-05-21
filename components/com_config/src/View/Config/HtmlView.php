<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Site\View\Config;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Config\Administrator\Controller\RequestController;

/**
 * View for the global configuration
 *
 * @since  3.2
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The form object
	 *
	 * @var   \JForm
	 * @since 3.2
	 */
	public $form;

	/**
	 * The data to be displayed in the form
	 *
	 * @var   array
	 * @since 3.2
	 */
	public $data;

	/**
	 * Is the current user a super administrator?
	 *
	 * @var   boolean
	 * @since 3.2
	 */
	protected $userIsSuperAdmin;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		$user = Factory::getUser();
		$this->userIsSuperAdmin = $user->authorise('core.admin');

		// Access backend com_config
		$requestController = new RequestController;

		// Execute backend controller
		$serviceData = json_decode($requestController->getJson(), true);

		$form = $this->getForm();

		if ($form)
		{
			$form->bind($serviceData);
		}

		$this->form = $form;
		$this->data = $serviceData;

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document.
	 *
	 * @return  void
	 */
	protected function _prepareDocument()
	{
		$app     = Factory::getApplication();
		$pathway = $app->getPathway();

		/**
		 * Because the application sets a default page title,
		 * we need to get it from the menu item itself
		 */
		$menu = $app->getMenu()->getActive();


		$title = $this->params->get('page_title', '');

		// If the menu item is not linked to this article
		if (!$this->menuItemMatchArticle)
		{
			// If a browser page title is defined, use that, then fall back to the article title if set, then fall back to the page_title option
			$title = $this->item->params->get('article_page_title', $this->item->title ?: $title);

			// Get ID of the category from active menu item
			if ($menu && $menu->component == 'com_content' && isset($menu->query['view'])
				&& in_array($menu->query['view'], ['categories', 'category']))
			{
				$id = $menu->query['id'];
			}
			else
			{
				$id = 0;
			}

			$path     = array(array('title' => $this->item->title, 'link' => ''));
			$category = Categories::getInstance('Content')->get($this->item->catid);

			while ($category !== null && $category->id != $id && $category->id !== 'root')
			{
				$path[]   = array('title' => $category->title, 'link' => RouteHelper::getCategoryRoute($category->id, $category->language));
				$category = $category->getParent();
			}

			$path = array_reverse($path);

			foreach ($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}

		if (empty($title))
		{
			$title = $this->item->title;
		}

		$this->setDocumentTitle($title);

		if ($this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}
		elseif ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetaData('robots', $this->params->get('robots'));
		}

		if ($app->get('MetaAuthor') == '1')
		{
			$author = $this->item->created_by_alias ?: $this->item->author;
			$this->document->setMetaData('author', $author);
		}

		$mdata = $this->item->metadata->toArray();

		foreach ($mdata as $k => $v)
		{
			if ($v)
			{
				$this->document->setMetaData($k, $v);
			}
		}

		// If there is a pagebreak heading or title, add it to the page title
		if (!empty($this->item->page_title))
		{
			$this->item->title = $this->item->title . ' - ' . $this->item->page_title;
			$this->setDocumentTitle(
				$this->item->page_title . ' - ' . Text::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $this->state->get('list.offset') + 1)
			);
		}

		if ($this->print)
		{
			$this->document->setMetaData('robots', 'noindex, nofollow');
		}
	}
}
