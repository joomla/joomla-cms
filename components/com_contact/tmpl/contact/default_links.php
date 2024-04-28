<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var \Joomla\Component\Contact\Site\View\Contact\HtmlView $this */
?>
<div class="com-contact__links contact-links">
    <ul class="list-unstyled">
        <?php
        // Letters 'a' to 'e'
        foreach (range('a', 'e') as $char) :
            $link = $this->item->params->get('link' . $char);
            $label = $this->item->params->get('link' . $char . '_name');

            if (!$link) :
                continue;
            endif;

            // Add 'http://' if not present
            $link = (0 === strpos($link, 'http')) ? $link : 'http://' . $link;

            // If no label is present, take the link
            $label = $label ?: $link;
            ?>
            <li>
                <a href="<?php echo $link; ?>" rel="noopener noreferrer">
                    <?php echo $label; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
