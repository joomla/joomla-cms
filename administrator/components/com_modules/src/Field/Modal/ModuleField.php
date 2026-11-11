<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Field\Modal;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ModalSelectField;
use Joomla\CMS\Form\FormField;
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
 * Supports a modal menu item picker.
 *
 * @since  6.1.0
 */
class ModuleField extends ModalSelectField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   6.1.0
     */
    protected $type = 'Modal_Module';


    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                        For example if the field has name="foo" and the group value is set to "bar" then the
     *                                      full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   6.1.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        // Check if the value consist with id:alias, extract the id only
        if ($value && str_contains($value, ':')) {
            [$id]  = explode(':', $value, 2);
            $value = (int) $id;
        }

        $return = parent::setup($element, $value, $group);

        if (!$return) {
            return $return;
        }

        $app = Factory::getApplication();

        $app->getLanguage()->load('com_modules', JPATH_ADMINISTRATOR);

        $languages = LanguageHelper::getContentLanguages([0, 1], false);
        $language  = (string) $this->element['language'];
        $clientId  = (int) $this->element['clientid'];

        // Prepare enabled actions
        $this->canDo['propagate']  = ((string) $this->element['propagate'] === 'true') && \count($languages) > 2;

        // Creating/editing module items is not supported in frontend.
        if (!$app->isClient('administrator')) {
            $this->canDo['new']  = false;
            $this->canDo['edit'] = false;
        }

        // Prepare Urls
        $linkItems = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkItems->setQuery([
            'option'                => 'com_modules',
            'view'                  => 'modules',
            'layout'                => 'modal',
            'tmpl'                  => 'component',
            'client_id'             => $clientId,
            'eid'                   => $this->getExtensionId(),
            Session::getFormToken() => 1,
        ]);
        $linkItem = clone $linkItems;
        $linkItem->setVar('view', 'module');
        $linkCheckin = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkCheckin->setQuery([
            'option'                => 'com_modules',
            'task'                  => 'modules.checkin',
            'format'                => 'json',
            Session::getFormToken() => 1,
        ]);

        if ($language) {
            $linkItems->setVar('forcedLanguage', $language);
            $linkItem->setVar('forcedLanguage', $language);

            $modalTitle = Text::_('COM_MODULES_SELECT_A_MODULE') . ' &#8212; ' . $this->getTitle();

            $this->dataAttributes['data-language'] = $language;
        } else {
            $modalTitle = Text::_('COM_MODULES_SELECT_A_MODULE');
        }

        $urlSelect = $linkItems;
        $urlEdit   = clone $linkItem;
        $urlEdit->setVar('task', 'module.edit');
        $urlNew    = clone $linkItem;
        $urlNew->setVar('task', 'module.add');

        $this->urls['select']  = (string) $urlSelect;
        $this->urls['new']     = (string) $urlNew;
        $this->urls['edit']    = (string) $urlEdit;
        $this->urls['checkin'] = (string) $linkCheckin;

        // Prepare titles
        $this->modalTitles['select']  = $modalTitle;
        $this->modalTitles['new']     = Text::_('COM_MODULES_NEW_MODULE');
        $this->modalTitles['edit']    = Text::_('COM_MODULES_EDIT_MODULE');

        $this->hint = $this->hint ?: Text::_('COM_MODULES_SELECT_A_MODULE');

        return $return;
    }


    /**
     * Method to retrieve the title of selected item.
     *
     * @return string
     *
     * @since   6.1.0
     */
    protected function getExtensionId()
    {
        $data = $this->form->getData();

        $module = $data->get('module', '');

        $eid = 0;

        if ($module) {
            try {
                $db    = $this->getDatabase();
                $query = $db->createQuery()
                    ->select($db->quoteName('extension_id'))
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('element') . ' = :module')
                    ->bind(':module', $module, ParameterType::STRING);
                $db->setQuery($query);

                $eid = $db->loadResult();
            } catch (\Throwable $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return $eid;
    }

    /**
     * Method to retrieve the title of selected item.
     *
     * @return string
     *
     * @since   6.1.0
     */
    protected function getValueTitle()
    {
        $value = (int) $this->value ?: '';
        $title = '';

        if ($value) {
            try {
                $db    = $this->getDatabase();
                $query = $db->createQuery()
                    ->select($db->quoteName('title'))
                    ->from($db->quoteName('#__modules'))
                    ->where($db->quoteName('id') . ' = :id')
                    ->bind(':id', $value, ParameterType::INTEGER);
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
     * @since 6.1.0
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
     * @since   6.1.0
     */
    protected function getRenderer($layoutId = 'default')
    {
        $layout = parent::getRenderer($layoutId);
        $layout->setComponent('com_modules');
        $layout->setClient((int) $this->element['clientid']);

        return $layout;
    }
}
