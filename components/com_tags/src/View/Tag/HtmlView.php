<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\View\Tag;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
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
	 * List of items associated with the tag
	 *
	 * @var    \stdClass[]|false
	 * @since  3.1
	 */
	protected $items;

	/**
	 * Tag data for the current tag or tags (on success, false on failure)
	 *
	 * @var    \JObject|boolean
	 * @since  3.1
	 */
	protected $item;

	/**
	 * UNUSED
	 *
	 * @var    null
	 * @since  3.1
	 */
	protected $children;

	/**
	 * UNUSED
	 *
	 * @var    null
	 * @since  3.1
	 */
	protected $parent;

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
	protected $params;

	/**
	 * Array of tags title
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $tags_title;

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
	 * @var    User|null
	 * @since  4.0.0
	 */
	protected $user = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function display($tpl = null)
	{
		$app    = Factory::getApplication();
		$params = $app->getParams();

		// Get some data from the models
		$state      = $this->get('State');
		$items      = $this->get('Items');
		$item       = $this->get('Item');
		$children   = $this->get('Children');
		$parent     = $this->get('Parent');
		$pagination = $this->get('Pagination');

		// Flag indicates to not add limitstart=0 to URL
		$pagination->hideEmptyLimitstart = true;

		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Check whether access level allows access.
		// @TODO: Should already be computed in $item->params->get('access-view')
		$user   = Factory::getUser();
		$groups = $user->getAuthorisedViewLevels();

		foreach ($item as $itemElement)
		{
			if (!in_array($itemElement->access, $groups))
			{
				unset($itemElement);
			}

			// Prepare the data.
			if (!empty($itemElement))
			{
				$temp = new Registry($itemElement->params);
				$itemElement->params   = clone $params;
				$itemElement->params->merge($temp);
				$itemElement->params   = (array) json_decode($itemElement->params);
				$itemElement->metadata = new Registry($itemElement->metadata);
			}
		}

		if ($items !== false)
		{
			PluginHelper::importPlugin('content');

			foreach ($items as $itemElement)
			{
				$itemElement->event = new \stdClass;

				// For some plugins.
				!empty($itemElement->core_body) ? $itemElement->text = $itemElement->core_body : $itemElement->text = null;

				$itemElement->core_params = new Registry($itemElement->core_params);

				Factory::getApplication()->triggerEvent('onContentPrepare', ['com_tags.tag', &$itemElement, &$itemElement->core_params, 0]);

				$results = Factory::getApplication()->triggerEvent('onContentAfterTitle',
					['com_tags.tag', &$itemElement, &$itemElement->core_params, 0]
				);
				$itemElement->event->afterDisplayTitle = trim(implode("\n", $results));

				$results = Factory::getApplication()->triggerEvent('onContentBeforeDisplay',
					['com_tags.tag', &$itemElement, &$itemElement->core_params, 0]
				);
				$itemElement->event->beforeDisplayContent = trim(implode("\n", $results));

				$results = Factory::getApplication()->triggerEvent('onContentAfterDisplay',
					['com_tags.tag', &$itemElement, &$itemElement->core_params, 0]
				);
				$itemElement->event->afterDisplayContent = trim(implode("\n", $results));

				// Write the results back into the body
				if (!empty($itemElement->core_body))
				{
					$itemElement->core_body = $itemElement->text;
				}

				// Categories store the images differently so lets re-map it so the display is correct
				if ($itemElement->type_alias === 'com_content.category')
				{
					$itemElement->core_images = json_encode(
						array(
							'image_intro' => $itemElement->core_params->get('image', ''),
							'image_intro_alt' => $itemElement->core_params->get('image_alt', '')
						)
					);
				}
			}
		}

		$this->state      = $state;
		$this->items      = $items;
		$this->children   = $children;
		$this->parent     = $parent;
		$this->pagination = $pagination;
		$this->user       = $user;
		$this->item       = $item;

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		// Merge tag params. If this is single-tag view, menu params override tag params
		// Otherwise, article params override menu item params
		$this->params = $this->state->get('params');
		$active       = $app->getMenu()->getActive();
		$temp         = clone $this->params;

		// Convert item params to a Registry object
		$item[0]->params = new Registry($item[0]->params);

		// Check to see which parameters should take priority
		if ($active)
		{
			$currentLink = $active->link;

			// If the current view is the active item and a tag view for one tag, then the menu item params take priority
			if (strpos($currentLink, 'view=tag') && strpos($currentLink, '&id[0]=' . (string) $item[0]->id))
			{
				// $item[0]->params are the tag params, $temp are the menu item params
				// Merge so that the menu item params take priority
				$item[0]->params->merge($temp);

				// Load layout from active query (in case it is an alternative menu item)
				if (isset($active->query['layout']))
				{
					$this->setLayout($active->query['layout']);
				}
			}
			else
			{
				// Current menuitem is not a single tag view, so the tag params take priority.
				// Merge the menu item params with the tag params so that the tag params take priority
				$temp->merge($item[0]->params);
				$item[0]->params = $temp;

				// Check for alternative layouts (since we are not in a single-article menu item)
				// Single-article menu item layout takes priority over alt layout for an article
				if ($layout = $item[0]->params->get('tag_layout'))
				{
					$this->setLayout($layout);
				}
			}
		}
		else
		{
			// Merge so that item params take priority
			$temp->merge($item[0]->params);
			$item[0]->params = $temp;

			// Check for alternative layouts (since we are not in a single-tag menu item)
			// Single-tag menu item layout takes priority over alt layout for an article
			if ($layout = $item[0]->params->get('tag_layout'))
			{
				$this->setLayout($layout);
			}
		}

		// Increment the hit counter
		$model = $this->getModel();
		$model->hit();

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
		$app              = Factory::getApplication();
		$menu             = $app->getMenu()->getActive();
		$this->tags_title = $this->getTagsTitle();
		$pathway          = $app->getPathway();
		$title            = '';

		// Highest priority for "Browser Page Title".
		if ($menu)
		{
			$title = $menu->getParams()->get('page_title', '');
		}

		if ($this->tags_title)
		{
			$this->params->def('page_heading', $this->tags_title);
			$title = $title ?: $this->tags_title;
		}
		elseif ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
			$title = $title ?: $this->params->get('page_title', $menu->title);
		}

		$this->setDocumentTitle($title);
		$pathway->addItem($title);

		foreach ($this->item as $itemElement)
		{
			if ($itemElement->metadesc)
			{
				$this->document->setDescription($itemElement->metadesc);
			}
			elseif ($this->params->get('menu-meta_description'))
			{
				$this->document->setDescription($this->params->get('menu-meta_description'));
			}

			if ($this->params->get('robots'))
			{
				$this->document->setMetaData('robots', $this->params->get('robots'));
			}
		}

		if (count($this->item) === 1)
		{
			foreach ($this->item[0]->metadata->toArray() as $k => $v)
			{
				if ($v)
				{
					$this->document->setMetaData($k, $v);
				}
			}
		}

		if ($this->params->get('show_feed_link', 1) == 1)
		{
			$link    = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(Route::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(Route::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
	}

	/**
	 * Creates the tags title for the output
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	protected function getTagsTitle()
	{
		$tags_title = array();

		if (!empty($this->item))
		{
			$user   = Factory::getUser();
			$groups = $user->getAuthorisedViewLevels();

			foreach ($this->item as $item)
			{
				if (in_array($item->access, $groups))
				{
					$tags_title[] = $item->title;
				}
			}
		}

		return implode(' ', $tags_title);
	}
}
