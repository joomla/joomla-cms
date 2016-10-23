<?php
/**
 * @package Tag View Feature for Joomla!
 * @copyright Copyright (c) 2016 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

JHtml::_('behavior.caption');
?>
<div class="category-list<?php echo $this->pageclass_sfx;?>">

<?php
$this->subtemplatename = 'articles';
echo JLayoutHelper::render('joomla.content.tag_default', $this);
?>

</div>
