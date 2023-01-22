<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Site\Service;

use Joomla\CMS\Categories\Categories;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Newsfeed Component Category Tree
 *
 * @since  1.6
 */
class Category extends Categories
{
    /**
     * Constructor
     *
     * @param   array  $options  options
     */
    public function __construct($options = [])
    {
        $options['table'] = '#__newsfeeds';
        $options['extension'] = 'com_newsfeeds';
        $options['statefield'] = 'published';
        parent::__construct($options);
    }
}
