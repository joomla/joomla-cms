<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData, EXTR_OVERWRITE);

/**
 * Layout variables
 * -----------------
 * @var   bool    $is_child
 * @var   string  $id
 * @var   string  $doTask
 * @var   string  $class
 * @var   string  $text
 * @var   string  $btnClass
 * @var   string  $tagName
 * @var   string  $htmlAttributes
 */
?>

<?php if ($is_child) : ?>
    <?php if (!empty($text)) : ?>
        <h6 class="dropdown-header <?php echo $btnClass ?? ''; ?>">
            <?php echo $text; ?>
        </h6>
    <?php else : ?>
        <div class="dropdown-divider <?php echo $btnClass ?? ''; ?>"></div>
    <?php endif; ?>
<?php endif; ?>
