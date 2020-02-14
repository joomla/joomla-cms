<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined('_JEXEC') or die ('restricted aceess');
if(!class_exists('SppagebuilderHelper')) {
	require_once dirname(__DIR__) . '/helpers/sppagebuilder.php';
}
?>
<div class="sp-pagebuilder-brand">
	<a href="index.php?option=com_sppagebuilder">
		<img src="<?php echo JURI::root(true) . '/administrator/components/com_sppagebuilder/assets/img/logo-white.svg'; ?>" alt="SP Page Builder">
		<span>Lite <?php echo SppagebuilderHelper::getVersion(); ?></span>
	</a>
</div>
