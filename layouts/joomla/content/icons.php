<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

$canEdit   = $displayData['params']->get('access-edit');
$articleId = $displayData['item']->id;
?>

<?php if ($canEdit) : ?>
    <div class="icons">
        <div class="float-end">
            <div>
                <?php echo HTMLHelper::_('icon.edit', $displayData['item'], $displayData['params']); ?>
            </div>
        </div>
    </div>
<?php endif; ?>
