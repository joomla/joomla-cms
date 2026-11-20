<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Finder\Administrator\View\Item\HtmlView $this */
?>
<div role="main">
    <h1 class="mb-3"><?php echo $this->item->title; ?></h1>
    <div class="card mb-3">
        <div class="card-header"><h2><?php echo Text::_('COM_FINDER_ITEM_FIELDSET_ITEM_TITLE'); ?></h2></div>
        <div class="card-body">
            <dl class="row">
                <?php foreach ($this->item as $key => $value) : ?>
                <dt class="col-sm-3"><?php echo $key; ?></dt>
                <dd class="col-sm-9<?php echo $key == 'object' ? ' text-break' : '';?>"><?php echo $value; ?></dd>
                <?php endforeach; ?>
            </dl>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h2><?php echo Text::_('COM_FINDER_ITEM_FIELDSET_TERMS_TITLE'); ?></h2></div>
        <div class="card-body">
            <table class="table">
                <caption class="visually-hidden">
                    <?php echo Text::_('COM_FINDER_ITEM_TERMS_TABLE_CAPTION'); ?>,
                </caption>
                <thead>
                <tr>
                    <th scope="col">id</th>
                    <th scope="col">term</th>
                    <th scope="col">stem</th>
                    <th scope="col">common</th>
                    <th scope="col">phrase</th>
                    <th scope="col">weight</th>
                    <th scope="col">links</th>
                    <th scope="col">language</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->terms as $term) : ?>
                    <tr>
                        <th scope="row"><?php echo $term->term_id; ?></th>
                        <td><?php echo $term->term; ?></td>
                        <td><?php echo $term->stem; ?></td>
                        <td><?php echo $term->common; ?></td>
                        <td><?php echo $term->phrase; ?></td>
                        <td><?php echo $term->weight; ?></td>
                        <td><?php echo $term->links; ?></td>
                        <td><?php echo $term->language; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h2><?php echo Text::_('COM_FINDER_ITEM_FIELDSET_TAXONOMIES_TITLE'); ?></h2></div>
        <div class="card-body">
            <table class="table">
                <caption class="visually-hidden">
                    <?php echo Text::_('COM_FINDER_ITEM_TAXONOMIES_TABLE_CAPTION'); ?>,
                </caption>
                <thead>
                    <tr>
                        <th scope="col">id</th>
                        <th scope="col">title</th>
                        <th scope="col">alias</th>
                        <th scope="col">lft</th>
                        <th scope="col">path</th>
                        <th scope="col">state</th>
                        <th scope="col">access</th>
                        <th scope="col">language</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->taxonomies as $taxonomy) : ?>
                        <tr>
                            <th scope="row"><?php echo $taxonomy->id; ?></th>
                            <td><?php echo $taxonomy->title; ?></td>
                            <td><?php echo $taxonomy->alias; ?></td>
                            <td><?php echo $taxonomy->lft; ?></td>
                            <td><?php echo $taxonomy->path; ?></td>
                            <td><?php echo $taxonomy->state; ?></td>
                            <td><?php echo $taxonomy->access; ?></td>
                            <td><?php echo $taxonomy->language; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
