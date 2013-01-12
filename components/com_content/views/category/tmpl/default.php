<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');

JHtml::_('behavior.caption');
?>
<div class="category-list<?php echo $this->pageclass_sfx;?>">
<?php
$this->subtemplatename = 'articles';
?>

<?php
	$layouttop  = new JLayoutFile('joomla.content.category_default');
	echo $layouttop->render($this);
?>

</div>
