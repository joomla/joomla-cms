<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\View\Categories;

use Joomla\CMS\MVC\View\CategoriesView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Contact categories view.
 *
 * @since  1.6
 */
class HtmlView extends CategoriesView
{
    /**
     * Language key for default page heading
     *
     * @var    string
     * @since  3.2
     */
    protected $pageHeading = 'COM_CONTACT_DEFAULT_PAGE_TITLE';

    /**
     * @var    string  The name of the extension for the category
     * @since  3.2
     */
    protected $extension = 'com_contact';
}
