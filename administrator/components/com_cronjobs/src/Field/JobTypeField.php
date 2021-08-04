<?php
/**
 * Declares the JobTypeField for listing all available job types.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 */

namespace Joomla\Component\Cronjobs\Administrator\Field;

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;

/**
 * A list field with all available job types
 *
 * @since __DEPLOY_VERSION__
 */
class JobTypeField extends ListField
{

}
