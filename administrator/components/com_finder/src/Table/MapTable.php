<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Table;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Nested;
use Joomla\Database\DatabaseDriver;

/**
 * Map table class for the Finder package.
 *
 * @since  2.5
 */
class MapTable extends Nested
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database Driver connector object.
     *
     * @since   2.5
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__finder_taxonomy', 'id', $db);

        $this->setColumnAlias('published', 'state');
        $this->access = (int) Factory::getApplication()->get('access');
    }

    /**
     * Override check function
     *
     * @return  boolean
     *
     * @see     Table::check()
     * @since   4.0.0
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Check for a title.
        if (trim($this->title) == '') {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_CATEGORY'));

            return false;
        }

        $this->alias = ApplicationHelper::stringURLSafe($this->title, $this->language);

        if (trim($this->alias) == '') {
            $this->alias = md5(serialize($this->getProperties()));
        }

        return true;
    }
}
