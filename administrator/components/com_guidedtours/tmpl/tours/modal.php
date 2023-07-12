<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_guidedtours
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\StringHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Guidedtours\Administrator\View\Tours\HtmlView;

$app = Factory::getApplication();

if ($app->isClient('site')) {
    Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
    ->useScript('multiselect');
    //->useScript('com_guidedtours.admin-tours-modal');

$function  = $app->getInput()->getCmd('function', 'jSelectTour');
$editor    = $app->getInput()->getCmd('editor', '');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$onclick   = $this->escape($function);
$multilang = Multilanguage::isEnabled();

if (!empty($editor)) {
    // This view is used also in com_menus. Load the xtd script only if the editor is set!
    $this->document->addScriptOptions('xtd-guidedtour', ['editor' => $editor]);
    $onclick = "jSelectTour";
}
?>
<div class="container-popup">

    <form action="<?php echo Route::_('index.php?option=com_guidedtours&view=toures&layout=modal&tmpl=component&function=' . $function . '&' . Session::getFormToken() . '=1&editor=' . $editor); ?>" method="post" name="adminForm" id="adminForm">

	    <div id="j-main-container" class="j-main-container">
            <?php
            // Search tools bar
            echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
            ?>

		    <!-- If no tours -->
            <?php if (empty($this->items)) :
                ?>
			    <!-- No tours -->
			    <div class="alert alert-info">
				    <span class="icon-info-circle" aria-hidden="true"></span>
				    <span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                    <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			    </div>
            <?php endif; ?>

		    <!-- If there are tours, we start with the table -->
            <?php if (!empty($this->items)) :
                ?>
			    <!-- Tours table starts here -->
			    <table class="table" id="toursList">

				    <caption class="visually-hidden">
                        <?php echo Text::_('COM_GUIDEDTOURS_GUIDEDTOURS_TABLE_CAPTION'); ?>,
					    <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
					    <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
				    </caption>

				    <!-- Tours table header -->
				    <thead>
				    <tr>
					    <th scope="col">
                            <?php echo Text::_('COM_GUIDEDTOURS_TITLE'); ?>
					    </th>
					    <th scope="col" class="d-none d-md-table-cell">
                            <?php echo Text::_('COM_GUIDEDTOURS_DESCRIPTION'); ?>
					    </th>
					    <th scope="col" class="text-center w-10 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
					    </th>

					    <!-- Add language types if multi-language enabled -->
                        <?php if (Multilanguage::isEnabled()) : ?>
						    <th scope="col" class="text-center w-10 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
						    </th>
                        <?php endif; ?>

					    <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                            <?php echo HTMLHelper::_(
                                'searchtools.sort',
                                'JGRID_HEADING_ID',
                                'a.id',
                                $listDirn,
                                $listOrder
                            ); ?>
					    </th>
				    </tr>
				    </thead>

				    <!-- Table body begins -->
				    <tbody <?php if ($saveOrder) : ?>
					    class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true" <?php
                    endif; ?>>
                    <?php foreach ($this->items as $i => $item) :
                        $canEditOwn = $canEditOwnTour && $item->created_by == $userId;
                        $canCheckin = $hasCheckinPermission || $item->checked_out == $userId || is_null($item->checked_out);
                        $canChange  = $canEditStateTour && $canCheckin;
                        ?>

					    <!-- Row begins -->
					    <tr class="row<?php echo $i % 2; ?>" data-draggable-group="none">

						    <th scope="row" class="has-context">
                                <?php $attribs = 'data-function="' . $this->escape($onclick) . '"'
                                                 . ' data-id="' . $item->id . '"'
                                                 . ' data-title="' . $this->escape($item->title) . '"'
                                                 . ' data-alias="' . $this->escape($item->alias) . '"';
                                ?>
							    <a class="select-link" href="javascript:void(0)" <?php echo $attribs; ?>>
                                    <?php echo $this->escape($item->title); ?>
							    </a>
						    </th>

						    <td class="d-none d-md-table-cell">
                                <?php echo StringHelper::truncate($item->description, 200, true, false); ?>
						    </td>

						    <!-- Adds access labels -->
						    <td class="small text-center d-none d-md-table-cell">
                                <?php echo $this->escape($item->access_level); ?>
						    </td>

                            <?php if (Multilanguage::isEnabled()) : ?>
							    <td class="text-center small d-none d-md-table-cell">
                                    <?php echo LayoutHelper::render('joomla.content.language', $item); ?>
							    </td>
                            <?php endif; ?>

						    <!-- Tour ID -->
						    <td class="d-none d-md-table-cell text-center">
                                <?php echo (int) $item->id; ?>
						    </td>
					    </tr>
                    <?php endforeach; ?>
				    </tbody>
			    </table>

                <?php
                // Load the pagination.
                echo $this->pagination->getListFooter();
                ?>
            <?php endif; ?>

		    <input type="hidden" name="task" value="">
		    <input type="hidden" name="boxchecked" value="0">
            <?php echo HTMLHelper::_('form.token'); ?>
	    </div>

    </form>
</div>
