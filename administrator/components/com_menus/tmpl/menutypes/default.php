<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$input = Factory::getApplication()->getInput();

// Checking if loaded via index.php or component.php
$tmpl = $input->getCmd('tmpl') ? '1' : '';

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_menus.admin-item-modal')->useScript('modal-content-select');

?>
<?php echo HTMLHelper::_('bootstrap.startAccordion', 'collapseTypes', ['active' => 'slide1']); ?>
    <?php $i = 0; ?>
    <?php foreach ($this->types as $name => $list) : ?>
        <?php echo HTMLHelper::_('bootstrap.addSlide', 'collapseTypes', $name, 'collapse' . ($i++)); ?>
            <div class="list-group">
                <?php foreach ($list as $title => $item) :
                    $menutype = ['id' => $this->recordId, 'title' => $item->type ?? $item->title, 'request' => $item->request];
                    $encoded  = base64_encode(json_encode($menutype));

                    $attrs = 'data-content-select data-content-type="com_menus.menutype"'
                        . ' data-item-id="' . (int) $this->recordId . '"'
                        . ' data-type="' . $this->escape($item->type ?? $item->title) . '"'
                        . ' data-request="' . ($item->request ? $this->escape(json_encode($item->request)) : '') . '"'
                        . ' data-encoded="' . $this->escape($encoded) . '"'
                        . ' data-tmpl-view="' . $tmpl . '"';
                ?>
                    <button type="button" class="choose_type list-group-item list-group-item-action" <?php echo $attrs; ?>>
                        <div class="pe-2">
                            <?php echo $title; ?>
                        </div>
                        <small class="text-muted">
                            <?php echo Text::_($item->description); ?>
                        </small>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endSlide'); ?>
    <?php endforeach; ?>
<?php echo HTMLHelper::_('bootstrap.endSlide'); ?>
<?php echo HTMLHelper::_('bootstrap.endAccordion');
