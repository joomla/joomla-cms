<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Associations\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Category;
use Joomla\Component\Associations\Administrator\Helper\AssociationsHelper;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Methods supporting a list of article records.
 *
 * @since  3.7.0
 */
class AssociationsModel extends ListModel
{
    /**
     * Override parent constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   3.7
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'title',
                'ordering',
                'itemtype',
                'language',
                'association',
                'menutype',
                'menutype_title',
                'level',
                'state',
                'category_id',
                'category_title',
                'access',
                'access_level',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   3.7.0
     */
    protected function populateState($ordering = 'ordering', $direction = 'asc')
    {
        $app = Factory::getApplication();

        $forcedLanguage = $app->getInput()->get('forcedLanguage', '', 'cmd');
        $forcedItemType = $app->getInput()->get('forcedItemType', '', 'string');

        // Adjust the context to support modal layouts.
        if ($layout = $app->getInput()->get('layout')) {
            $this->context .= '.' . $layout;
        }

        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.' . $forcedLanguage;
        }

        // Adjust the context to support forced component item types.
        if ($forcedItemType) {
            $this->context .= '.' . $forcedItemType;
        }

        $this->setState('itemtype', $this->getUserStateFromRequest($this->context . '.itemtype', 'itemtype', '', 'string'));
        $this->setState('language', $this->getUserStateFromRequest($this->context . '.language', 'language', '', 'string'));

        // List state information.
        parent::populateState($ordering, $direction);

        // Force a language.
        if (!empty($forcedLanguage)) {
            $this->setState('language', $forcedLanguage);
        }

