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
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
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
class MenutypeField extends ListField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'menutype';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   1.6
     */
    protected function getInput()
    {
        $html     = [];
        $recordId = (int) $this->form->getValue('id');
        $size     = (string) ($v = $this->element['size']) ? ' size="' . $v . '"' : '';
        $class    = (string) ($v = $this->element['class']) ? ' class="form-control ' . $v . '"' : ' class="form-control"';
        $required = (string) $this->element['required'] ? ' required="required"' : '';
        $clientId = (int) $this->element['clientid'] ?: 0;

        // Get a reverse lookup of the base link URL to Title
        switch ($this->value) {
            case 'url':
                $value = Text::_('COM_MENUS_TYPE_EXTERNAL_URL');
                break;

            case 'alias':
                $value = Text::_('COM_MENUS_TYPE_ALIAS');
                break;

            case 'separator':
                $value = Text::_('COM_MENUS_TYPE_SEPARATOR');
                break;

            case 'heading':
                $value = Text::_('COM_MENUS_TYPE_HEADING');
                break;

            case 'container':
                $value = Text::_('COM_MENUS_TYPE_CONTAINER');
                break;

            default:
                $link = $this->form->getValue('link');
                $value = '';

                if ($link !== null) {
                    $model = Factory::getApplication()->bootComponent('com_menus')
                        ->getMVCFactory()->createModel('Menutypes', 'Administrator', ['ignore_request' => true]);
                    $model->setState('client_id', $clientId);

                    $rlu   = $model->getReverseLookup();

                    // Clean the link back to the option, view and layout
                    $value = Text::_(ArrayHelper::getValue($rlu, MenusHelper::getLinkKey($link)));
                }
                break;
        }

        $link = Route::_('index.php?option=com_menus&view=menutypes&tmpl=component&client_id=' . $clientId . '&recordId=' . $recordId);
        $html[] = '<span class="input-group"><input type="text" ' . $required . ' readonly="readonly" id="' . $this->id
            . '" value="' . $value . '"' . $size . $class . '>';
        $html[] = '<button type="button" data-bs-target="#menuTypeModal" class="btn btn-primary" data-bs-toggle="modal">'
            . '<span class="icon-list icon-white" aria-hidden="true"></span> '
            . Text::_('JSELECT') . '</button></span>';
        $html[] = HTMLHelper::_(
            'bootstrap.renderModal',
            'menuTypeModal',
            [
                'url'        => $link,
                'title'      => Text::_('COM_MENUS_ITEM_FIELD_TYPE_LABEL'),
                'width'      => '800px',
                'height'     => '300px',
                'modalWidth' => 80,
                'bodyHeight' => 70,
                'footer'     => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
                        . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
            ]
        );

        // This hidden field has an ID so it can be used for showon attributes
        $html[] = '<input type="hidden" name="' . $this->name . '" value="'
            . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" id="' . $this->id . '_val">';

        return implode("\n", $html);
    }
}
