<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_block
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (!empty($params->get('block-image')))
{
	JFactory::getDocument()->addStyleDeclaration(
		'section.' . $params->get('block-position') . ' { 
			padding-top: 15px;
			padding-bottom: 15px;
			background-image: url(' . $params->get('block-image') . ');
		}
		section.' . $params->get('block-position') . ' + main {
			padding-top: 7.5px;
		}
		'
	);
}

?>

<section id="<?php echo $params->get('block-id'); ?>" class="module-block <?php echo $params->get('block-position'); ?> <?php echo $params->get('block-class'); ?>">
	<div class="wrapper">
		<?php echo JHtml::_('content.prepare', '{loadposition ' . $params->get('block-position') . '}'); ?>
	</div>
</section>
