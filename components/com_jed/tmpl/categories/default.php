<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_jed.newjed')
    ->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');

$catlist = $this->items;

?>

<div class="jed-home-categories">
    <?php
    $rowc = 1;
    if (count($catlist) == 0) {
        echo "<h1>" . Text::_('COM_JED_CATEGORIES_NONE_LABEL') . "</h1>";
    } else {
        foreach ($catlist as $c) {
            if ($c->id !== 25) {
                if ($rowc == 1) {
                    echo '<div class="row">';
                }
                ?>
                <div class="col jed-home-category">
                    <div class="jed-home-item-view">
                        <div class="jed-home-item-title">
                            <div class="jed-home-category-icon-box">
                                <span class="jed-home-category-icon fa fa-camera"></span>
                            </div>
                            <h4 class="jed-home-category-title">

                                <a href="<?= Route::_(sprintf('index.php?option=com_jed&view=extensions&catid=%d&id=%d', $c->parent_id, $c->id)) ?>">
                                    <?php echo $c->title; ?></a>
                            </h4>
                            <span class="jed-home-category-icon-numitems "><?php echo 0 + $c->numitems; ?></span>
                        </div>
                        <ul class="jed-home-subcategories unstyled">
                            <?php
                            array_multisort(array_column($c->children, "numitems"), SORT_DESC, $c->children);
                            foreach ($c->children as $sc) {
                                if ($sc->id <> $c->id) {
                                    if ($sc->numitems > 0) { ?>
                                        <li class="jed-home-subcategories-child had-items">
                                            <a href="<?= Route::_(sprintf('index.php?option=com_jed&view=extensions&catid=%d&id=%d', $sc->parent_id, $sc->id)) ?>">
                                                <?php echo $sc->title; ?></a>
                                            <span class=" badge-info-cat">  <?php echo 0 + $sc->numitems; ?></span>
                                        </li>
                                    <?php }
                                }
                            }
                            ?>
                        </ul>

                    </div>
                </div>
                            <?php
                            if ($rowc == 3) {
                                echo '</div>';
                                $rowc = 1;
                            } else {
                                $rowc = $rowc + 1;
                            }
            }
        } //end foreach($catlist as $cat)
    }
    ?>

</div>

