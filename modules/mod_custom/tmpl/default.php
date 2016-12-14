<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_custom
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>


<div class="custom<?php echo $moduleclass_sfx ?>"<?php if ($params->get('backgroundimage')) : ?> style="background-image:url(<?php echo $params->get('backgroundimage');?>)"<?php endif;?><?php if ($params->get('modulecss_id')) : ?> id="<?php echo $params->get('module_id'); ?>"<?php endif;?>>
	<?php echo $module->content;?>
</div>
