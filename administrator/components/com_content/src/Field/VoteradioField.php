<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Field;

use Joomla\CMS\Form\Field\RadioField;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Voteradio Field class.
 *
 * @since  3.8.0
 */
class VoteradioField extends RadioField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.8.0
     */
    protected $type = 'Voteradio';

    /**
     * Method to attach a form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @since  4.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        // Requires vote plugin enabled
        if (!PluginHelper::isEnabled('content', 'vote')) {
            return false;
        }

        return parent::setup($element, $value, $group);
    }
}
