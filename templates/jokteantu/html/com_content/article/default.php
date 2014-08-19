<?php
/**	
 * @package     Jokte.Site
 * @subpackage	jokteantu
 * @author 	    Equipo de desarrollo juuntos.
 * @copyleft    (comparte igual)  Jokte!
 * @license     GNU General Public License version 3 o superior.
*/

// Sin acceso directo
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

// Create shortcuts to some parameters.
$params		= $this->item->params;
$canEdit	= $this->item->params->get('access-edit');
$user		= JFactory::getUser();
?>
<div class="item-page<?php echo $this->pageclass_sfx?>">
<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
<?php endif; ?>

<?php 
// Nuevo Jokte v1.2.2 
if ($params->get('show_copete')) :
	if (in_array('article', $params->get('show_copete_view'))) :
		if ($this->item->copete != Null): ?>
		<h4><?php echo $this->item->copete; ?></h4>
	<?php 
		endif; 
	endif; 
endif;
?>

<div id="cabecera-articulo">
<?php if ($params->get('show_publish_date')) : ?>
	<dd class="published">
	<div class="fecha">
	<span class="dia"><?php echo JText::sprintf( JHtml::_('date',$this->item->publish_up, JText::_('d'))); ?></span>
	<div class="fecha2">
	<span class="mes"><?php echo JText::sprintf( JHtml::_('date',$this->item->publish_up, JText::_('M'))); ?></span>
	<span class="año"><?php echo JText::sprintf( JHtml::_('date',$this->item->publish_up, JText::_('Y'))); ?></span>
	</div>
	</div>
	</dd>
<?php endif; ?>

<?php if ($params->get('show_title')) : ?>
	<h2>
	<?php if ($params->get('link_titles') && !empty($this->item->readmore_link)) : ?>
		<a href="<?php echo $this->item->readmore_link; ?>">
		<?php echo $this->escape($this->item->title); ?></a>
	<?php else : ?>
		<?php echo $this->escape($this->item->title); ?>
	<?php endif; ?>
	</h2>
<?php endif; ?>

<?php 
	// Nuevo Jokte v1.2.2
	if ($params->get('show_subtitle')) : 
		if (in_array('article', $params->get('show_subtitle_view'))) :
		?>
		<div class="subtitulos">
			<h3><?php echo $this->escape($this->item->subtitle); ?></h3>
		</div>	
<?php 	endif; 
	endif;
?>

