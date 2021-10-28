<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
// ksort($this->files, SORT_STRING);

return;
?>

<ul class="directory-tree treeselect">
	<?php foreach($this->files[0] as $key => $value) : ?>
			<?php $cleanKey = str_replace(JPATH_ROOT . '/media/templates/' . ($this->template->client_id === 0 ? 'site' : 'administrator') . '/' . $this->template->element . '/', '', $key); ?>
			<li class="folder-select">
				<a class="folder-url" data-id="<?php echo base64_encode($cleanKey); ?>" href="">
					<span class="icon-folder icon-fw" aria-hidden="true"></span>
					<?php $explodeArray = explode('/', $key); echo $this->escape(end($explodeArray)); ?>
				</a>
				<?php echo $this->folderTree($value); ?>
			</li>
	<?php endforeach; ?>
</ul>
