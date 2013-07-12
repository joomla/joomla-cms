<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
// Checking if loaded via index.php or component.php
$tmpl = $input->getCmd('tmpl', '');
$document = JFactory::getDocument();

//Adding System Links
$list = array();
$o = new JObject;
$o->title    = 'COM_MENUS_TYPE_EXTERNAL_URL';
$o->type     = 'url';
$o->description  = 'COM_MENUS_TYPE_EXTERNAL_URL_DESC';
$o->request    = null;
$list[] = $o;

$o = new JObject;
$o->title    = 'COM_MENUS_TYPE_ALIAS';
$o->type     = 'alias';
$o->description  = 'COM_MENUS_TYPE_ALIAS_DESC';
$o->request    = null;
$list[] = $o;

$o = new JObject;
$o->title    = 'COM_MENUS_TYPE_SEPARATOR';
$o->type     = 'separator';
$o->description  = 'COM_MENUS_TYPE_SEPARATOR_DESC';
$o->request    = null;
$list[] = $o;

$o = new JObject;
$o->title    = 'COM_MENUS_TYPE_HEADING';
$o->type     = 'heading';
$o->description  = 'COM_MENUS_TYPE_HEADING_DESC';
$o->request    = null;
$list[] = $o;
$this->types['COM_MENUS_TYPE_SYSTEM'] = $list;

$sortedTypes = array();
foreach ($this->types as $name => $list)
{
	$tmp = array();
	foreach ($list as $item)
	{
		$tmp[JText::_($item->title)] = $item;
	}
	ksort($tmp);
	$sortedTypes[JText::_($name)] = $tmp;
}
ksort($sortedTypes);
?>

<script type="text/javascript">
	setmenutype = function(type)
	{
		<?php if ($tmpl) : ?>
			window.parent.Joomla.submitbutton('item.setType', type);
			window.parent.SqueezeBox.close();
		<?php else : ?>
			window.location="index.php?option=com_menus&view=item&task=item.setType&layout=edit&type="+('item.setType', type);
		<?php endif; ?>
	}
</script>

<?php echo JHtml::_('bootstrap.startAccordion', 'collapseTypes', array('active' => 'slide1')); ?>
	<?php
		$i = 0;
		foreach ($sortedTypes as $name => $list) : ?>
		<?php echo JHtml::_('bootstrap.addSlide', 'collapseTypes', $name, 'collapse' . $i++); ?>
			<ul class="nav nav-tabs nav-stacked">
				<?php foreach ($list as $title => $item) : ?>
					<li>
						<a class="choose_type" href="#" title="<?php echo JText::_($item->description); ?>"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title' => $item->title, 'request' => $item->request))); ?>')">
							<?php echo $title;?> <small class="muted"><?php echo JText::_($item->description); ?></small>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php echo JHtml::_('bootstrap.endSlide'); ?>
	<?php endforeach; ?>
	<?php echo JHtml::_('bootstrap.endSlide'); ?>
<?php echo JHtml::_('bootstrap.endAccordion'); ?>
