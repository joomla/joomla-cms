<?php

/**
 * @package     Jed\Component\Jed\Administrator\Traits
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */



// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Router\Route;

/**
 * @var array $displayData
 */
$i = 0;
?>
<div class="d-flex flex-row gap-1 align-items-center">
    <span aria-hidden="true" class="icon-tag"></span>
    <?php foreach ($displayData['categories'] as $cat) : ?>
        <?php $i++ ?>
        <a href="<?= Route::_(sprintf('index.php?option=com_jed&view=extensions&id=%d&catid=%d', $cat->id, $cat->parent_id)) ?>">
            <?= htmlentities($cat->title) ?>
        </a>
        <?php if ($i != count($displayData['categories'])) : ?>
            <span class="text-muted">&bull;</span>
        <?php endif ?>
    <?php endforeach; ?>
</div>
