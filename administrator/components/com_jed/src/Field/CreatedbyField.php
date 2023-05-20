<?php

/**
 * @package       JED
 *
 * @copyright     (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Jed\Component\Jed\Administrator\Helper\JedHelper;
use Joomla\CMS\Form\FormField;

/**
 * Created By Field
 *
 * @since  4.0.0
 */
class CreatedbyField extends FormField
{
    /**
     * The form field type.
     *
     * @var        string
     * @since  4.0.0
     */
    protected $type = 'createdby';

    /**
     * Method to get the field input markup.
     *
     * @return    string    The field input markup.
     *
     * @since  4.0.0
     */
    protected function getInput(): string
    {
        // Initialize variables.
        $html = [];

        // Load user
        $user_id = $this->value;

        if ($user_id) {
            $user = JedHelper::getUserById($user_id);
        } else {
            $user   = JedHelper::getUser();
            $html[] = '<input type="hidden" name="' . $this->name . '" value="' . $user->id . '" />';
        }

        if (!$this->hidden) {
            $html[] = "<div>" . $user->name . " (" . $user->username . ")</div>";
        }

        return implode($html);
    }
}
