<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\Field;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Total Impressions field.
 *
 * @since  1.6
 */
class ImptotalField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    protected $type = 'ImpTotal';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   1.6
     */
    protected function getInput()
    {
        $class    = ' class="form-control validate-numeric text_area"';
        $onchange = ' onchange="document.getElementById(\'' . $this->id . '_unlimited\').checked=document.getElementById(\'' . $this->id
            . '\').value==\'\';"';
        $onclick  = ' onclick="if (document.getElementById(\'' . $this->id . '_unlimited\').checked) document.getElementById(\'' . $this->id
            . '\').value=\'\';"';
        $value    = empty($this->value) ? '' : $this->value;
        $checked  = empty($this->value) ? ' checked="checked"' : '';

        return '<input type="text" name="' . $this->name . '" id="' . $this->id . '" size="9" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8')
            . '" ' . $class . $onchange . '>'
            . '<fieldset class="form-check impunlimited"><input class="form-check-input" id="' . $this->id . '_unlimited" type="checkbox"' . $checked . $onclick . '>'
            . '<label for="' . $this->id . '_unlimited" id="jform-imp" class="form-check-label">' . Text::_('COM_BANNERS_UNLIMITED') . '</label></fieldset>';
    }
}
