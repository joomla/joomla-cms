<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Service;

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Categories\Categories;

/**
 * Content Component Category Tree
 *
 * @since  4.0.0
 */
class Category extends Categories
{
    /**
     * Class constructor
     *
     * @param   array  $options  Array of options
     *
     * @since   11.1
     */
    public function __construct($options = [])
    {
        $options = array_merge($options, [
            'extension'  => 'com_jed',
            'table'      => '#__jed_extensions',
            'field'      => 'primary_category_id',
            'key'        => 'id',
            'statefield' => 'published',
        ]);

        parent::__construct($options);
    }
}
