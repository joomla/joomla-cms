<?php

/**
 * @package    Joomla.Installation
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Form\Rule;

use Joomla\CMS\Form\FormRule;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Rule class for the prefix DB.
 *
 * @since  1.7
 */
class PrefixRule extends FormRule
{
    /**
     * The regular expression to use in testing a form field value.
     *
     * @var    string
     * @since  1.7
     */
    protected $regex = '^[a-z][a-z0-9]*_$';

    /**
     * The regular expression modifiers to use when testing a form field value.
     *
     * @var    string
     * @since  1.7
     */
    protected $modifiers = 'i';
}
