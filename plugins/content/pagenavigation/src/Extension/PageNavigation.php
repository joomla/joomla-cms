<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagenavigation
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Content\PageNavigation\Extension;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Event\Content\BeforeDisplayEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\SecondaryCategoriesHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Helper\QueryHelper;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Pagenavigation plugin class.
 *
 * @since  1.5
 */
final class PageNavigation extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   5.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentBeforeDisplay' => 'onContentBeforeDisplay',
        ];
    }

    /**
     * If in the article view and the parameter is enabled shows the page navigation
     *
     * @param   BeforeDisplayEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onContentBeforeDisplay(BeforeDisplayEvent $event)
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $params  = $event->getParams();

        $app   = $this->getApplication();
        $view  = $app->getInput()->get('view');
        $print = $app->getInput()->getBool('print');

        if ($print) {
            return;
        }

        if ($context === 'com_content.article' && $view === 'article' && $params->get('show_item_navigation')) {
            $db         = $this->getDatabase();
            $user       = $app->getIdentity();
            $lang       = $app->getLanguage();
            $now        = Factory::getDate()->toSql();
            $query      = $db->createQuery();
            $uid        = $row->id;
            $option     = 'com_content';
            $canPublish = $user->authorise('core.edit.state', $option . '.article.' . $row->id);

            $navigationContext = $this->getNavigationContext($row, $params);

            $query->order($this->getOrderBy($query, $navigationContext, $params));

            $contextParams = $navigationContext['params'] ?? $params;
            $paramsList    = $contextParams->toArray();

            if (\array_key_exists('orderby_sec', $paramsList)) {
                $order_method = (string) $contextParams->get('orderby_sec', '');
            } else {
                $order_method = (string) $contextParams->get('orderby', '');
            }

            $case_when = ' CASE WHEN ' . $query->charLength($db->quoteName('a.alias'), '!=', '0')
                . ' THEN ' . $query->concatenate([$query->castAs('CHAR', $db->quoteName('a.id')), $db->quoteName('a.alias')], ':')
                . ' ELSE ' . $query->castAs('CHAR', 'a.id') . ' END AS ' . $db->quoteName('slug');

            $case_when1 = ' CASE WHEN ' . $query->charLength($db->quoteName('cc.alias'), '!=', '0')
                . ' THEN ' . $query->concatenate([$query->castAs('CHAR', $db->quoteName('cc.id')), $db->quoteName('cc.alias')], ':')
                . ' ELSE ' . $query->castAs('CHAR', 'cc.id') . ' END AS ' . $db->quoteName('catslug');

            $query->select($db->quoteName(['a.id', 'a.title', 'a.catid', 'a.language']))
                ->select([$case_when, $case_when1])
                ->from($db->quoteName('#__content', 'a'))
                ->join('LEFT', $db->quoteName('#__categories', 'cc'), $db->quoteName('cc.id') . ' = ' . $db->quoteName('a.catid'))
                ->join('LEFT', $db->quoteName('#__content_frontpage', 'fp'), $db->quoteName('fp.content_id') . ' = ' . $db->quoteName('a.id'));
            if ($order_method === 'author' || $order_method === 'rauthor') {
                $query->select($db->quoteName(['a.created_by', 'u.name']));
                $query->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by'));
            }

            $publishedFilter = $this->applyNavigationContext($query, $navigationContext, $row, $now);

            if (!$canPublish) {
                $query->whereIn($db->quoteName('a.access'), Access::getAuthorisedViewLevels($user->id));
            }

            if ($this->shouldApplyPublishDates($publishedFilter)) {
                $query->where(
                    [
                        '(' . $db->quoteName('a.publish_up') . ' IS NULL OR ' . $db->quoteName('a.publish_up') . ' <= :nowDate1)',
                        '(' . $db->quoteName('a.publish_down') . ' IS NULL OR ' . $db->quoteName('a.publish_down') . ' >= :nowDate2)',
                    ]
                )
                    ->bind(':nowDate1', $now)
                    ->bind(':nowDate2', $now);
            }

            if ($app->isClient('site') && $app->getLanguageFilter()) {
                $query->whereIn($db->quoteName('a.language'), [$lang->getTag(), '*'], ParameterType::STRING);
            }

            $db->setQuery($query);
            $list = $db->loadObjectList('id');

            // This check needed if incorrect Itemid is given resulting in an incorrect result.
            if (!\is_array($list)) {
                $list = [];
            }

            reset($list);

            // Location of current content item in array list.
            $location = array_search($uid, array_keys($list));
            $rows     = array_values($list);

            $row->prev = null;
            $row->next = null;

            if ($location - 1 >= 0) {
                // The previous content item cannot be in the array position -1.
                $row->prev = $rows[$location - 1];
            }

            if (($location + 1) < \count($rows)) {
                // The next content item cannot be in an array position greater than the number of array positions.
                $row->next = $rows[$location + 1];
            }

            if ($row->prev) {
                $row->prev_label = ($this->params->get('display', 0) == 0) ? $lang->_('JPREV') : $row->prev->title;
                $row->prev       = RouteHelper::getArticleRoute($row->prev->slug, $row->prev->catid, $row->prev->language);
            } else {
                $row->prev_label = '';
                $row->prev       = '';
            }

            if ($row->next) {
                $row->next_label = ($this->params->get('display', 0) == 0) ? $lang->_('JNEXT') : $row->next->title;
                $row->next       = RouteHelper::getArticleRoute($row->next->slug, $row->next->catid, $row->next->language);
            } else {
                $row->next_label = '';
                $row->next       = '';
            }

            // Output.
            if ($row->prev || $row->next) {
                // Get the path for the layout file
                $path = PluginHelper::getLayoutPath('content', 'pagenavigation');

                // Render the pagenav
                ob_start();
                include $path;
                $row->pagination = ob_get_clean();

                $row->paginationposition = $this->params->get('position', 1);

                // This will default to the 1.5 and 1.6-1.7 behavior.
                $row->paginationrelative = $this->params->get('relative', 0);
            }
        }
    }

    /**
     * Get the navigation context saved by the originating list view.
     *
     * @param   object    $row     The current article.
     * @param   Registry  $params  The current article params.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getNavigationContext(object $row, Registry $params): array
    {
        $app    = $this->getApplication();
        $itemId = (int) $app->getUserState('com_content.navigation_context', 0);
        $menu   = $itemId ? $app->getMenu()->getItem($itemId) : null;
        if (!$menu || empty($menu->query['view'])) {
            return $this->getFallbackNavigationContext($row, $params);
        }

        $view       = $menu->query['view'] ?? '';
        $menuParams = $menu->getParams();
        if (!\in_array($view, ['archive', 'category', 'featured'], true)) {
            return $this->getFallbackNavigationContext($row, $params);
        }

        if ($view === 'category') {
            $showSubcategories = (int) $menuParams->get('show_subcategory_content', 0);
            return [
                'view'                 => 'category',
                'params'               => $menuParams,
                'categoryIds'          => [(int) ($menu->query['id'] ?? 0)],
                'includeSubcategories' => $showSubcategories > 0,
                'maxCategoryLevels'    => $showSubcategories ?: 1,
                'featured'             => $menuParams->get('show_featured', 'show'),
                'published'            => ContentComponent::CONDITION_PUBLISHED,
            ];
        }

        if ($view === 'featured') {
            return [
                'view'                 => 'featured',
                'params'               => $menuParams,
                'categoryIds'          => (array) $menuParams->get('featured_categories', []),
                'includeSubcategories' => false,
                'maxCategoryLevels'    => 1,
                'published'            => ContentComponent::CONDITION_PUBLISHED,
            ];
        }

        return [
            'view'                 => 'archive',
            'params'               => $menuParams,
            'categoryIds'          => (array) ($menu->query['catid'] ?? $menuParams->get('catid', [])),
            'includeSubcategories' => false,
            'maxCategoryLevels'    => 1,
            'published'            => ContentComponent::CONDITION_ARCHIVED,
        ];
    }

    /**
     * Get the legacy navigation context when no saved list menu is available.
     *
     * @param   object    $row     The current article.
     * @param   Registry  $params  The current article params.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getFallbackNavigationContext(object $row, Registry $params): array
    {
        return [
            'view'                 => 'article',
            'params'               => $params,
            'categoryIds'          => [(int) $row->catid],
            'includeSubcategories' => false,
            'maxCategoryLevels'    => 1,
            'published'            => (int) $row->state,
        ];
    }

    /**
     * Apply filters for the saved navigation context.
     *
     * @param   QueryInterface  $query    The query.
     * @param   array           $context  The saved navigation context.
     * @param   object          $row      The current article.
     * @param   string          $now      Current SQL date.
     *
     * @return  mixed
     *
     * @since   __DEPLOY_VERSION__
     */
    private function applyNavigationContext(QueryInterface $query, array $context, object $row, string $now): mixed
    {
        $published = $context['published'] ?? (int) $row->state;

        $this->applyPublishedFilter($query, $published);
        $this->applyCategoryFilter($query, $context);
        $this->applyFeaturedFilter($query, $context, $now);

        return $published;
    }

    /**
     * Apply the published-state filter used by content list models.
     *
     * @param   QueryInterface  $query      The query.
     * @param   mixed           $condition  Published condition.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function applyPublishedFilter(QueryInterface $query, mixed $condition): void
    {
        $db = $this->getDatabase();

        if (is_numeric($condition) && (int) $condition === ContentComponent::CONDITION_ARCHIVED) {
            $conditionUnpublished = ContentComponent::CONDITION_UNPUBLISHED;
            $conditionArchived    = ContentComponent::CONDITION_ARCHIVED;

            $query->where(
                '((' . $db->quoteName('cc.published') . ' = 2 AND ' . $db->quoteName('a.state') . ' > :conditionUnpublished)'
                . ' OR (' . $db->quoteName('cc.published') . ' = 1 AND ' . $db->quoteName('a.state') . ' = :conditionArchived))'
            )
                ->bind(':conditionUnpublished', $conditionUnpublished, ParameterType::INTEGER)
                ->bind(':conditionArchived', $conditionArchived, ParameterType::INTEGER);

            return;
        }

        if (is_numeric($condition)) {
            $condition = (int) $condition;

            $query->where($db->quoteName('cc.published') . ' = 1 AND ' . $db->quoteName('a.state') . ' = :condition')
                ->bind(':condition', $condition, ParameterType::INTEGER);

            return;
        }

        if (\is_array($condition)) {
            $condition = array_values(array_unique(array_map('intval', $condition)));

            if ($condition) {
                $query->where($db->quoteName('cc.published') . ' = 1')
                    ->whereIn($db->quoteName('a.state'), $condition, ParameterType::INTEGER);
            }
        }
    }

    /**
     * Apply primary and secondary category membership filtering.
     *
     * @param   QueryInterface  $query    The query.
     * @param   array           $context  The saved navigation context.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function applyCategoryFilter(QueryInterface $query, array $context): void
    {
        $categoryIds = SecondaryCategoriesHelper::normalizeCategoryIds($context['categoryIds'] ?? []);

        if (!$categoryIds) {
            return;
        }

        $helper = new SecondaryCategoriesHelper('com_content.article');

        $query->where(
            $helper->buildCategoryMembershipCondition(
                $categoryIds,
                (bool) ($context['includeSubcategories'] ?? false),
                (int) ($context['maxCategoryLevels'] ?? 1),
                'a'
            )
        );
    }

    /**
     * Apply featured filters for featured menus and category menus that limit featured articles.
     *
     * @param   QueryInterface  $query    The query.
     * @param   array           $context  The saved navigation context.
     * @param   string          $now      Current SQL date.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function applyFeaturedFilter(QueryInterface $query, array $context, string $now): void
    {
        $db   = $this->getDatabase();
        $view = $context['view'] ?? 'article';

        if ($view === 'featured') {
            $query->where(
                [
                    $db->quoteName('a.featured') . ' = 1',
                    '(' . $db->quoteName('fp.featured_up') . ' IS NULL OR ' . $db->quoteName('fp.featured_up') . ' <= :frontpageUp)',
                    '(' . $db->quoteName('fp.featured_down') . ' IS NULL OR ' . $db->quoteName('fp.featured_down') . ' >= :frontpageDown)',
                ]
            )
                ->bind(':frontpageUp', $now)
                ->bind(':frontpageDown', $now);

            return;
        }

        if ($view !== 'category') {
            return;
        }

        switch ($context['featured'] ?? 'show') {
            case 'hide':
                $query->extendWhere(
                    'AND',
                    [
                        $db->quoteName('a.featured') . ' = 0',
                        '(' . $db->quoteName('fp.featured_up') . ' IS NOT NULL AND ' . $db->quoteName('fp.featured_up') . ' >= :categoryFeaturedUp)',
                        '(' . $db->quoteName('fp.featured_down') . ' IS NOT NULL AND ' . $db->quoteName('fp.featured_down') . ' <= :categoryFeaturedDown)',
                    ],
                    'OR'
                )
                    ->bind(':categoryFeaturedUp', $now)
                    ->bind(':categoryFeaturedDown', $now);
                break;

            case 'only':
                $query->where(
                    [
                        $db->quoteName('a.featured') . ' = 1',
                        '(' . $db->quoteName('fp.featured_up') . ' IS NULL OR ' . $db->quoteName('fp.featured_up') . ' <= :categoryFeaturedUp)',
                        '(' . $db->quoteName('fp.featured_down') . ' IS NULL OR ' . $db->quoteName('fp.featured_down') . ' >= :categoryFeaturedDown)',
                    ]
                )
                    ->bind(':categoryFeaturedUp', $now)
                    ->bind(':categoryFeaturedDown', $now);
                break;
        }
    }

    /**
     * Check if publish_up/down filters should be applied.
     *
     * @param   mixed  $condition  Published condition.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    private function shouldApplyPublishDates(mixed $condition): bool
    {
        if (is_numeric($condition)) {
            return (int) $condition !== ContentComponent::CONDITION_UNPUBLISHED;
        }

        if (\is_array($condition)) {
            return !\in_array(ContentComponent::CONDITION_UNPUBLISHED, array_map('intval', $condition), true);
        }

        return true;
    }

    /**
     * Build the order clause for the saved context.
     *
     * @param   QueryInterface  $query    The query.
     * @param   array           $context  The saved navigation context.
     * @param   Registry        $params   The current article params.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getOrderBy(QueryInterface $query, array $context, Registry $params): string
    {
        $view          = $context['view'] ?? 'article';
        $contextParams = $context['params'] ?? $params;

        if (\in_array($view, ['archive', 'category', 'featured'], true)) {
            $order = '';

            if ($view !== 'archive') {
                $order .= $this->getPrimaryOrderBy((string) $contextParams->get('orderby_pri', ''));
            }

            $order .= $this->getSecondaryOrderBy(
                $query,
                (string) $contextParams->get('orderby_sec', 'rdate'),
                (string) $contextParams->get('order_date', 'created')
            );

            if ($view === 'category') {
                return $order . ', ' . $this->getDatabase()->quoteName('a.created');
            }

            return $order . ', ' . $this->getDatabase()->quoteName('a.created') . ' DESC';
        }

        return $this->getLegacyOrderBy($query, $params);
    }

    /**
     * Build category ordering SQL.
     *
     * @param   string  $orderby  The category ordering option.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getPrimaryOrderBy(string $orderby): string
    {
        $db = $this->getDatabase();

        return match ($orderby) {
            'alpha'  => $db->quoteName('cc.path') . ', ',
            'ralpha' => $db->quoteName('cc.path') . ' DESC, ',
            'order'  => $db->quoteName('cc.lft') . ', ',
            default  => '',
        };
    }

    /**
     * Build article ordering SQL.
     *
     * @param   QueryInterface  $query      The query.
     * @param   string          $orderby    The article ordering option.
     * @param   string          $orderDate  The date ordering option.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getSecondaryOrderBy(QueryInterface $query, string $orderby, string $orderDate): string
    {
        $db        = $this->getDatabase();
        $queryDate = QueryHelper::getQueryDate($orderDate, $db);
        $author    = 'CASE WHEN ' . $db->quoteName('a.created_by_alias') . ' > ' . $db->quote(' ')
            . ' THEN ' . $db->quoteName('a.created_by_alias') . ' ELSE ' . $db->quoteName('u.name') . ' END';

        return match ($orderby) {
            'date'    => $queryDate,
            'rdate'   => $queryDate . ' DESC',
            'alpha'   => $db->quoteName('a.title'),
            'ralpha'  => $db->quoteName('a.title') . ' DESC',
            'hits'    => $db->quoteName('a.hits') . ' DESC',
            'rhits'   => $db->quoteName('a.hits'),
            'rorder'  => $db->quoteName('a.ordering') . ' DESC',
            'author'  => $author,
            'rauthor' => $author . ' DESC',
            'front'   => $db->quoteName('a.featured') . ' DESC, ' . $db->quoteName('fp.ordering') . ', ' . $queryDate . ' DESC',
            'random'  => $query->rand(),
            default   => $db->quoteName('a.ordering'),
        };
    }

    /**
     * Build the legacy article navigation ordering.
     *
     * @param   QueryInterface  $query   The query.
     * @param   Registry        $params  The current article params.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getLegacyOrderBy(QueryInterface $query, Registry $params): string
    {
        $paramsList = $params->toArray();

        if (\array_key_exists('orderby_sec', $paramsList)) {
            $orderMethod = (string) $params->get('orderby_sec', '');
        } else {
            $orderMethod = (string) $params->get('orderby', '');
        }

        if ($orderMethod === 'front') {
            $orderMethod = '';
        }

        if ($orderMethod === 'hits') {
            return $this->getDatabase()->quoteName('a.hits');
        }

        if ($orderMethod === 'rhits') {
            return $this->getDatabase()->quoteName('a.hits') . ' DESC';
        }

        return $this->getSecondaryOrderBy($query, $orderMethod, (string) $params->get('order_date', 'created'));
    }
}
