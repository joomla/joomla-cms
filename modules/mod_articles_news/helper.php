<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_news
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_content/models', 'ContentModel');

/**
 * Helper for mod_articles_news
 *
 * @since  1.6
 */
abstract class ModArticlesNewsHelper
{
	/**
	 * Get a list of the latest articles from the article model
	 *
	 * @param   \Joomla\Registry\Registry  &$params  object holding the models parameters
	 *
	 * @return  mixed
	 *
	 * @since 1.6
	 */
	public static function getList(&$params)
	{
		// Get an instance of the generic articles model
		$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		// Set application parameters in model
		$app       = JFactory::getApplication();
		$appParams = $app->getParams();
		$model->setState('params', $appParams);

		$model->setState('list.start', 0);
		$model->setState('filter.published', 1);

		// Set the filters based on the module params
		$model->setState('list.limit', (int) $params->get('count', 5));

		// This module does not use tags data
		$model->setState('load_tags', false);

		// Access filter
		$access     = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$model->setState('filter.access', $access);

		// Category filter
		$model->setState('filter.category_id', $params->get('catid', array()));

		// Filter by language
		$model->setState('filter.language', $app->getLanguageFilter());

		// Filer by tag
		$model->setState('filter.tag', $params->get('tag', array()));

		// Featured switch
		$featured = $params->get('show_featured', '');

		if ($featured === '')
		{
			$model->setState('filter.featured', 'show');
		}
		elseif ($featured)
		{
			$model->setState('filter.featured', 'only');
		}
		else
		{
			$model->setState('filter.featured', 'hide');
		}

		// Set ordering
		$ordering = $params->get('ordering', 'a.publish_up');
		$model->setState('list.ordering', $ordering);

		if (trim($ordering) === 'rand()')
		{
			$model->setState('list.ordering', JFactory::getDbo()->getQuery(true)->Rand());
		}
		else
		{
			$direction = $params->get('direction', 1) ? 'DESC' : 'ASC';
			$model->setState('list.direction', $direction);
			$model->setState('list.ordering', $ordering);
		}

		// Check if we should trigger additional plugin events
		$triggerEvents = $params->get('triggerevents', 1);

		// Retrieve Content
		$items = $model->getItems();

		foreach ($items as &$item)
		{
			$item->readmore = strlen(trim($item->fulltext));
			$item->slug     = $item->id . ':' . $item->alias;

			/** @deprecated Catslug is deprecated, use catid instead. 4.0 */
			$item->catslug  = $item->catid . ':' . $item->category_alias;

			if ($access || in_array($item->access, $authorised))
			{
				// We know that user has the privilege to view the article
				$item->link     = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
				$item->linkText = JText::_('MOD_ARTICLES_NEWS_READMORE');
			}
			else
			{
				$item->link = new JUri(JRoute::_('index.php?option=com_users&view=login', false));
				$item->link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)));
				$item->linkText = JText::_('MOD_ARTICLES_NEWS_READMORE_REGISTER');
			}

			$item->introtext = JHtml::_('content.prepare', $item->introtext, '', 'mod_articles_news.content');

			// Remove any images belongs to the text
			if (!$params->get('image'))
			{
				$item->introtext = preg_replace('/<img[^>]*>/', '', $item->introtext);
			}

			// Show the Intro/Full image field of the article
			if ($params->get('img_intro_full') !== 'none')
			{
				$images = json_decode($item->images);
				$item->imageSrc = '';
				$item->imageAlt = '';
				$item->imageCaption = '';

				if ($params->get('img_intro_full') === 'intro' && !empty($images->image_intro))
				{
					$item->imageSrc = htmlspecialchars($images->image_intro, ENT_COMPAT, 'UTF-8');
					$item->imageAlt = htmlspecialchars($images->image_intro_alt, ENT_COMPAT, 'UTF-8');

					if ($images->image_intro_caption) 
					{
						$item->imageCaption = htmlspecialchars($images->image_intro_caption, ENT_COMPAT, 'UTF-8');
					}
				}
				elseif ($params->get('img_intro_full') === 'full' && !empty($images->image_fulltext))
				{
					$item->imageSrc = htmlspecialchars($images->image_fulltext, ENT_COMPAT, 'UTF-8');
					$item->imageAlt = htmlspecialchars($images->image_fulltext_alt, ENT_COMPAT, 'UTF-8');

					if ($images->image_intro_caption) 
					{
						$item->imageCaption = htmlspecialchars($images->image_fulltext_caption, ENT_COMPAT, 'UTF-8');
					}
				}
			}

			if ($triggerEvents)
			{
				$item->text = '';
				$app->triggerEvent('onContentPrepare', array ('com_content.article', &$item, &$params, 0));

				$results                 = $app->triggerEvent('onContentAfterTitle', array('com_content.article', &$item, &$params, 0));
				$item->afterDisplayTitle = trim(implode("\n", $results));

				$results                    = $app->triggerEvent('onContentBeforeDisplay', array('com_content.article', &$item, &$params, 0));
				$item->beforeDisplayContent = trim(implode("\n", $results));

				$results                   = $app->triggerEvent('onContentAfterDisplay', array('com_content.article', &$item, &$params, 0));
				$item->afterDisplayContent = trim(implode("\n", $results));
			}
			else
			{
				$item->afterDisplayTitle    = '';
				$item->beforeDisplayContent = '';
				$item->afterDisplayContent  = '';
			}
		}

		return $items;
	}
}
