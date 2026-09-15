<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;

\defined('_JEXEC') or die;

    // If there are no items, continue
if (empty($displayData)) {
    return;
}

foreach ($displayData as $key => $row) {
    ?>
    <div id="<?php echo $this->escape($key); ?>" class="changelog">
        <div class="changelog__item justify-content-xxl-around">
            <span><?php echo $this->escape($key); ?></span>
        </div>
    </div>
    <?php
    \array_walk(
        $row,
        function ($item, $changeType) {

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
                            <li><?php echo implode('</li><li>', $item); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <?php
        }
    );
}
