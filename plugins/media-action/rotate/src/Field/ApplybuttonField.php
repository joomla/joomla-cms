<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Media-Action.rotate
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\MediaAction\Rotate\Field;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Renders the button which applies the selected rotation angle.
 *
 * @since  __DEPLOY_VERSION__
 */
class ApplybuttonField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $type = 'Applybutton';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getInput()
    {
        Text::script('PLG_MEDIA-ACTION_ROTATE_APPLIED');

        return '<button type="button" id="' . $this->id . '" class="btn btn-primary">'
            . Text::_((string) $this->element['text'])
            . '</button>'
            . '<span id="' . $this->id . '-status" class="visually-hidden" role="status"></span>';
    }
}
