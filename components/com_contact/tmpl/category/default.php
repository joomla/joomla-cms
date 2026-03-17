<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\Component\Contact\Site\View\Category\HtmlView $this */
?>

<div class="com-contact-category">
    <?php
        $this->subtemplatename = 'items';
        echo LayoutHelper::render('joomla.content.category_default', $this);
    ?>
</div>
