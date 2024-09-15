<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Contact\Site\Helper\RouteHelper;

/** @var \Joomla\Component\Contact\Administrator\View\Contacts\HtmlView $this */

$app = Factory::getApplication();

if ($app->isClient('site')) {
    Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('modal-content-select')
    ->useScript('com_contact.admin-contacts-modal');

// @todo: Use of Function and Editor is deprecated and should be removed in 6.0. It stays only for backward compatibility.
$function  = $app->getInput()->getCmd('function', 'jSelectContact');
$editor    = $app->getInput()->getCmd('editor', '');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$onclick   = $this->escape($function);
$multilang = Multilanguage::isEnabled();

if (!empty($editor)) {
    // This view is used also in com_menus. Load the xtd script only if the editor is set!
    $this->getDocument()->addScriptOptions('xtd-contacts', ['editor' => $editor]);
    $onclick = "jSelectContact";
}
?>
<div class="container-popup">

    <form action="<?php echo Route::_('index.php?option=com_contact&view=contacts&layout=modal&tmpl=component&editor=' . $editor . '&function=' . $function . '&' . Session::getFormToken() . '=1'); ?>" method="post" name="adminForm" id="adminForm">

        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

        <?php if (empty($this->items)) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table table-sm">
                <caption class="visually-hidden">
                    <?php echo Text::_('COM_CONTACT_TABLE_CAPTION'); ?>,
                        <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                        <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                </caption>
                <thead>
                    <tr>
                        <th scope="col" class="w-1 text-center">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="title">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.name', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-10">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_CONTACT_FIELD_LINKED_USER_LABEL', 'ul.name', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-15 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
                        </th>
                        <?php if ($multilang) : ?>
                            <th scope="col" class="w-10 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language_title', $listDirn, $listOrder); ?>
                            </th>
                        <?php endif; ?>
                        <th scope="col" class="w-1">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $iconStates = [
                    -2 => 'icon-trash',
                    0  => 'icon-unpublish',
                    1  => 'icon-publish',
                    2  => 'icon-archive',
                ];
                ?>
                <?php foreach ($this->items as $i => $item) : ?>
                    <?php
                    $lang = '';
                    if ($item->language && $multilang) {
                        $tag = \strlen($item->language);
                        if ($tag == 5) {
                            $lang = substr($item->language, 0, 2);
                        } elseif ($tag == 6) {
                            $lang = substr($item->language, 0, 3);
                        }
                    }

                    $link     = RouteHelper::getContactRoute($item->id, $item->catid, $item->language);
                    $itemHtml = '<a href="' . $this->escape($link) . '"' . ($lang ? ' hreflang="' . $lang . '"' : '') . '>' . $item->name . '</a>';
                    ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="text-center">
                            <span class="tbody-icon">
                                <span class="<?php echo $iconStates[$this->escape($item->published)]; ?>" aria-hidden="true"></span>
                            </span>
                        </td>
                        <th scope="row">
                            <?php $attribs = 'data-content-select data-content-type="com_contact.contact"'
                                . 'data-function="' . $this->escape($onclick) . '"'
                                . ' data-id="' . $item->id . '"'
                                . ' data-title="' . $this->escape($item->name) . '"'
                                . ' data-cat-id="' . $this->escape($item->catid) . '"'
                                . ' data-uri="' . $this->escape($link) . '"'
                                . ' data-language="' . $this->escape($lang) . '"'
                                . ' data-html="' . $this->escape($itemHtml) . '"';
                            ?>
                            <a class="select-link" href="javascript:void(0)" <?php echo $attribs; ?>>
                                <?php echo $this->escape($item->name); ?>
                            </a>
                            <div class="small">
                                <?php echo Text::_('JCATEGORY') . ': ' . $this->escape($item->category_title); ?>
                            </div>
                        </th>
                        <td>
                            <?php if (!empty($item->linked_user)) : ?>
                                <?php echo $item->linked_user; ?>
                            <?php endif; ?>
                        </td>
                        <td class="small d-none d-md-table-cell">
                            <?php echo $this->escape($item->access_level); ?>
                        </td>
                        <?php if ($multilang) : ?>
                            <td class="small d-none d-md-table-cell">
                                <?php echo LayoutHelper::render('joomla.content.language', $item); ?>
                            </td>
                        <?php endif; ?>
                        <td>
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php // load the pagination. ?>
            <?php echo $this->pagination->getListFooter(); ?>

        <?php endif; ?>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="forcedLanguage" value="<?php echo $app->getInput()->get('forcedLanguage', '', 'CMD'); ?>">
        <?php echo HTMLHelper::_('form.token'); ?>

    </form>
</div>
