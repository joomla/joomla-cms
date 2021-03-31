<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

extract($displayData);

$options = [
		'clientId'  => $clientId,
		'canChange' => $canChange,
		'canDelete' => $canDelete,
		'canEdit'   => $canEdit,
		'canCreate' => $canCreate,
		'item'      => $item,
		'i'         => $i
];
?>
<div class="template-style card text-dark bg-light">
	<?php
	/**
	 * @see: administrator/components/com_templates/layouts/card-header.php
	 */
	echo LayoutHelper::render('card-header', $options);
	?>
	<div class="card-media">
		<div class="template-thumbnail">
			<img src="<?php echo str_replace('thumbnail', 'preview', $item->thumbnail); ?>?v=<?php echo $item->xmldata->get('version')?>" width="600" height="400" alt="<?php echo $item->title; ?>">
		</div>
	</div>
	<?php
		/**
		 * @see: administrator/components/com_templates/layouts/card-footer.php
		 */
		echo LayoutHelper::render('card-footer', $options);
	?>
</div>
