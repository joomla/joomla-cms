<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mails
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mails\Administrator\Table;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Mail Table class.
 *
 * @since  4.0.0
 */
class TemplateTable extends Table
{
    /**
     * An array of key names to be json encoded in the bind function
     *
     * @var    array
     * @since  4.0.0
     */
    protected $_jsonEncode = ['attachments', 'params'];

    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since   4.0.0
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__mail_templates', array('template_id', 'language'), $db);
    }
}