<?php if ($canEdit ||  $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
	<ul class="actions">
	<?php if (!$this->print) : ?>
		<?php if ($params->get('show_print_icon')) : ?>
			<li class="print-icon">
			<?php echo JHtml::_('icon.print_popup',  $this->item, $params); ?>
			</li>
		<?php endif; ?>

		<?php if ($params->get('show_email_icon')) : ?>
			<li class="email-icon">
			<?php echo JHtml::_('icon.email',  $this->item, $params); ?>
			</li>
		<?php endif; ?>

		<?php if ($canEdit) : ?>
			<li class="edit-icon">
			<?php echo JHtml::_('icon.edit', $this->item, $params); ?>
			</li>
		<?php endif; ?>

	<?php else : ?>
		<li>
		<?php echo JHtml::_('icon.print_screen',  $this->item, $params); ?>
		</li>
	<?php endif; ?>

	</ul>
<?php endif; ?>

<?php 
	if (in_array('article', $params->get('show_socialbuttons'))) :
		if ($params->get('position_socialbuttons') == 'top') : ?>
		<?php echo $this->loadTemplate('social'); ?>
<?php endif; 
	endif;
?>

<?php  if (!$params->get('show_intro')) :
	echo $this->item->event->afterDisplayTitle;
endif; ?>

<?php echo $this->item->event->beforeDisplayContent; ?>

<?php $useDefList = (($params->get('show_author')) or ($params->get('show_category')) or ($params->get('show_parent_category'))
	or ($params->get('show_create_date')) or ($params->get('show_modify_date')) or ($params->get('show_publish_date'))
	or ($params->get('show_hits'))); ?>

<?php if ($useDefList) : ?>
	<dl class="article-info">

<?php endif; ?>
<?php if ($params->get('show_author') && !empty($this->item->author )) : ?>
	<dd class="createdby">
	<?php $author = $this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author; ?>
	<?php if (!empty($this->item->contactid) && $params->get('link_author') == true): ?>
	<?php
		$needle = 'index.php?option=com_contact&view=contact&id=' . $this->item->contactid;
		$item = JSite::getMenu()->getItems('link', $needle, true);
		$cntlink = !empty($item) ? $needle . '&Itemid=' . $item->id : $needle;
	?>
		<?php echo JText::sprintf('TPL_JOKTEANTU_ESCRITO_POR', JHtml::_('link', JRoute::_($cntlink), $author)); ?>
	<?php else: ?>
		<?php echo JText::sprintf('TPL_JOKTEANTU_ESCRITO_POR', $author); ?>
	<?php endif; ?>
	</dd>
<?php endif; ?>
<?php if ($params->get('show_hits')) : ?>
	<dd class="hits">
	<?php echo JText::sprintf('COM_CONTENT_ARTICLE_HITS', $this->item->hits); ?>
	</dd>
<?php endif; ?>
<?php if ($params->get('show_parent_category') && $this->item->parent_slug != '1:root') : ?>
	<dd class="parent-category-name">
	<?php	$title = $this->escape($this->item->parent_title);
	$url = '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->parent_slug)).'">'.$title.'</a>';?>
	<?php if ($params->get('link_parent_category') and $this->item->parent_slug) : ?>
		<?php echo JText::sprintf('COM_CONTENT_PARENT', $url); ?>
	<?php else : ?>
		<?php echo JText::sprintf('COM_CONTENT_PARENT', $title); ?>
	<?php endif; ?>
	</dd>
<?php endif; ?>
<?php if ($params->get('show_category')) : ?>
	<dd class="category-name">
	<?php 	$title = $this->escape($this->item->category_title);
	$url = '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->catslug)).'">'.$title.'</a>';?>
	<?php if ($params->get('link_category') and $this->item->catslug) : ?>
		<?php echo JText::sprintf('COM_CONTENT_CATEGORY', $url); ?>
	<?php else : ?>
		<?php echo JText::sprintf('COM_CONTENT_CATEGORY', $title); ?>
	<?php endif; ?>
	</dd>
<?php endif; ?>
<?php if ($params->get('show_create_date')) : ?>
	<dd class="create">
	<?php echo JText::sprintf('COM_CONTENT_CREATED_DATE_ON', JHtml::_('date',$this->item->created, JText::_('d.M'))); ?>
	</dd>
<?php endif; ?>
<?php if ($params->get('show_modify_date')) : ?>
	<dd class="modified">
	<?php echo JText::sprintf('COM_CONTENT_LAST_UPDATED', JHtml::_('date',$this->item->modified, JText::_('d.M'))); ?>
	</dd>
<?php endif; ?>



<?php if ($useDefList) : ?>
	</dl>
<?php endif; ?>
</div>
<?php if (isset ($this->item->toc)) : ?>
	<?php echo $this->item->toc; ?>
<?php endif; ?>
<?php if ($params->get('access-view')):?>
	<?php	
	if ($params->get('show_avatar') <> '0') :
	  if (in_array('article', $params->get('show_avatar_view'))) :
		echo JHtml::_('utiles.avatar',$this->item, $params); 
	  endif; 
	endif;
	?>
	<?php echo $this->item->text; ?>
	
	<?php 
	// Nuevo Jokte v1.2.2
	if (in_array('article', $params->get('show_socialbuttons'))) :
		if ($params->get('position_socialbuttons') == 'bottom') : 
			echo $this->loadTemplate('social');
		endif; 
	endif;
	?>
	
	<?php // Nuevo Jokte v1.2.2 ?>
	<?php if ($this->item->params->get('show_attachments') == '1') : ?>
		<?php if(isset($this->item->attachments) AND (!empty($this->item->attachments))) : ?>
			<?php  
				// Cargo los archivos
				$files = json_decode($this->item->attachments);				
			?>
				<div id="attachments">
					<table class="table">
						<thead>
							<tr class="attach-header">
								<th>Nombre de Archivo</th>
								<th>Descarga</th>
								<th>Tamaño</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$xf = 0;
							foreach ($files as $file) : 
								if (!empty($file) || $file != "") :
									// Extraigo el el nombre de archivo
									$fileName = JFile::getName($file);
									//Extraigo la extensión
									$fileExtension = JFile::getExt($file);
									$size = round(@filesize($file) / 1024, 2);
									($xf == 2) ? $xf = 0 : $xf = $xf;
							?>
							<tr class="attach-files<?php echo '-'.$xf ?>">
								<td><?php echo $fileName ?></td>
								<td style="text-align:center">
									<?php if ($this->item->params->get('attachments_antileech') == '1') : ?>
										<a href="<?php echo JRoute::_('index.php?task=download&filename='. $fileName) ?>" 
											title="<?php echo JText::_('CLICK HERE TO DOWNLOADS');?>">
											<?php echo JHtml::_('image', 'media/mime-icon-16/'.$fileExtension.'.png', JText::sprintf('COM_MEDIA_IMAGE_TITLE', $fileName), array('height' => 16, 'width' => 16), true); ?>
										</a>
												
									<?php else: ?>
										<a class="btn btn-small" href="<?php echo $file ?>">
											<?php echo JHtml::_('image', 'media/mime-icon-16/'.$fileExtension.'.png', JText::sprintf('COM_MEDIA_IMAGE_TITLE', $fileName), array('height' => 16, 'width' => 16), true); ?>
										</a>
									<?php endif; ?>
								</td>
								<td>
									<?php echo $size.' Kb'; ?></a>
								</td>
							</tr> 
							<?php 
								endif;
								$xf = 1;
							endforeach; ?> 
						</tbody>
					</table>
				</div>
			<?php endif; ?> 
	<?php endif; ?>  
	<br />
	<?php //optional teaser intro text for guests ?>
<?php elseif ($params->get('show_noauth') == true and  $user->get('guest') ) : ?>
	<?php echo $this->item->introtext; ?>
	<?php //Optional link to let them register to see the whole article. ?>
	<?php if ($params->get('show_readmore') && $this->item->fulltext != null) :
		$link1 = JRoute::_('index.php?option=com_users&view=login');
		$link = new JURI($link1);?>
		<p class="readmore">
		<a href="<?php echo $link; ?>">
		<?php $attribs = json_decode($this->item->attribs);  ?>
		<?php
		if ($attribs->alternative_readmore == null) :
			echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
		elseif ($readmore = $this->item->alternative_readmore) :
			echo $readmore;
			if ($params->get('show_readmore_title', 0) != 0) :
			    echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
			endif;
		elseif ($params->get('show_readmore_title', 0) == 0) :
			echo JText::sprintf('COM_CONTENT_READ_MORE_TITLE');
		else :
			echo JText::_('COM_CONTENT_READ_MORE');
			echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
		endif; ?></a>
		</p>
	<?php endif; ?>
<?php endif; ?>
<?php echo $this->item->event->afterDisplayContent; ?>
</div>
<?php // Nuevo Jokte v1.2.2 ?>
<?php 
	if ($params->get('show_simpletags')) : 
		if (in_array('article', $params->get('show_simpletags_view'))) :		
		$tags = JHtml::_('utiles.simpletags', $this->item->metakey);
		?>
		<div class="etiquetas">
			<span class="tagslabel"><?php echo JText::_('COM_CONTENT_LABEL_TAGS').': '; ?></span> 
			<?php foreach ($tags as $etiqueta): ?>
				<span class="tag"><?php echo $etiqueta; ?></span>
			<?php endforeach; ?>	
		</div>
		<br />
<?php 	endif;
	endif; 
if ($params->get('show_disqus')) : 
	echo JHtml::_('utiles.disqus', $this->item,$params);
endif;
?>