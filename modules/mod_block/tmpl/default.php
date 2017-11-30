<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_block
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<section class="container-modules <?php echo $params->get('position-class') ?>">
	<div class="wrapper">
		<?php echo JHtml::_('content.prepare', '{loadposition '.$params->get('position').'}') ?>
	</div>
</section>
