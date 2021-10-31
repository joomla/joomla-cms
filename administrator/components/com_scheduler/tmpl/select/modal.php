<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/** The SelectView modal layout template. */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\Component\Scheduler\Administrator\View\Select\HtmlView;

/** @var  HtmlView  $this */

// Is this needed?
$this->modalLink = '&tmpl=component&view=select&layout=modal';

// Wrap the default layout in a div.container-popup
?>
<div class="container-popup">
	<?php $this->setLayout('default'); ?>

	<?php try
	{
		echo $this->loadTemplate();
	}
	catch (Exception $e)
	{
		die('Exception while loading template..');
	}
	?>
</div>
