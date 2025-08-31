<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content Component Article Model
 *
 * @since  1.5
 */
class FormModel extends \Joomla\Component\Content\Administrator\Model\ArticleModel
{
    /**
     * Model typeAlias string. Used for version history.
     *
     * @var        string
     */
    public $typeAlias = 'com_content.article';

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
        $app   = Factory::getApplication();
        $input = $app->getInput();

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        if ($params && $params->get('enable_category') == 1 && $params->get('catid')) {
            $catId = $params->get('catid');
        } else {
            $catId = 0;
        }

        // Load state from the request.
        $pk = $input->getInt('a_id');
        $this->setState('article.id', $pk);

        $this->setState('article.catid', $input->getInt('catid', $catId));

        $return = $input->get('return', '', 'base64');
        $this->setState('return_page', base64_decode($return));

        $this->setState('layout', $input->getString('layout'));
    }

    /**
     * Method to get article data.
     *
     * @param   integer  $itemId  The id of the article.
     *
     * @return  mixed  Content item data object on success, false on failure.
     */
    public function getItem($itemId = null)
    {
        $itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('article.id');

        // Get a row instance.
        $table = $this->getTable();

        // Attempt to load the row.
        $return = $table->load($itemId);

        // Check for a table object error.
        if ($return === false && $table->getError()) {
            $this->setError($table->getError());

            return false;
        }

        $properties = $table->getProperties(1);
        $value      = ArrayHelper::toObject($properties);

        // Convert attrib field to Registry.
        $value->params = new Registry($value->attribs);

        // Compute selected asset permissions.
        $user   = $this->getCurrentUser();
        $userId = $user->id;
        $asset  = 'com_content.article.' . $value->id;

        // Check general edit permission first.
        if ($user->authorise('core.edit', $asset)) {
            $value->params->set('access-edit', true);
        } elseif (!empty($userId) && $user->authorise('core.edit.own', $asset)) {
            // Now check if edit.own is available.
            // Check for a valid user and that they are the owner.
            if ($userId == $value->created_by) {
                $value->params->set('access-edit', true);
            }
        }

        // Check edit state permission.
        if ($itemId) {
            // Existing item
            $value->params->set('access-change', $user->authorise('core.edit.state', $asset));
        } else {
            // New item.
            $catId = (int) $this->getState('article.catid');

            if ($catId) {
                $value->params->set('access-change', $user->authorise('core.edit.state', 'com_content.category.' . $catId));
                $value->catid = $catId;
            } else {
                $value->params->set('access-change', $user->authorise('core.edit.state', 'com_content'));
            }
        }

        $value->articletext = $value->introtext;

        if (!empty($value->fulltext)) {
            $value->articletext .= '<hr id="system-readmore">' . $value->fulltext;
        }

        // Convert the metadata field to an array.
        $registry        = new Registry($value->metadata);
        $value->metadata = $registry->toArray();

        if ($itemId) {
            $value->tags = new TagsHelper();
            $value->tags->getTagIds($value->id, 'com_content.article');
            $value->metadata['tags'] = $value->tags;

            $value->featured_up   = null;
            $value->featured_down = null;

            if ($value->featured) {
                // Get featured dates.
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select(
                        [
                            $db->quoteName('featured_up'),
                            $db->quoteName('featured_down'),
                        ]
                    )
                    ->from($db->quoteName('#__content_frontpage'))
                    ->where($db->quoteName('content_id') . ' = :id')
                    ->bind(':id', $value->id, ParameterType::INTEGER);

                $featured = $db->setQuery($query)->loadObject();

                if ($featured) {
                    $value->featured_up   = $featured->featured_up;
                    $value->featured_down = $featured->featured_down;
                }
            }
        }

        return $value;
    }

    /**
     * Get the return URL.
     *
     * @return  string  The return URL.
     *
     * @since   1.6
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('return_page', ''));
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   3.2
     */
    public function save($data)
    {
        // Associations are not edited in frontend ATM so we have to inherit them
        if (
            Associations::isEnabled() && !empty($data['id'])
            && $associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $data['id'])
        ) {
            foreach ($associations as $tag => $associated) {
                $associations[$tag] = (int) $associated->id;
            }

            $data['associations'] = $associations;
        }

        if (!Multilanguage::isEnabled()) {
            $data['language'] = '*';
        }

        return parent::save($data);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|boolean  A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = parent::getForm($data, $loadData);

        if (empty($form)) {
            return false;
        }

        $app  = Factory::getApplication();
        $user = $app->getIdentity();

        // On edit article, we get ID of article from article.id state, but on save, we use data from input
        $id = (int) $this->getState('article.id', $app->getInput()->getInt('a_id'));

        // Existing record. We can't edit the category in frontend if not edit.state.
        if ($id > 0 && !$user->authorise('core.edit.state', 'com_content.article.' . $id)) {
            $form->setFieldAttribute('catid', 'readonly', 'true');
            $form->setFieldAttribute('catid', 'required', 'false');
            $form->setFieldAttribute('catid', 'filter', 'unset');
        }

        // Prevent messing with article language and category when editing existing article with associations
        if ($this->getState('article.id') && Associations::isEnabled()) {
            $associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $id);

            // Make fields read only
            if (!empty($associations)) {
                $form->setFieldAttribute('language', 'readonly', 'true');
                $form->setFieldAttribute('catid', 'readonly', 'true');
                $form->setFieldAttribute('language', 'filter', 'unset');
                $form->setFieldAttribute('catid', 'filter', 'unset');
            }
        }

        return $form;
    }

    /**
     * Allows preprocessing of the JForm object.
     *
     * @param   Form    $form   The form object
     * @param   array   $data   The data to be merged into the form object
     * @param   string  $group  The plugin group to be executed
     *
     * @return  void
     *
     * @since   3.7.0
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        $params = $this->getState()->get('params');

        if ($params && $params->get('enable_category') == 1 && $params->get('catid')) {
            $form->setFieldAttribute('catid', 'default', $params->get('catid'));
            $form->setFieldAttribute('catid', 'readonly', 'true');

            if (Multilanguage::isEnabled()) {
                $categoryId = (int) $params->get('catid');

                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select($db->quoteName('language'))
                    ->from($db->quoteName('#__categories'))
                    ->where($db->quoteName('id') . ' = :categoryId')
                    ->bind(':categoryId', $categoryId, ParameterType::INTEGER);
                $db->setQuery($query);

                $result = $db->loadResult();

                if ($result != '*') {
                    $form->setFieldAttribute('language', 'readonly', 'true');
                    $form->setFieldAttribute('language', 'default', $result);
                }
            }
        }

        if (!Multilanguage::isEnabled()) {
            $form->setFieldAttribute('language', 'type', 'hidden');
            $form->setFieldAttribute('language', 'default', '*');
        }

        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function getTable($name = 'Article', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }
}
