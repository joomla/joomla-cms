<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Administrator\Field\Modal;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ModalSelectField;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports a modal newsfeeds picker.
 *
 * @since  1.6
 */
class NewsfeedField extends ModalSelectField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'Modal_Newsfeed';

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value.
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   5.1.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        // Check if the value consist with id:alias, extract the id only
        if ($value && str_contains($value, ':')) {
            [$id]  = explode(':', $value, 2);
            $value = (int) $id;
        }

        $result = parent::setup($element, $value, $group);

        if (!$result) {
            return $result;
        }

        Factory::getApplication()->getLanguage()->load('com_newsfeeds', JPATH_ADMINISTRATOR);

        $languages = LanguageHelper::getContentLanguages([0, 1], false);
        $language  = (string) $this->element['language'];

        // Prepare enabled actions
        $this->canDo['propagate']  = ((string) $this->element['propagate'] == 'true') && \count($languages) > 2;

        // Prepare Urls
        $linkitems = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkitems->setQuery([
            'option'                => 'com_newsfeeds',
            'view'                  => 'newsfeeds',
            'layout'                => 'modal',
            'tmpl'                  => 'component',
            Session::getFormToken() => 1,
        ]);
        $linkItem = clone $linkitems;
        $linkItem->setVar('view', 'newsfeed');
        $linkCheckin = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkCheckin->setQuery([
            'option'                => 'com_newsfeeds',
            'task'                  => 'newsfeeds.checkin',
            'format'                => 'json',
            Session::getFormToken() => 1,
        ]);

        if ($language) {
            $linkitems->setVar('forcedLanguage', $language);
            $linkItem->setVar('forcedLanguage', $language);

            $modalTitle = Text::_('COM_NEWSFEEDS_SELECT_A_FEED') . ' &#8212; ' . $this->getTitle();

            $this->dataAttributes['data-language'] = $language;
        } else {
            $modalTitle = Text::_('COM_NEWSFEEDS_SELECT_A_FEED');
        }

        $urlSelect = $linkitems;
        $urlEdit   = clone $linkItem;
        $urlEdit->setVar('task', 'newsfeed.edit');
        $urlNew    = clone $linkItem;
        $urlNew->setVar('task', 'newsfeed.add');

        $this->urls['select']  = (string) $urlSelect;
        $this->urls['new']     = (string) $urlNew;
        $this->urls['edit']    = (string) $urlEdit;
        $this->urls['checkin'] = (string) $linkCheckin;

        // Prepare titles
        $this->modalTitles['select']  = $modalTitle;
        $this->modalTitles['new']     = Text::_('COM_NEWSFEEDS_NEW_NEWSFEED');
        $this->modalTitles['edit']    = Text::_('COM_NEWSFEEDS_EDIT_NEWSFEED');

        $this->hint = $this->hint ?: Text::_('COM_NEWSFEEDS_SELECT_A_FEED');

        return $result;
    }

    /**
     * Method to retrieve the title of selected item.
     *
     * @return string
     *
     * @since   5.1.0
     */
    protected function getValueTitle()
    {
        $value = (int) $this->value ?: '';
        $title = '';

        if ($value) {
            try {
                $db    = $this->getDatabase();
                $query = $db->createQuery()
                    ->select($db->quoteName('name'))
                    ->from($db->quoteName('#__newsfeeds'))
                    ->where($db->quoteName('id') . ' = :value')
                    ->bind(':value', $value, ParameterType::INTEGER);
                $db->setQuery($query);

                $title = $db->loadResult();
            } catch (\Throwable $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return $title ?: $value;
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since 5.1.0
     */
    protected function getLayoutData()
    {
        $data             = parent::getLayoutData();
        $data['language'] = (string) $this->element['language'];

        return $data;
    }

    /**
     * Get the renderer
     *
     * @param   string  $layoutId  Id to load
     *
     * @return  FileLayout
     *
     * @since   5.1.0
     */
    protected function getRenderer($layoutId = 'default')
    {
        $layout = parent::getRenderer($layoutId);
        $layout->setComponent('com_newsfeeds');
        $layout->setClient(1);

        return $layout;
    }
}
