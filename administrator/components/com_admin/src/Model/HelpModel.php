<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Help\Help;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Admin Component Help Model
 *
 * @since  1.6
 */
class HelpModel extends BaseDatabaseModel
{
    /**
     * The page to be viewed
     *
     * @var    string
     * @since  1.6
     */
    protected $page = null;

    /**
     * Method to get the page
     *
     * @return  string  The page
     *
     * @since   1.6
     */
    public function &getPage()
    {
        if (\is_null($this->page)) {
            $this->page = Help::createUrl(Factory::getApplication()->getInput()->get('page', 'Start_Here'));
        }

        return $this->page;
    }
}
