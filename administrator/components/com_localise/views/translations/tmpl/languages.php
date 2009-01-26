<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

// TOOLBAR
JToolbarHelper::title( JText::_( 'Translation Manager' ), 'langmanager.png' );
JToolbarHelper::custom('files','edit','','View Files');
JToolbarHelper::makeDefault();
JToolbarHelper::archiveList('package','Package');
JToolbarHelper::divider();
JToolbarHelper::deleteList(JText::_('Confirm Delete XML'),'removexml');
JToolbarHelper::editList('editxml');
JToolbarHelper::addNew('addxml');
JToolbarHelper::divider();
// ! configure/preferences check will be deprecated
( is_callable( array('JToolbarHelper', 'preferences') ) ) ? JToolbarHelper::preferences('com_localise',400,600) : JToolbarHelper::configuration('com_localise',400,600);;

// build a submenu
$submenu = '
<div class="submenu-box">
	<div class="submenu-pad">
		<ul id="submenu">';
foreach ( array('*'=>JText::_('Any Client')) + $this->options['clients'] as $k=>$v ) {
	$class = ($k == $this->options['filter_client']) ? ' class="active"' : '';
	$submenu .= "\n\t\t\t" . '<li><a href="index.php?option=com_localise&filter_client=' . $k .'"' . $class . '>' . $v . '</a></li>';
}
$submenu .= '
		</ul>
		<div class="clr"></div>
	</div>
</div>
';
$document =& JFactory::getDocument();
$document->setBuffer( $submenu, 'module', 'submenu' );
?>

<div id="localise">
<form action="index.php" method="post" name="adminForm">
	<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
	<input type="hidden" name="option" value="com_localise" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="1" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

	<table class="adminlist" id="languages">

		<thead>
			<tr>
				<th width="20">&nbsp;</th>
				<th width="15%"><?php echo JText::_( 'Client' ); ?></th>
				<th width="20%"><?php echo JHtml::_( 'grid.sort', 'Language', 'tag', $this->lists['order_Dir'], $this->lists['order'], $this->options['task'] ); ?></th>
				<th width="5%"><?php echo JText::_( 'Default' ); ?></th>
				<th width="5%"><?php echo JText::_( 'XML' ); ?></th>
				<th width="5%"><?php echo JText::_( 'Files' ); ?></th>
				<th width="5%"><?php echo JText::_( 'Version' ); ?></th>
				<th width="60"><?php echo JText::_( 'Date' ); ?></th>
				<th width="20%"><?php echo JText::_( 'Author' ); ?></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<td width="100%" colspan="9">
					<?php echo $this->pagenav->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>

		<tbody>
		<?php
		// process the rows (each is an XML language file)
		$k = 0;
		for ($i=0, $n=count( $this->data ); $i < $n; $i++) {
			$row =& $this->data[$i];
			?>
			<tr class="row<?php echo $i; ?>">
				<td width="20">
					<input type="radio" name="client_lang" value="<?php echo $row->client_lang;?>" <?php if ($row->client_lang == $this->options['client_lang']) echo 'checked="checked"'; ?> />
				</td>
				<td width="15%">
					<b><?php echo $row->client_name	;?></b>
				</td>
				<td width="25%">
					<?php echo $this->getTooltip( '['.$row->tag.'] &nbsp; '.$row->name, $row->description, $row->name, '' ); ?>
				</td>
				<td align="center">
					<?php echo ($row->isdefault) ? '<img src="templates/khepri/images/menu/icon-16-default.png" alt="'.JText::_('Default').'" />' : '&nbsp;'; ?>
				</td>
				<td align="center">
					<?php echo '<a href="index.php?option=com_localise&amp;task=editxml&amp;client_lang=' . $row->client_lang . '">' . $this->getTooltip( '<img src="components/com_localise/images/xml.png" alt="XML" />', null, 'Edit XML', 'TC' ) . '</a>'; ?>
				</td>
				<td align="center">
					<?php echo '<a href="index.php?option=com_localise&amp;task=files&amp;client_lang=' . $row->client_lang .'">' . $this->getTooltip( $row->files, null, 'View Files', 'TC' ) . '</a>'; ?>
				</td>
				<td align="center">
					<?php echo $row->version; ?>
				</td>
				<td align="center">
					<?php echo $row->creationDate; ?>
				</td>
				<td align="center">
					<?php echo $row->author; ?>
				</td>
			</tr>
		<?php
		}
		?>
		</tbody>

	</table>

</form>
</div>