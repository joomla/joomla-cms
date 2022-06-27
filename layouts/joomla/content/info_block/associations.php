<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>

<?php if (!empty($displayData['item']->associations)) : ?>
    <?php $associations = $displayData['item']->associations; ?>

<dd class="association">
    <span class="icon-globe icon-fw" aria-hidden="true"></span>
    <?php echo Text::_('JASSOCIATIONS'); ?>
    <?php foreach ($associations as $association) : ?>
        <?php if ($displayData['item']->params->get('flags', 1) && $association['language']->image) : ?>
            <?php $flag = HTMLHelper::_('image', 'mod_languages/' . $association['language']->image . '.gif', $association['language']->title_native, array('title' => $association['language']->title_native), true); ?>
            <a href="<?php echo Route::_($association['item']); ?>"><?php echo $flag; ?></a>
        <?php else : ?>
            <?php $class = 'btn btn-secondary btn-sm btn-' . strtolower($association['language']->lang_code); ?>
            <a class="<?php echo $class; ?>" title="<?php echo $association['language']->title_native; ?>" href="<?php echo Route::_($association['item']); ?>"><?php echo $association['language']->lang_code; ?>
                <span class="visually-hidden"><?php echo $association['language']->title_native; ?></span>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</dd>
<?php endif; ?>
