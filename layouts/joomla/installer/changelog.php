<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/*
 * The items of a changelog come from a remote server and may contain HTML (see the documentation of the
 * changelog file). Only a small set of formatting tags and the href and title attributes is kept, everything
 * else (e.g. data-* attributes, event handlers, buttons, images, forms) is removed.
 */
$changelogFilter = InputFilter::getInstance(
    ['a', 'b', 'strong', 'i', 'em', 'u', 'code', 'pre', 'br', 'p', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'small', 'sub', 'sup', 'blockquote'],
    ['href', 'title'],
    InputFilter::ONLY_ALLOW_DEFINED_TAGS,
    InputFilter::ONLY_ALLOW_DEFINED_ATTRIBUTES
);

array_walk(
    $displayData,
    function ($items, $changeType) use ($changelogFilter) {
        // If there are no items, continue
        if (empty($items)) {
            return;
        }

        switch ($changeType) {
            case 'security':
                $class = 'bg-danger';
                break;
            case 'fix':
                $class = 'bg-dark';
                break;
            case 'language':
                $class = 'bg-primary';
                break;
            case 'addition':
                $class = 'bg-success';
                break;
            case 'change':
                $class = 'bg-warning';
                break;
            case 'remove':
                $class = 'bg-secondary';
                break;
            default:
            case 'note':
                $class = 'bg-info';
                break;
        }

        ?>
        <div class="changelog">
            <div class="changelog__item">
                <div class="changelog__tag">
                    <span class="badge <?php echo $class; ?>"><?php echo Text::_('COM_INSTALLER_CHANGELOG_' . $changeType); ?></span>
                </div>
                <div class="changelog__list">
                    <ul>
                        <li><?php echo implode('</li><li>', array_map(static fn ($item) => $changelogFilter->clean($item, 'html'), $items)); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
);
