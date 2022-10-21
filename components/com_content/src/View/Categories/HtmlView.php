<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\View\Categories;

use Joomla\CMS\MVC\View\CategoriesView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content categories view.
 *
 * @since  1.5
 */
class HtmlView extends CategoriesView
{
    /**
     * Language key for default page heading
     *
     * @var    string
     * @since  3.2
     */
    protected $pageHeading = 'JGLOBAL_ARTICLES';

    /**
     * @var    string  The name of the extension for the category
     * @since  3.2
     */
    protected $extension = 'com_content';
}