        // Force a component item type.
        if (!empty($forcedItemType)) {
            $this->setState('itemtype', $forcedItemType);
        }
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since  3.7.0
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('itemtype');
        $id .= ':' . $this->getState('language');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.category_id');
        $id .= ':' . $this->getState('filter.menutype');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.level');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  QueryInterface|boolean
     *
     * @since  3.7.0
     */
    protected function getListQuery()
    {
        $type         = null;

        [$extensionName, $typeName] = explode('.', $this->state->get('itemtype'), 2);

        $extension = AssociationsHelper::getSupportedExtension($extensionName);
        $types     = $extension->get('types');

        if (\array_key_exists($typeName, $types)) {
            $type = $types[$typeName];
        }

        if (\is_null($type)) {
            return false;
        }

        // Create a new query object.
        $user     = $this->getCurrentUser();
        $db       = $this->getDatabase();
        $query    = $db->getQuery(true);

        $details = $type->get('details');

        if (!\array_key_exists('support', $details)) {
            return false;
        }

        $support = $details['support'];

        if (!\array_key_exists('fields', $details)) {
            return false;
        }

        $fields = $details['fields'];

        // Main query.
        $query->select($db->quoteName($fields['id'], 'id'))
            ->select($db->quoteName($fields['title'], 'title'))
            ->select($db->quoteName($fields['alias'], 'alias'));

        if (!\array_key_exists('tables', $details)) {
            return false;
        }

        $tables = $details['tables'];

        foreach ($tables as $key => $table) {
            $query->from($db->quoteName($table, $key));
        }

        if (!\array_key_exists('joins', $details)) {
            return false;
        }

        $joins = $details['joins'];

        foreach ($joins as $join) {
            $query->join($join['type'], $db->quoteName($join['condition']));
        }

        // Join over the language.
        $query->select($db->quoteName($fields['language'], 'language'))
            ->select($db->quoteName('l.title', 'language_title'))
            ->select($db->quoteName('l.image', 'language_image'))
            ->join(
                'LEFT',
                $db->quoteName('#__languages', 'l'),
                $db->quoteName('l.lang_code') . ' = ' . $db->quoteName($fields['language'])
            );
        $extensionNameItem = $extensionName . '.item';

        // Join over the associations.
        $query->select('COUNT(' . $db->quoteName('asso2.id') . ') > 1 AS ' . $db->quoteName('association'))
            ->join(
                'LEFT',
                $db->quoteName('#__associations', 'asso'),
                $db->quoteName('asso.id') . ' = ' . $db->quoteName($fields['id'])
                . ' AND ' . $db->quoteName('asso.context') . ' = :context'
            )
            ->join(
                'LEFT',
                $db->quoteName('#__associations', 'asso2'),
                $db->quoteName('asso2.key') . ' = ' . $db->quoteName('asso.key')
            )
            ->bind(':context', $extensionNameItem);

        // Prepare the group by clause.
        $groupby = [
            $fields['id'],
            $fields['title'],
            $fields['alias'],
            $fields['language'],
            'l.title',
            'l.image',
        ];

        // Select author for ACL checks.
        if (!empty($fields['created_user_id'])) {
            $query->select($db->quoteName($fields['created_user_id'], 'created_user_id'));

            $groupby[] = $fields['created_user_id'];
        }

        // Select checked out data for check in checkins.
        if (!empty($fields['checked_out']) && !empty($fields['checked_out_time'])) {
            $query->select($db->quoteName($fields['checked_out'], 'checked_out'))
                ->select($db->quoteName($fields['checked_out_time'], 'checked_out_time'));

            // Join over the users.
            $query->select($db->quoteName('u.name', 'editor'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__users', 'u'),
                    $db->quoteName('u.id') . ' = ' . $db->quoteName($fields['checked_out'])
                );

            $groupby[] = 'u.name';
            $groupby[] = $fields['checked_out'];
            $groupby[] = $fields['checked_out_time'];
        }

        // If component item type supports ordering, select the ordering also.
        if (!empty($fields['ordering'])) {
            $query->select($db->quoteName($fields['ordering'], 'ordering'));

            $groupby[] = $fields['ordering'];
        }

        // If component item type supports state, select the item state also.
        if (!empty($fields['state'])) {
            $query->select($db->quoteName($fields['state'], 'state'));

            $groupby[] = $fields['state'];
        }

        // If component item type supports level, select the level also.
        if (!empty($fields['level'])) {
            $query->select($db->quoteName($fields['level'], 'level'));

            $groupby[] = $fields['level'];
        }

        // If component item type supports categories, select the category also.
        if (!empty($fields['catid'])) {
            $query->select($db->quoteName($fields['catid'], 'catid'));

            // Join over the categories.
            $query->select($db->quoteName('c.title', 'category_title'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__categories', 'c'),
                    $db->quoteName('c.id') . ' = ' . $db->quoteName($fields['catid'])
                );

            $groupby[] = 'c.title';
            $groupby[] = $fields['catid'];
        }

        // If component item type supports menu type, select the menu type also.
        if (!empty($fields['menutype'])) {
            $query->select($db->quoteName($fields['menutype'], 'menutype'));

            // Join over the menu types.
            $query->select($db->quoteName('mt.title', 'menutype_title'))
                ->select($db->quoteName('mt.id', 'menutypeid'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__menu_types', 'mt'),
                    $db->quoteName('mt.menutype') . ' = ' . $db->quoteName($fields['menutype'])
                );

            $groupby[] = 'mt.title';
            $groupby[] = 'mt.id';
            $groupby[] = $fields['menutype'];
        }

        // If component item type supports access level, select the access level also.
        if (\array_key_exists('acl', $support) && $support['acl'] == true && !empty($fields['access'])) {
            $query->select($db->quoteName($fields['access'], 'access'));

            // Join over the access levels.
            $query->select($db->quoteName('ag.title', 'access_level'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__viewlevels', 'ag'),
                    $db->quoteName('ag.id') . ' = ' . $db->quoteName($fields['access'])
                );

            $groupby[] = 'ag.title';
            $groupby[] = $fields['access'];

            // Implement View Level Access.
            if (!$user->authorise('core.admin', $extensionName)) {
                $groups = $user->getAuthorisedViewLevels();
                $query->whereIn($db->quoteName($fields['access']), $groups);
            }
        }

        // If component item type is menus we need to remove the root item and the administrator menu.
        if ($extensionName === 'com_menus') {
            $query->where($db->quoteName($fields['id']) . ' > 1')
                ->where($db->quoteName('a.client_id') . ' = 0');
        }

        // If component item type is category we need to remove all other component categories.
        if ($typeName === 'category') {
            $query->where($db->quoteName('a.extension') . ' = :extensionname')
                ->bind(':extensionname', $extensionName);
        } elseif ($typeNameExploded = explode('.', $typeName)) {
            if (\count($typeNameExploded) > 1 && array_pop($typeNameExploded) === 'category') {
                $section              = implode('.', $typeNameExploded);
                $extensionNameSection = $extensionName . '.' . $section;
                $query->where($db->quoteName('a.extension') . ' = :extensionsection')
                    ->bind(':extensionsection', $extensionNameSection);
            }
        }

        // Filter on the language.
        if ($language = $this->getState('language')) {
            $query->where($db->quoteName($fields['language']) . ' = :language')
                ->bind(':language', $language);
        }

        // Filter by item state.
        $state = $this->getState('filter.state');

        if (is_numeric($state)) {
            $state = (int) $state;
            $query->where($db->quoteName($fields['state']) . ' = :state')
                ->bind(':state', $state, ParameterType::INTEGER);
        } elseif ($state === '') {
            $query->whereIn($db->quoteName($fields['state']), [0, 1]);
        }

        // Filter on the category.
        $baselevel = 1;

        if ($categoryId = $this->getState('filter.category_id')) {
            $categoryTable = new Category($db);
            $categoryTable->load($categoryId);
            $baselevel = (int) $categoryTable->level;

            $lft = (int) $categoryTable->lft;
            $rgt = (int) $categoryTable->rgt;
            $query->where($db->quoteName('c.lft') . ' >= :lft')
                ->where($db->quoteName('c.rgt') . ' <= :rgt')
                ->bind(':lft', $lft, ParameterType::INTEGER)
                ->bind(':rgt', $rgt, ParameterType::INTEGER);
        }

        // Filter on the level.
        if ($level = $this->getState('filter.level')) {
            $queryLevel = ((int) $level + (int) $baselevel - 1);
            $query->where($db->quoteName('a.level') . ' <= :alevel')
                ->bind(':alevel', $queryLevel, ParameterType::INTEGER);
        }

        // Filter by menu type.
        if ($menutype = $this->getState('filter.menutype')) {
            $query->where($db->quoteName($fields['menutype']) . ' = :menutype2')
                ->bind(':menutype2', $menutype);
        }

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $access = (int) $access;
            $query->where($db->quoteName($fields['access']) . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        }

        // Filter by search in name.
        if ($search = $this->getState('filter.search')) {
            if (stripos($search, 'id:') === 0) {
                $search = (int) substr($search, 3);
                $query->where($db->quoteName($fields['id']) . ' = :searchid')
                    ->bind(':searchid', $search, ParameterType::INTEGER);
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->where('(' . $db->quoteName($fields['title']) . ' LIKE :title'
                    . ' OR ' . $db->quoteName($fields['alias']) . ' LIKE :alias)')
                    ->bind(':title', $search)
                    ->bind(':alias', $search);
            }
        }

        // Add the group by clause
        $query->group($db->quoteName($groupby));

        // Add the list ordering clause
        $listOrdering  = $this->state->get('list.ordering', 'id');
        $orderDirn     = $this->state->get('list.direction', 'ASC');

        $query->order($db->escape($listOrdering) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    /**
     * Delete associations from #__associations table.
     *
     * @param   string  $context  The associations context. Empty for all.
     * @param   string  $key      The associations key. Empty for all.
     *
     * @return  boolean  True on success.
     *
     * @since  3.7.0
     */
    public function purge($context = '', $key = '')
    {
        $app   = Factory::getApplication();
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)->delete($db->quoteName('#__associations'));

        // Filter by associations context.
        if ($context) {
            $query->where($db->quoteName('context') . ' = :context')
                ->bind(':context', $context);
        }

        // Filter by key.
        if ($key) {
            $query->where($db->quoteName('key') . ' = :key')
                ->bind(':key', $key);
        }

        $db->setQuery($query);

        try {
            $db->execute();
        } catch (ExecutionFailureException) {
            $app->enqueueMessage(Text::_('COM_ASSOCIATIONS_PURGE_FAILED'), 'error');

            return false;
        }

        $app->enqueueMessage(
            Text::_((int) $db->getAffectedRows() > 0 ? 'COM_ASSOCIATIONS_PURGE_SUCCESS' : 'COM_ASSOCIATIONS_PURGE_NONE'),
            'message'
        );

        return true;
    }

    /**
     * Delete orphans from the #__associations table.
     *
     * @param   string  $context  The associations context. Empty for all.
     * @param   string  $key      The associations key. Empty for all.
     *
     * @return  boolean  True on success
     *
     * @since  3.7.0
     */
    public function clean($context = '', $key = '')
    {
        $app   = Factory::getApplication();
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('key') . ', COUNT(*)')
            ->from($db->quoteName('#__associations'))
            ->group($db->quoteName('key'))
            ->having('COUNT(*) = 1');

        // Filter by associations context.
        if ($context) {
            $query->where($db->quoteName('context') . ' = :context')
                ->bind(':context', $context);
        }

        // Filter by key.
        if ($key) {
            $query->where($db->quoteName('key') . ' = :key')
                ->bind(':key', $key);
        }

        $db->setQuery($query);

        $assocKeys = $db->loadObjectList();

        $count = 0;

        // We have orphans. Let's delete them.
        foreach ($assocKeys as $value) {
            $query->clear()
                ->delete($db->quoteName('#__associations'))
                ->where($db->quoteName('key') . ' = :valuekey')
                ->bind(':valuekey', $value->key);

            $db->setQuery($query);

            try {
                $db->execute();
            } catch (ExecutionFailureException) {
                $app->enqueueMessage(Text::_('COM_ASSOCIATIONS_DELETE_ORPHANS_FAILED'), 'error');

                return false;
            }

            $count += (int) $db->getAffectedRows();
        }

        $app->enqueueMessage(
            Text::_($count > 0 ? 'COM_ASSOCIATIONS_DELETE_ORPHANS_SUCCESS' : 'COM_ASSOCIATIONS_DELETE_ORPHANS_NONE'),
            'message'
        );

        return true;
    }
}
