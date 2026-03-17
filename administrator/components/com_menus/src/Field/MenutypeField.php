<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ModalSelectField;
use Joomla\CMS\Language\Text;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Menu Type field.
 *
 * @since  1.6
 */
class MenutypeField extends ModalSelectField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'menutype';

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
     * @since   5.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if (!$result) {
            return $result;
        }

        $recordId = (int) $this->form->getValue('id');
        $clientId = (int) $this->element['clientid'] ?: 0;

        $url = 'index.php?option=com_menus&view=menutypes&tmpl=component&client_id=' . $clientId . '&recordId=' . $recordId;

        $this->urls['select']        = $url;
        $this->canDo['clear']        = false;
        $this->modalTitles['select'] = Text::_('COM_MENUS_ITEM_FIELD_TYPE_LABEL');
        $this->buttonIcons['select'] = 'icon-list';

        return $result;
    }

    /**
     * Method to retrieve the title of selected item.
     *
     * @return string
     *
     * @since   5.0.0
     */
    protected function getValueTitle()
    {
        $title    = '';
        $clientId = (int) $this->element['clientid'] ?: 0;

        // Get a reverse lookup of the base link URL to Title
        switch ($this->value) {
            case 'url':
                $title = Text::_('COM_MENUS_TYPE_EXTERNAL_URL');
                break;

            case 'alias':
                $title = Text::_('COM_MENUS_TYPE_ALIAS');
                break;

            case 'separator':
                $title = Text::_('COM_MENUS_TYPE_SEPARATOR');
                break;

            case 'heading':
                $title = Text::_('COM_MENUS_TYPE_HEADING');
                break;

            case 'container':
                $title = Text::_('COM_MENUS_TYPE_CONTAINER');
                break;

            default:
                $link = $this->form->getValue('link');

                if ($link !== null) {
                    /** @var \Joomla\Component\Menus\Administrator\Model\MenutypesModel $model */
                    $model = Factory::getApplication()->bootComponent('com_menus')
                        ->getMVCFactory()->createModel('Menutypes', 'Administrator', ['ignore_request' => true]);
                    $model->setState('client_id', $clientId);

                    $rlu   = $model->getReverseLookup();

                    // Clean the link back to the option, view and layout
                    $title = Text::_(ArrayHelper::getValue($rlu, MenusHelper::getLinkKey($link)));
                }
                break;
        }

        return $title;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   5.0.0
     */
    protected function getInput()
    {
        // Get the layout data
        $data = $this->getLayoutData();

        // Load the content title here to avoid a double DB Query
        $data['valueTitle'] = $this->getValueTitle();

        // On new item creation the model forces the value to be 'component',
        // However this is need to be empty in the input for correct validation and rendering.
        if ($data['value'] === 'component' && !$data['valueTitle'] && !$this->form->getValue('link')) {
            $data['value'] = '';
        }

        return $this->getRenderer($this->layout)->render($data);
    }
}
