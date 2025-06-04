<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\Model;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Single item model for a contact
 *
 * @package     Joomla.Site
 * @subpackage  com_contact
 * @since       1.5
 */
class ContactModel extends FormModel
{
    /**
     * The name of the view for a single item
     *
     * @var    string
     * @since  1.6
     */
    protected $view_item = 'contact';

    /**
     * A loaded item
     *
     * @var    \stdClass[]
     * @since  1.6
     */
    protected $_item = [];

    /**
     * Model context string.
     *
     * @var     string
     */
    protected $_context = 'com_contact.contact';

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState()
    {
        /** @var SiteApplication $app */
        $app = Factory::getContainer()->get(SiteApplication::class);

        if (Factory::getApplication()->isClient('api')) {
            // @todo: remove this
            $app->loadLanguage();
            $this->setState('contact.id', Factory::getApplication()->getInput()->post->getInt('id'));
        } else {
            $this->setState('contact.id', $app->getInput()->getInt('id'));
        }

        $this->setState('params', $app->getParams());

        $user = $this->getCurrentUser();

        if ((!$user->authorise('core.edit.state', 'com_contact')) && (!$user->authorise('core.edit', 'com_contact'))) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }
    }

    /**
     * Method to get the contact form.
     * The base form is loaded from XML and then an event is fired
     *
     * @param   array    $data      An optional array of data for the form to interrogate.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form  A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm('com_contact.contact', 'contact', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        $temp    = clone $this->getState('params');
        $contact = $this->getItem($this->getState('contact.id'));
        $active  = Factory::getContainer()->get(SiteApplication::class)->getMenu()->getActive();

        if ($active) {
            // If the current view is the active item and a contact view for this contact, then the menu item params take priority
            if (strpos($active->link, 'view=contact') && strpos($active->link, '&id=' . (int) $contact->id)) {
                // $contact->params are the contact params, $temp are the menu item params
                // Merge so that the menu item params take priority
                $contact->params->merge($temp);
            } else {
                // Current view is not a single contact, so the contact params take priority here
                // Merge the menu item params with the contact params so that the contact params take priority
                $temp->merge($contact->params);
                $contact->params = $temp;
            }
        } else {
            // Merge so that contact params take priority
            $temp->merge($contact->params);
            $contact->params = $temp;
        }

        if (!$contact->params->get('show_email_copy', 0)) {
            $form->removeField('contact_email_copy');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array    The default data is an empty array.
     *
     * @since   1.6.2
     */
    protected function loadFormData()
    {
        $data = (array) Factory::getApplication()->getUserState('com_contact.contact.data', []);

        if (empty($data['language']) && Multilanguage::isEnabled()) {
            $data['language'] = Factory::getLanguage()->getTag();
        }

        // Add contact catid to contact form data, so fields plugin can work properly
        if (empty($data['catid'])) {
            $data['catid'] = $this->getItem()->catid;
        }

        $this->preprocessData('com_contact.contact', $data);

        return $data;
    }

    /**
     * Gets a contact
     *
     * @param   integer  $pk  Id for the contact
     *
     * @return  mixed \stdClass or null
     *
     * @since   1.6.0
     */
    public function getItem($pk = null)
    {
        $pk = $pk ?: (int) $this->getState('contact.id');

        if (!isset($this->_item[$pk])) {
            try {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true);

                $query->select($this->getState('item.select', 'a.*'))
                    ->select($this->getSlugColumn($query, 'a.id', 'a.alias') . ' AS slug')
                    ->select($this->getSlugColumn($query, 'c.id', 'c.alias') . ' AS catslug')
                    ->from($db->quoteName('#__contact_details', 'a'))

                    // Join on category table.
                    ->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access')
                    ->leftJoin($db->quoteName('#__categories', 'c'), 'c.id = a.catid')

                    // Join over the categories to get parent category titles
                    ->select('parent.title AS parent_title, parent.id AS parent_id, parent.path AS parent_route, parent.alias AS parent_alias')
                    ->leftJoin($db->quoteName('#__categories', 'parent'), 'parent.id = c.parent_id')
                    ->where($db->quoteName('a.id') . ' = :id')
                    ->bind(':id', $pk, ParameterType::INTEGER);

                // Filter by start and end dates.
                $nowDate = Factory::getDate()->toSql();

                // Filter by published state.
                $published = $this->getState('filter.published');
                $archived  = $this->getState('filter.archived');

                if (is_numeric($published)) {
                    $queryString = $db->quoteName('a.published') . ' = :published';

                    if ($archived !== null) {
                        $queryString = '(' . $queryString . ' OR ' . $db->quoteName('a.published') . ' = :archived)';
                        $query->bind(':archived', $archived, ParameterType::INTEGER);
                    }

                    $query->where($queryString)
                        ->where('(' . $db->quoteName('a.publish_up') . ' IS NULL OR ' . $db->quoteName('a.publish_up') . ' <= :publish_up)')
                        ->where('(' . $db->quoteName('a.publish_down') . ' IS NULL OR ' . $db->quoteName('a.publish_down') . ' >= :publish_down)')
                        ->bind(':published', $published, ParameterType::INTEGER)
                        ->bind(':publish_up', $nowDate)
                        ->bind(':publish_down', $nowDate);
                }

                $db->setQuery($query);
                $data = $db->loadObject();

                if (empty($data)) {
                    throw new \Exception(Text::_('COM_CONTACT_ERROR_CONTACT_NOT_FOUND'), 404);
                }

                // Check for published state if filter set.
                if ((is_numeric($published) || is_numeric($archived)) && (($data->published != $published) && ($data->published != $archived))) {
                    throw new \Exception(Text::_('COM_CONTACT_ERROR_CONTACT_NOT_FOUND'), 404);
                }

                /**
                 * In case some entity params have been set to "use global", those are
                 * represented as an empty string and must be "overridden" by merging
                 * the component and / or menu params here.
                 */
                $registry = new Registry($data->params);

                $data->params = clone $this->getState('params');
                $data->params->merge($registry);

                $registry       = new Registry($data->metadata);
                $data->metadata = $registry;

                // Some contexts may not use tags data at all, so we allow callers to disable loading tag data
                if ($this->getState('load_tags', true)) {
                    $data->tags = new TagsHelper();
                    $data->tags->getItemTags('com_contact.contact', $data->id);
                }

                // Compute access permissions.
                if ($this->getState('filter.access')) {
                    // If the access filter has been set, we already know this user can view.
                    $data->params->set('access-view', true);
                } else {
                    // If no access filter is set, the layout takes some responsibility for display of limited information.
                    $user   = $this->getCurrentUser();
                    $groups = $user->getAuthorisedViewLevels();

                    if ($data->catid == 0 || $data->category_access === null) {
                        $data->params->set('access-view', \in_array($data->access, $groups));
                    } else {
                        $data->params->set('access-view', \in_array($data->access, $groups) && \in_array($data->category_access, $groups));
                    }
                }

                $this->_item[$pk] = $data;
            } catch (\Exception $e) {
                if ($e->getCode() == 404) {
                    // Need to go through the error handler to allow Redirect to work.
                    throw $e;
                }

                $this->setError($e);
                $this->_item[$pk] = false;
            }
        }

        if ($this->_item[$pk]) {
            $this->buildContactExtendedData($this->_item[$pk]);
        }

        return $this->_item[$pk];
    }

    /**
     * Load extended data (profile, articles) for a contact
     *
     * @param   object  $contact  The contact object
     *
     * @return  void
     */
    protected function buildContactExtendedData($contact)
    {
        $db        = $this->getDatabase();
        $nowDate   = Factory::getDate()->toSql();
        $user      = $this->getCurrentUser();
        $groups    = $user->getAuthorisedViewLevels();
        $published = $this->getState('filter.published');
        $query     = $db->getQuery(true);

        // If we are showing a contact list, then the contact parameters take priority
        // So merge the contact parameters with the merged parameters
        if ($this->getState('params')->get('show_contact_list')) {
            $this->getState('params')->merge($contact->params);
        }

        // Get the com_content articles by the linked user
        if ((int) $contact->user_id && $this->getState('params')->get('show_articles')) {
            $query->select('a.id')
                ->select('a.title')
                ->select('a.state')
                ->select('a.access')
                ->select('a.catid')
                ->select('a.created')
                ->select('a.language')
                ->select('a.publish_up')
                ->select('a.introtext')
                ->select('a.images')
                ->select($this->getSlugColumn($query, 'a.id', 'a.alias') . ' AS slug')
                ->select($this->getSlugColumn($query, 'c.id', 'c.alias') . ' AS catslug')
                ->from($db->quoteName('#__content', 'a'))
                ->leftJoin($db->quoteName('#__categories', 'c') . ' ON a.catid = c.id')
                ->where($db->quoteName('a.created_by') . ' = :created_by')
                ->whereIn($db->quoteName('a.access'), $groups)
                ->bind(':created_by', $contact->user_id, ParameterType::INTEGER)
                ->order('a.publish_up DESC');

            // Filter per language if plugin published
            if (Multilanguage::isEnabled()) {
                $language = [Factory::getLanguage()->getTag(), '*'];
                $query->whereIn($db->quoteName('a.language'), $language, ParameterType::STRING);
            }

            if (is_numeric($published)) {
                $query->where('a.state IN (1,2)')
                    ->where('(' . $db->quoteName('a.publish_up') . ' IS NULL' .
                        ' OR ' . $db->quoteName('a.publish_up') . ' <= :now1)')
                    ->where('(' . $db->quoteName('a.publish_down') . ' IS NULL' .
                        ' OR ' . $db->quoteName('a.publish_down') . ' >= :now2)')
                    ->bind([':now1', ':now2'], $nowDate);
            }

            // Number of articles to display from config/menu params
            $articles_display_num = $this->getState('params')->get('articles_display_num', 10);

            // Use contact setting?
            if ($articles_display_num === 'use_contact') {
                $articles_display_num = $contact->params->get('articles_display_num', 10);

                // Use global?
                if ((string) $articles_display_num === '') {
                    $articles_display_num = ComponentHelper::getParams('com_contact')->get('articles_display_num', 10);
                }
            }

            $query->setLimit((int) $articles_display_num);
            $db->setQuery($query);
            $contact->articles = $db->loadObjectList();
        } else {
            $contact->articles = null;
        }

        // Get the profile information for the linked user
        $userModel = $this->bootComponent('com_users')->getMVCFactory()
            ->createModel('User', 'Administrator', ['ignore_request' => true]);
        $data = $userModel->getItem((int) $contact->user_id);

        PluginHelper::importPlugin('user');

        // Get the form.
        Form::addFormPath(JPATH_SITE . '/components/com_users/forms');

        $form = Form::getInstance('com_users.profile', 'profile');

        // Trigger the form preparation event.
        Factory::getApplication()->triggerEvent('onContentPrepareForm', [$form, $data]);

        // Trigger the data preparation event.
        Factory::getApplication()->triggerEvent('onContentPrepareData', ['com_users.profile', $data]);

        // Load the data into the form after the plugins have operated.
        $form->bind($data);
        $contact->profile = $form;
    }

    /**
     * Generate column expression for slug or catslug.
     *
     * @param   QueryInterface  $query  Current query instance.
     * @param   string          $id     Column id name.
     * @param   string          $alias  Column alias name.
     *
     * @return  string
     *
     * @since   4.0.0
     */
    private function getSlugColumn($query, $id, $alias)
    {
        return 'CASE WHEN '
            . $query->charLength($alias, '!=', '0')
            . ' THEN '
            . $query->concatenate([$query->castAsChar($id), $alias], ':')
            . ' ELSE '
            . $query->castAsChar($id) . ' END';
    }

    /**
     * Increment the hit counter for the contact.
     *
     * @param   integer  $pk  Optional primary key of the contact to increment.
     *
     * @return  boolean  True if successful; false otherwise and internal error set.
     *
     * @since   3.0
     */
    public function hit($pk = 0)
    {
        $input    = Factory::getApplication()->getInput();
        $hitcount = $input->getInt('hitcount', 1);

        if ($hitcount) {
            $pk = $pk ?: (int) $this->getState('contact.id');

            $table = $this->getTable('Contact');
            $table->hit($pk);
        }

        return true;
    }
}
