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

$input = Factory::getApplication()->input;

// Checking if loaded via index.php or component.php
$tmpl = ($input->getCmd('tmpl') != '') ? '1' : '';
$tmpl = json_encode($tmpl, JSON_NUMERIC_CHECK);

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_menus.admin-item-modal');

?>
<?php echo HTMLHelper::_('bootstrap.startAccordion', 'collapseTypes', array('active' => 'slide1')); ?>
	<?php $i = 0; ?>
	<?php foreach ($this->types as $name => $list) : ?>
		<?php echo HTMLHelper::_('bootstrap.addSlide', 'collapseTypes', $name, 'collapse' . ($i++)); ?>
			<div class="list-group">
				<?php foreach ($list as $title => $item) : ?>
					<?php $menutype = array('id' => $this->recordId, 'title' => $item->type ?? $item->title, 'request' => $item->request); ?>
					<?php $menutype = base64_encode(json_encode($menutype)); ?>
					<a class="choose_type list-group-item list-group-item-action" href="#"
						onclick="Joomla.setMenuType('<?php echo $menutype; ?>', '<?php echo $tmpl; ?>')">
						<div class="pe-2">
							<?php echo $title; ?>
						</div>
						<small class="text-muted">
							<?php echo Text::_($item->description); ?>
						</small>
					</a>
				<?php endforeach; ?>
			</div>
		<?php echo HTMLHelper::_('bootstrap.endSlide'); ?>
	<?php endforeach; ?>
<?php echo HTMLHelper::_('bootstrap.endSlide'); ?>
<?php echo HTMLHelper::_('bootstrap.endAccordion');
