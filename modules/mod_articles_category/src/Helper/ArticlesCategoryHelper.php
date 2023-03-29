<?php

/**
 * @package    Joomla.Site
 * @subpackage mod_articles_category
 *
 * @copyright (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\ArticlesCategory\Site\Helper;

use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Content\Site\Model\ArticlesModel;
use Joomla\Component\Content\Site\Model\CategoriesModel;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_articles_category
 *
 * @since __DEPLOY_VERSION__
 */
class ArticlesCategoryHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Retrieve a list of article
     *
     * @param Registry        $moduleParams The module parameters.
     * @param SiteApplication $app          The current application.
     *
     * @return object[]
     *
     * @since __DEPLOY_VERSION__
     */
    public function getArticles(Registry $moduleParams, SiteApplication $app)
    {
        $mvcContentFactory = $app->bootComponent('com_content')->getMVCFactory();

        /* @var ArticlesModel $articlesModel */
        $articlesModel = $mvcContentFactory->createModel('Articles', 'Site', ['ignore_request' => true]);

        // Set application parameters in model
        $appParams = $app->getParams();
        $articlesModel->setState('params', $appParams);

        $articlesModel->setState('list.start', 0);
        $articlesModel->setState('filter.published', ContentComponent::CONDITION_PUBLISHED);

        // Set the filters based on the module params
        $articlesModel->setState('list.limit', (int) $moduleParams->get('count', 0));
        $articlesModel->setState(
            'load_tags',
            $moduleParams->get('show_tags', 0) || $moduleParams->get('article_grouping', 'none') === 'tags'
        );

        // Access filter
        $access = !ComponentHelper::getParams('com_content')->get('show_noauth');
        $articlesModel->setState('filter.access', $access);

        // Get the component and view name
        $input  = $app->getInput();
        $option = $input->get('option');
        $view   = $input->get('view');

        // Preparation for Normal or Dynamic Modes
        $mode = $moduleParams->get('mode', 'normal');

        // If we inside an article view, get the article id
        $active_article_id = $view === 'article' ? $input->getInt('id') : '';

        switch ($mode) {
            case 'dynamic':
                if ($option === 'com_content') {
                    switch ($view) {
                        case 'category':
                        case 'categories':
                            $catids = [$input->getInt('id')];
                            break;
                        case 'article':
                            if ($moduleParams->get('show_on_article_page', 1)) {
                                $catid = $input->getInt('catid');

                                if (!$catid) {
                                    // Get an instance of the generic article model
                                    $articleModel = $mvcContentFactory->createModel(
                                        'Article',
                                        'Site',
                                        ['ignore_request' => true]
                                    );

                                    $articleModel->setState('params', $appParams);
                                    $articleModel->setState('filter.published', 1);
                                    $articleModel->setState('article.id', (int) $active_article_id);
                                    $item   = $articleModel->getItem();
                                    $catids = [$item->catid];
                                } else {
                                    $catids = [$catid];
                                }
                            } else {
                                // Return right away if show_on_article_page option is off
                                return;
                            }
                            break;

                        default:
                            // Return right away if not on the category or article views
                            return;
                    }
                } else {
                    // Return right away if not on a com_content page
                    return;
                }

                break;

            default:
                $catids = $moduleParams->get('catid');
                $articlesModel->setState(
                    'filter.category_id.include',
                    (bool) $moduleParams->get('category_filtering_type', 1)
                );
                break;
        }

        // Category filter
        if (isset($catids)) {
            if (
                $moduleParams->get('show_child_category_articles', 0)
                && (int) $moduleParams->get('levels', 0) > 0
            ) {
                /* @var CategoriesModel $categoriesModel */
                $categoriesModel = $mvcContentFactory->createModel(
                    'Categories',
                    'Site',
                    ['ignore_request' => true]
                );

                $categoriesModel->setState('params', $appParams);
                $levels = $moduleParams->get('levels', 1) ?: 9999;
                $categoriesModel->setState('filter.get_children', $levels);
                $categoriesModel->setState('filter.published', 1);
                $categoriesModel->setState('filter.access', $access);

                $additional_catids = [];

                foreach ($catids as $catid) {
                    $categoriesModel->setState('filter.parentId', $catid);
                    $categories = $categoriesModel->getItems(true);

                    if ($categories) {
                        foreach ($categories as $category) {
                            $condition = (($category->level - $categories->getParent()->level) <= $levels);

                            if ($condition) {
                                $additional_catids[] = $category->id;
                            }
                        }
                    }
                }

                $catids = array_unique(array_merge($catids, $additional_catids));
            }

            $articlesModel->setState('filter.category_id', $catids);
        }

        // Set ordering
        $ordering = $moduleParams->get('article_ordering', 'a.ordering');

        switch ($ordering) {
            case 'random':
                $articlesModel->setState('list.ordering', $this->getDatabase()->getQuery(true)->rand());
                break;

            case 'rating_count':
            case 'rating':
                $articlesModel->setState('list.ordering', $ordering);
                $articlesModel->setState('list.direction', $moduleParams->get('article_ordering_direction', 'ASC'));

                if (!PluginHelper::isEnabled('content', 'vote')) {
                    $articlesModel->setState('list.ordering', 'a.ordering');
                }

                break;

            default:
                $articlesModel->setState('list.ordering', $ordering);
                $articlesModel->setState('list.direction', $moduleParams->get('article_ordering_direction', 'ASC'));
                break;
        }

        // Filter by multiple tags
        $articlesModel->setState('filter.tag', $moduleParams->get('filter_tag', []));

        // Filter by featured articles
        $articlesModel->setState('filter.featured', $moduleParams->get('show_front', 'show'));

        // Author filter
        $articlesModel->setState('filter.author_id', $moduleParams->get('created_by', []));
        $articlesModel->setState('filter.author_id.include', $moduleParams->get('author_filtering_type', 1));
        $articlesModel->setState('filter.author_alias', $moduleParams->get('created_by_alias', []));
        $articlesModel->setState(
            'filter.author_alias.include',
            $moduleParams->get('author_alias_filtering_type', 1)
        );

        $excluded_articles = $moduleParams->get('excluded_articles', '');

        if ($excluded_articles) {
            $excluded_articles = explode("\r\n", $excluded_articles);
            $articlesModel->setState('filter.article_id', $excluded_articles);

            // Exclude
            $articlesModel->setState('filter.article_id.include', false);
        }

        $date_filtering = $moduleParams->get('date_filtering', 'off');

        if ($date_filtering !== 'off') {
            $articlesModel->setState('filter.date_filtering', $date_filtering);
            $articlesModel->setState(
                'filter.date_field',
                $moduleParams->get('date_field', 'a.created')
            );
            $articlesModel->setState(
                'filter.start_date_range',
                $moduleParams->get('start_date_range', '1000-01-01 00:00:00')
            );
            $articlesModel->setState(
                'filter.end_date_range',
                $moduleParams->get('end_date_range', '9999-12-31 23:59:59')
            );
            $articlesModel->setState(
                'filter.relative_date',
                $moduleParams->get('relative_date', 30)
            );
        }

        // Filter by language
        $articlesModel->setState('filter.language', $app->getLanguageFilter());

        // Prepare the module output
        $items      = [];
        $itemParams = new \stdClass();

        $itemParams->show_date        = $moduleParams->get('show_date', 0);
        $itemParams->show_date_field  = $moduleParams->get('show_date_field', 'created');
        $itemParams->show_date_format = $moduleParams->get('show_date_format', 'Y-m-d H:i:s');
        $itemParams->show_category    = $moduleParams->get('show_category', 0);
        $itemParams->show_hits        = $moduleParams->get('show_hits', 0);
        $itemParams->show_author      = $moduleParams->get('show_author', 0);
        $itemParams->show_introtext   = $moduleParams->get('show_introtext', 0);
        $itemParams->introtext_limit  = $moduleParams->get('introtext_limit', 100);

        $itemParams->active_article_id = $active_article_id;
        $itemParams->authorised        = Access::getAuthorisedViewLevels($app->getIdentity()->get('id'));
        $itemParams->access            = $access;
        $itemParams->url_param_itemid  = $input->getInt('Itemid');
        $itemParams->menu              = $app->getMenu();

        foreach ($articlesModel->getItems() as $item) {
            $items[] = $this->prepareItem($item, $itemParams);
        }

        // Check if items need be grouped
        $groupBy        = $moduleParams->get('article_grouping', 'none');
        $groupDirection = $moduleParams->get('article_grouping_direction', 'ksort');

        if ($groupBy !== 'none') {
            switch ($groupBy) {
                case 'year':
                case 'month_year':
                    $items = static::groupByDate(
                        $items,
                        $groupDirection,
                        $groupBy,
                        $moduleParams->get('month_year_format', 'F Y'),
                        $moduleParams->get('date_grouping_field', 'created')
                    );
                    break;
                case 'author':
                case 'category_title':
                    $items = static::groupBy($items, $groupBy, $groupDirection);
                    break;
                case 'tags':
                    $items = static::groupByTags($items, $groupDirection);
                    break;
            }
        }

        return $items;
    }

    /**
     * Prepare the article before render.
     *
     * @param object    $item   The article to prepare
     * @param \stdClass $params The model item
     *
     * @return object
     *
     * @since __DEPLOY_VERSION__
     */
    private function prepareItem(object $item, \stdClass $params): object
    {
        $item->slug = $item->id . ':' . $item->alias;

        if ($params->access || \in_array($item->access, $params->authorised)) {
            // We know that user has the privilege to view the article
            $item->link = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language));
        } else {
            $menuitems = $params->menu->getItems('link', 'index.php?option=com_users&view=login');

            if (isset($menuitems[0])) {
                $Itemid = $menuitems[0]->id;
            } elseif ($params->url_param_itemid > 0) {
                // Use Itemid from requesting page only if there is no existing menu
                $Itemid = $params->url_param_itemid;
            }

            $item->link = Route::_('index.php?option=com_users&view=login&Itemid=' . $Itemid);
        }

        // Used for styling the active article
        $item->active = $item->id == $params->active_article_id ? 'active' : '';

        $item->displayDate = '';
        $dateField         = $params->show_date_field;

        if ($params->show_date) {
            $item->displayDate = HTMLHelper::_('date', $item->$dateField, $params->show_date_format);
        }

        if ($item->catid) {
            $item->displayCategoryLink  = Route::_(
                RouteHelper::getCategoryRoute($item->catid, $item->category_language)
            );
            $item->displayCategoryTitle = $params->show_category
                ? '<a href="' . $item->displayCategoryLink . '">' . $item->category_title . '</a>'
                : '';
        } else {
            $item->displayCategoryTitle = $params->show_category ? $item->category_title : '';
        }

        $item->displayHits       = $params->show_hits ? $item->hits : '';
        $item->displayAuthorName = $params->show_author ? $item->author : '';

        if ($params->show_introtext) {
            $item->introtext = HTMLHelper::_('content.prepare', $item->introtext, '', 'mod_articles_category.content');
            $item->introtext = static::_cleanIntrotext($item->introtext);
        }

        $item->displayIntrotext = $params->show_introtext
            ? self::truncate($item->introtext, $params->introtext_limit)
            : '';
        $item->displayReadmore  = $item->alternative_readmore;

        return $item;
    }

    /**
     * Get a list of articles from a specific category
     *
     * @param Registry &$params object holding the models parameters
     *
     * @return array The array of users
     *
     * @since 1.6
     *
     * @deprecated __DEPLOY_VERSION__ will be removed in 6.0
     *             Use the non-static method getArticles
     *             Example: Factory::getApplication()->bootModule('mod_articles_category', 'site')
     *                          ->getHelper('ArticlesCategoryHelper')
     *                          ->getArticles($params, Factory::getApplication())
     */
    public static function getList(Registry &$params): array
    {
        /* @var SiteApplication $app */
        $app = Factory::getApplication();

        return (new self())->getArticles($params, $app);
    }

    /**
     * Strips unnecessary tags from the introtext
     *
     * @param string $introtext introtext to sanitize
     *
     * @return string
     *
     * @since 1.6
     */
    public static function _cleanIntrotext(string $introtext): string
    {
        $introtext = str_replace(['<p>', '</p>'], ' ', $introtext);
        $introtext = strip_tags($introtext, '<a><em><strong><joomla-hidden-mail>');

        return trim($introtext);
    }

    /**
     * Method to truncate introtext
     *
     * The goal is to get the proper length plain text string with as much of
     * the html intact as possible with all tags properly closed.
     *
     * @param string $html      The content of the introtext to be truncated
     * @param int    $maxLength The maximum number of characters to render
     *
     * @return string The truncated string
     *
     * @since 1.6
     */
    public static function truncate(string $html, int $maxLength = 0): string
    {
        $baseLength = \strlen($html);

        // First get the plain text string. This is the rendered text we want to end up with.
        $ptString = HTMLHelper::_('string.truncate', $html, $maxLength, true, false);

        for ($maxLength; $maxLength < $baseLength;) {
            // Now get the string if we allow html.
            $htmlString = HTMLHelper::_('string.truncate', $html, $maxLength, true, true);

            // Now get the plain text from the html string.
            $htmlStringToPtString = HTMLHelper::_(
                'string.truncate',
                $htmlString,
                $maxLength,
                true,
                false
            );

            // If the new plain text string matches the original plain text string we are done.
            if ($ptString === $htmlStringToPtString) {
                return $htmlString;
            }

            // Get the number of html tag characters in the first $maxlength characters
            $diffLength = \strlen($ptString) - \strlen($htmlStringToPtString);

            // Set new $maxlength that adjusts for the html tags
            $maxLength += $diffLength;

            if ($baseLength <= $maxLength || $diffLength <= 0) {
                return $htmlString;
            }
        }

        return $ptString;
    }

    /**
     * Groups items by field
     *
     * @param array  $list            list of items
     * @param string $fieldName       name of field that is used for grouping
     * @param string $direction       ordering direction
     * @param null   $fieldNameToKeep field name to keep
     *
     * @return array
     *
     * @since 1.6
     */
    public static function groupBy(array $list, string $fieldName, string $direction, $fieldNameToKeep = null): array
    {
        $grouped = [];

        foreach ($list as $key => $item) {
            if (!isset($grouped[$item->$fieldName])) {
                $grouped[$item->$fieldName] = [];
            }

            if ($fieldNameToKeep === null) {
                $grouped[$item->$fieldName][$key] = $item;
            } else {
                $grouped[$item->$fieldName][$key] = $item->$fieldNameToKeep;
            }

            unset($list[$key]);
        }

        $direction($grouped);

        return $grouped;
    }

    /**
     * Groups items by date
     *
     * @param array  $list            list of items
     * @param string $direction       ordering direction
     * @param string $type            type of grouping
     * @param string $monthYearFormat date format to use
     * @param string $field           date field to group by
     *
     * @return array
     *
     * @since 1.6
     */
    public static function groupByDate(
        array $list,
        string $direction = 'ksort',
        string $type = 'year',
        string $monthYearFormat = 'F Y',
        string $field = 'created'
    ): array {
        $grouped = [];

        foreach ($list as $key => $item) {
            switch ($type) {
                case 'month_year':
                    $month_year = StringHelper::substr($item->$field, 0, 7);

                    if (!isset($grouped[$month_year])) {
                        $grouped[$month_year] = [];
                    }

                    $grouped[$month_year][$key] = $item;
                    break;

                default:
                    $year = StringHelper::substr($item->$field, 0, 4);

                    if (!isset($grouped[$year])) {
                        $grouped[$year] = [];
                    }

                    $grouped[$year][$key] = $item;
                    break;
            }

            unset($list[$key]);
        }

        $direction($grouped);

        if ($type === 'month_year') {
            foreach ($grouped as $group => $items) {
                $date                      = new Date($group);
                $formatted_group           = $date->format($monthYearFormat);
                $grouped[$formatted_group] = $items;

                unset($grouped[$group]);
            }
        }

        return $grouped;
    }

    /**
     * Groups items by tags
     *
     * @param array  $list      list of items
     * @param string $direction ordering direction
     *
     * @return array
     *
     * @since 3.9.0
     */
    public static function groupByTags(array $list, string $direction = 'ksort'): array
    {
        $grouped  = [];
        $untagged = [];

        if (!$list) {
            return $grouped;
        }

        foreach ($list as $item) {
            if ($item->tags->itemTags) {
                foreach ($item->tags->itemTags as $tag) {
                    $grouped[$tag->title][] = $item;
                }
            } else {
                $untagged[] = $item;
            }
        }

        $direction($grouped);

        if ($untagged) {
            $grouped['MOD_ARTICLES_CATEGORY_UNTAGGED'] = $untagged;
        }

        return $grouped;
    }
}
