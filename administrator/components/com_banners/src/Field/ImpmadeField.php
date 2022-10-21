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
 * Impressions field.
 *
 * @since  1.6
 */
class ImpmadeField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    protected $type = 'ImpMade';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   1.6
     */
    protected function getInput()
    {
        $onclick = ' onclick="document.getElementById(\'' . $this->id . '\').value=\'0\';"';

        return '<div class="input-group"><input class="form-control" type="text" name="' . $this->name . '" id="' . $this->id . '" value="'
            . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" readonly="readonly">'
            . '<button type="button" class="btn btn-secondary" ' . $onclick . '>'
            . '<span class="icon-sync" aria-hidden="true"></span> ' . Text::_('JRESET') . '</button></div>';
    }
}
