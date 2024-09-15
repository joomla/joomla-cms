<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_banners
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Site\Service;

use Joomla\CMS\Categories\Categories;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Banners Component Category Tree
 *
 * @since  1.6
 */
class Category extends Categories
{
    /**
     * Constructor
     *
     * @param   array  $options  Array of options
     *
     * @since   1.6
     */
    public function __construct($options = [])
    {
        $options['table']     = '#__banners';
        $options['extension'] = 'com_banners';

        parent::__construct($options);
    }
}
