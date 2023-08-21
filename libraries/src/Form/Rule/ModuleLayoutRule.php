<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

use Joomla\CMS\Form\FormRule;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  3.9.26
 */
class ModuleLayoutRule extends FormRule
{
    /**
     * The regular expression to use in testing a module layout field value.
     *
     * A valid module layout field value consists of
     * - optionally a template name with only characters, numbers, hyphens and
     *   underscores, which can also be just "_" for layouts provided by the
     *   module, followed by a colon.
     * - the base name of the layout file, not starting with a dot and with
     *   only characters, numbers, dots and hyphens but no underscores (see
     *   method "getInput" of the "ModuleLayout" field).
     *
     * @var    string
     * @since  3.9.26
     */
    protected $regex = '^([A-Za-z0-9_-]+:)?[A-Za-z0-9-][A-Za-z0-9\.-]*$';
}
