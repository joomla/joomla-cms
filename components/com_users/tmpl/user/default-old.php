<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
?>

ID: <?php echo $this->item->id; ?><br>
Name: <?php echo $this->item->name; ?><br>
<br><br>

<?php if ($this->item->articles): ?>
	<?php foreach ($this->item->articles as $article): ?>
		<?php echo $article->id; ?>
		<?php echo $article->title; ?>
		<?php echo $article->created; ?>
		<?php echo $article->hits; ?>
        <br>
	<?php endforeach; ?>
<?php endif; ?>
