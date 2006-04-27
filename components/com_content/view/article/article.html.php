<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * HTML Article View class for the Content component
 *
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JViewHTMLArticle extends JView
{
	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Article';

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function display()
	{
		// Initialize variables
		$app	= & $this->get('Application');
		$user	= & $app->getUser();
		$menu	= & $this->get('Menu');
		$Itemid	= $menu->id;
		$linkOn = null;
		$linkText = null;

		// At some point in the future this will be in a request object
		$page	= JRequest::getVar('limitstart', 0, '', 'int');
		$noJS	= JRequest::getVar('hide_js', 0, '', 'int');
		$type	= JRequest::getVar('format', 'html');

		// Get the article from the model
		$article	= & $this->get('Article');
		$params		= & $article->parameters;

		// Create a user access object for the current user
		$access = new stdClass();
		$access->canEdit	= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn	= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish	= $user->authorize('action', 'publish', 'content', 'all');

		// Handle BreadCrumbs
		$breadcrumbs = & $app->getPathWay();
		if (!empty ($Itemid)) {
			// Section
			if (!empty ($article->section)) {
				$breadcrumbs->addItem($article->section, sefRelToAbs('index.php?option=com_content&amp;task=section&amp;id='.$article->sectionid.'&amp;Itemid='.$Itemid));
			}
			// Category
			if (!empty ($article->section)) {
				$breadcrumbs->addItem($article->category, sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$article->sectionid.'&amp;id='.$article->catid.'&amp;Itemid='.$Itemid));
			}
		}
		// Item
		$breadcrumbs->addItem($article->title, '');

		// Handle Page Title
		$doc = & $app->getDocument();
		$doc->setTitle($article->title);

		// Handle metadata
		$doc->setDescription( $article->metadesc );
		$doc->setMetadata('keywords', $article->metakey);

		// Process the content plugins
		JPluginHelper::importPlugin('content');
		$results = $app->triggerEvent('onPrepareContent', array (& $article, & $params, $page));

		// If there is a pagebreak heading or title, add it to the page title
		if (isset ($article->page_title)) {
			$doc->setTitle($article->title.' '.$article->page_title);
		}

		// Time to build the readmore button if it should be shown
		if ($params->get('readmore') || $params->get('link_titles')) {
			if ($params->get('intro_only')) {
				// Checks to make sure user has access to the full article
				if ($article->access <= $user->get('gid')) {
					$Itemid = JContentHelper::getItemid($article->id);
					$linkOn = sefRelToAbs("index.php?option=com_content&amp;task=view&amp;id=".$article->id."&amp;Itemid=".$Itemid);

					if (@$article->readmore) {
					// text for the readmore link
						$linkText = JText::_('Read more...');
					}
				} else {
					$linkOn = sefRelToAbs("index.php?option=com_registration&amp;task=register");

					if (@$article->readmore) {
					// text for the readmore link if accessible only if registered
						$linkText = JText::_('Register to read more...');
					}
				}
			}
		}

		// Popup pages get special treatment for page titles
		if ($params->get('popup') && $type =! 'html') {
			$doc->setTitle($app->getCfg('sitename').' - '.$article->title);
		}

		// If the user can edit the article, display the edit icon
		if ($access->canEdit) {
			?>
			<div class="contentpaneopen_edit<?php echo $params->get( 'pageclass_sfx' ); ?>" style="float: left;">
				<?php JContentHTMLHelper::editIcon($article, $params, $access); ?>
			</div>
			<?php
		}

		// Time to build the title bar... this may also include the pdf/print/email buttons if enabled
		if ($params->get('item_title') || $params->get('pdf') || $params->get('print') || $params->get('email')) {
			// Build the link for the print button
			$printLink = $app->getBaseURL().'index2.php?option=com_content&amp;task=view&amp;id='.$article->id.'&amp;Itemid='.$Itemid.'&amp;pop=1&amp;page='.@ $page;
			?>
			<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<tr>
			<?php

			// displays Item Title
			JContentHTMLHelper::title($article, $params, $linkOn, $access);

			// displays PDF Icon
			JContentHTMLHelper::pdfIcon($article, $params, $linkOn, $noJS);

			// displays Print Icon
			mosHTML::PrintIcon($article, $params, $noJS, $printLink);

			// displays Email Icon
			JContentHTMLHelper::emailIcon($article, $params, $noJS);
			?>
			</tr>
			</table>
			<?php
		}

		// If only displaying intro, display the output from the onAfterDisplayTitle event
		if (!$params->get('intro_only')) {
			$results = $app->triggerEvent('onAfterDisplayTitle', array (& $article, & $params, $page));
			echo trim(implode("\n", $results));
		}

		// Display the output from the onBeforeDisplayContent event
		$onBeforeDisplayContent = $app->triggerEvent('onBeforeDisplayContent', array (& $article, & $params, $page));
		echo trim(implode("\n", $onBeforeDisplayContent));
		?>
		<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php

		// displays Section & Category
		JContentHTMLHelper::sectionCategory($article, $params);

		// displays Author Name
		JContentHTMLHelper::author($article, $params);

		// displays Created Date
		JContentHTMLHelper::createDate($article, $params);

		// displays Urls
		JContentHTMLHelper::url($article, $params);
		?>
		<tr>
			<td valign="top" colspan="2">
		<?php

		// displays Table of Contents
		JContentHTMLHelper::toc($article);

		// displays Item Text
		echo ampReplace($article->text);
		?>
			</td>
		</tr>
		<?php

		// displays Modified Date
		JContentHTMLHelper::modifiedDate($article, $params);

		// displays Readmore button
		JContentHTMLHelper::readMore($params, $linkOn, $linkText);
		?>
		</table>
		<span class="article_seperator">&nbsp;</span>
		<?php

		// Fire the after display content event
		$onAfterDisplayContent = $app->triggerEvent('onAfterDisplayContent', array (& $article, & $params, $page));
		echo trim(implode("\n", $onAfterDisplayContent));

		// displays close button in pop-up window
		mosHTML::CloseButton($params, $noJS);
	}

	function edit()
	{
		// Initialize variables
		$app	= & $this->get('Application');
		$doc	= & $app->getDocument();
		$user	= & $app->getUser();
		$menu	= & $this->get('Menu');
		$Itemid	= $menu->id;

		// At some point in the future this will come from a request object
		$page		= JRequest::getVar('limitstart', 0, '', 'int');
		$noJS		= JRequest::getVar('hide_js', 0, '', 'int');
		$Returnid	= JRequest::getVar('Returnid', $Itemid, '', 'int');

		// Add the Calendar includes to the document <head> section
		$doc->addStyleSheet('includes/js/calendar/calendar-mos.css');
		$doc->addScript('includes/js/calendar/calendar_mini.js');
		$doc->addScript('includes/js/calendar/lang/calendar-en.js');

		// Get the article from the model
		$article	= & $this->get('Article');
		$params		= $article->parameters;

		// Get the lists
		$lists = $this->_buildEditLists();

		// Load the JEditor object
		jimport('joomla.presentation.editor');
		$editor = & JEditor::getInstance();

		// Load the mosTabs object
		$tabs = new mosTabs(0);

		// Load the Overlib library
		mosCommonHTML::loadOverlib();

		// Ensure the row data is safe html
		mosMakeHtmlSafe($article);

		// Build the page title string
		$title = $article->id ? JText::_('Edit') : JText::_('New');

		// Set page title
		$doc->setTitle($title);

		// Add pathway item
		$breadcrumbs = & $app->getPathway();
		$breadcrumbs->addItem($title, '');

		?>
	  	<script language="javascript" type="text/javascript">
		onunload = WarnUser;
		var folderimages = new Array;
		<?php
		$i = 0;
		foreach ($lists['images'] as $k => $items) {
			foreach ($items as $v) {
				echo "\n	folderimages[".$i ++."] = new Array( '$k','".addslashes($v->value)."','".addslashes($v->text)."' );";
			}
		}
		?>

		function delay(gap)
		{ /* gap is in millisecs */
			var then,now; then=new Date().getTime();
			now=then;
			while((now-then)<gap)
			{now=new Date().getTime();}
		}

		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				window.top.hidePopWin();
				return;
			}

			// assemble the images back into one field
			var temp = new Array;
			for (var i=0, n=form.imagelist.options.length; i < n; i++) {
				temp[i] = form.imagelist.options[i].value;
			}
			form.images.value = temp.join( '\n' );
			try {
				form.onsubmit();
			}
			catch(e){}
			// do field validation
			if (form.title.value == "") {
				alert ( "<?php echo JText::_( 'Content item must have a title', true ); ?>" );
			} else if (parseInt('<?php echo $article->sectionid;?>')) {
				// for content items
				if (getSelectedValue('adminForm','catid') < 1) {
					alert ( "<?php echo JText::_( 'Please select a category', true ); ?>" );
				} else {
		<?php
		// JavaScript for extracting editor text
		echo $editor->save( 'text' );
		?>
					submitform(pressbutton);
					window.top.hidePopWin();
					delay(500);
					window.top.location.reload(true);
				}
			}
		}
		</script>
		<?php
		// Build overlib text
		$docinfo = '<table><tr><td>';
		$docinfo .= '<strong>'.JText::_('Expiry Date').':</strong> ';
		$docinfo .= '</td><td>';
		$docinfo .= $article->publish_down;
		$docinfo .= '</td></tr><tr><td>';
		$docinfo .= '<strong>'.JText::_('Version').':</strong> ';
		$docinfo .= '</td><td>';
		$docinfo .= $article->version;
		$docinfo .= '</td></tr><tr><td>';
		$docinfo .= '<strong>'.JText::_('Created').':</strong> ';
		$docinfo .= '</td><td>';
		$docinfo .= $article->created;
		$docinfo .= '</td></tr><tr><td>';
		$docinfo .= '<strong>'.JText::_('Last Modified').':</strong> ';
		$docinfo .= '</td><td>';
		$docinfo .= $article->modified;
		$docinfo .= '</td></tr><tr><td>';
		$docinfo .= '<strong>'.JText::_('Hits').':</strong> ';
		$docinfo .= '</td><td>';
		$docinfo .= $article->hits;
		$docinfo .= '</td></tr></table>';
		?>
		<form action="index.php" method="post" name="adminForm" onSubmit="javascript:setgood();">

		<table class="adminform" width="100%">
		<tr>
			<td>
				<div style="float: left;">
					<label for="title">
						<?php echo JText::_( 'Title' ); ?>:
					</label>
					<input class="inputbox" type="text" id="title" name="title" size="50" maxlength="100" value="<?php echo $article->title; ?>" />
					&nbsp;&nbsp;&nbsp;
					<?php /*echo mosToolTip('<table>'.$docinfo.'</table>', JText::_( 'Item Information', true ), '', '', '<strong>['.JText::_( 'Info', true ).']</strong>');*/ ?>
				</div>
				<div style="float: right;">
				<button type="button" onclick="javascript:submitbutton('save')">
					<?php echo JText::_('Save') ?>
				</button>
				<button type="button" onclick="javascript:submitbutton('cancel')" />
					<?php echo JText::_('Cancel') ?>
				</button>
				</div>
			</td>
		</tr>
		</table>

		<!-- Begin Article Parameters Section -->
		<!-- Images Tab -->
		<?php
		$title = JText::_('Editor');
		$tabs->startPane('content-pane');
		$tabs->startTab($title, 'editor-page');

		/*
		 * We need to unify the introtext and fulltext fields and have the
		 * fields separated by the {readmore} tag, so lets do that now.
		 */
		if (JString::strlen($article->fulltext) > 1) {
			$article->text = $article->introtext.'{readmore}'.$article->fulltext;
		} else {
			$article->text = $article->introtext;
		}
		// Display the editor
		// arguments (areaname, content, hidden field, width, height, rows, cols)
		echo $editor->display('text', $article->text, '655', '400', '70', '15');
		echo $editor->getButtons('text');
		?>

		<!-- Images Tab -->
		<?php
		$title = JText::_('Images');
		$tabs->endTab();
		$tabs->startTab($title, 'images-page');
		?>
			<table width="100%" class="adminform">
			<tr>
				<td colspan="4">
					<label for="folders">
						<?php echo JText::_( 'Sub-folder' ); ?>
					</label>
					- <?php echo $lists['folders'];?>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="imagefiles">
						<?php echo JText::_( 'Gallery Images' ); ?>
					</label>
				</td>
				<td width="1%">
				</td>
				<td valign="top">
					<?php echo JText::_( 'Content Images' ); ?>
				</td>
				<td valign="top">
					<?php echo JText::_( 'Edit Image' ); ?>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<?php echo $lists['imagefiles'];?>
					<br />
					<input class="button" type="button" value="<?php echo JText::_( 'Insert' ); ?>" onclick="addSelectedToList('adminForm','imagefiles','imagelist')" />
				</td>
				<td width="2%">
					<input class="button" type="button" value=">>" onclick="addSelectedToList('adminForm','imagefiles','imagelist')" title="<?php echo JText::_( 'Add' ); ?>"/>
					<br/>
					<input class="button" type="button" value="<<" onclick="delSelectedFromList('adminForm','imagelist')" title="<?php echo JText::_( 'Remove' ); ?>"/>
				</td>
				<td valign="top">
					<?php echo $lists['imagelist'];?>
					<br />
					<input class="button" type="button" value="<?php echo JText::_( 'Up' ); ?>" onclick="moveInList('adminForm','imagelist',adminForm.imagelist.selectedIndex,-1)" />
					<br />
					<input class="button" type="button" value="<?php echo JText::_( 'Down' ); ?>" onclick="moveInList('adminForm','imagelist',adminForm.imagelist.selectedIndex,+1)" />
				</td>
				<td valign="top" width="100%">
					<table width="100%">
					<tr>
						<td align="right">
							<label for="_source">
								<?php echo JText::_( 'Source' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" id= "_source" name= "_source" value="" size="15" />
						</td>
					</tr>
					<tr>
						<td align="right" valign="top">
							<label for="_align">
								<?php echo JText::_( 'Align' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $lists['_align']; ?>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="_alt">
								<?php echo JText::_( 'Alt Text' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" id="_alt" name="_alt" value="" size="15" />
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="_border">
								<?php echo JText::_( 'Border' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" id="_border" name="_border" value="" size="3" maxlength="1" />
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="_caption">
								<?php echo JText::_( 'Caption' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" id="_caption" name="_caption" value="" size="30" />
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="_caption_position">
								<?php echo JText::_( 'Caption Position' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $lists['_caption_position']; ?>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="_caption_align">
								<?php echo JText::_( 'Caption Align' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $lists['_caption_align']; ?>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="_width">
								<?php echo JText::_( 'Caption Width' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" id="_width" name="_width" value="" size="5" maxlength="5" />
						</td>
					</tr>
					<tr>
						<td align="right"></td>
						<td>
							<input class="button" type="button" value="<?php echo JText::_( 'Apply' ); ?>" onclick="applyImageProps()" />
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<img name="view_imagefiles" src="images/M_images/blank.png" width="50" alt="<?php echo JText::_( 'No Image' ); ?>" />
				</td>
				<td>&nbsp;</td>
				<td>
					<img name="view_imagelist" src="images/M_images/blank.png" width="50" alt="<?php echo JText::_( 'No Image' ); ?>" />
				</td>
				<td>&nbsp;</td>
			</tr>
			</table>

		<!-- Publishing Tab -->
		<?php
		$title = JText::_('Publishing');
		$tabs->endTab();
		$tabs->startTab($title, 'publish-page');
		?>

			<table class="adminform">
		<?php

		// If the document is in a section display the section and category dropdown
		if ($article->sectionid) {
		?>
			<tr>
				<td>
					<label for="catid">
						<?php echo JText::_( 'Section' ); ?>:
					</label>
				</td>
				<td>
					<strong>
						<?php echo $article->section;?>
					</strong>
				</td>
			</tr>
			<tr>
				<td>
					<label for="catid">
						<?php echo JText::_( 'Category' ); ?>:
					</label>
				</td>
				<td>
					<?php echo $lists['catid']; ?>
				</td>
			</tr>
			<?php
		}

		if ($user->authorize('action', 'publish', 'content', 'all')) {
		?>
				<tr>
					<td >
						<label for="state">
							<?php echo JText::_( 'Published' ); ?>:
						</label>
					</td>
					<td>
						<?php echo $lists['state']; ?>
					</td>
				</tr>
				<?php
		}
		?>
			<tr>
				<td width="120">
					<label for="frontpage">
						<?php echo JText::_( 'Show on Front Page' ); ?>:
					</label>
				</td>
				<td>
					<?php echo $lists['frontpage']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="created_by_alias">
						<?php echo JText::_( 'Author Alias' ); ?>:
					</label>
				</td>
				<td>
					<input type="text" id="created_by_alias" name="created_by_alias" size="50" maxlength="100" value="<?php echo $article->created_by_alias; ?>" class="inputbox" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="publish_up">
						<?php echo JText::_( 'Start Publishing' ); ?>:
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="publish_up" id="publish_up" size="25" maxlength="19" value="<?php echo $article->publish_up; ?>" />
					<input type="reset" class="button" value="..." onclick="return showCalendar('publish_up', 'y-mm-dd');" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="publish_down">
						<?php echo JText::_( 'Finish Publishing' ); ?>:
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="publish_down" id="publish_down" size="25" maxlength="19" value="<?php echo $article->publish_down; ?>" />
					<input type="reset" class="button" value="..." onclick="return showCalendar('publish_down', 'y-mm-dd');" />
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="access">
						<?php echo JText::_( 'Access Level' ); ?>:
					</label>
				</td>
				<td>
					<?php echo $lists['access']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="ordering">
						<?php echo JText::_( 'Ordering' ); ?>:
					</label>
				</td>
				<td>
					<?php echo $lists['ordering']; ?>
				</td>
			</tr>
			</table>

		<!-- Metadata Tab -->
		<?php
		$title = JText::_('Metadata');
		$tabs->endTab();
		$tabs->startTab($title, 'meta-page');
		?>
			<table class="adminform">
			<tr>
				<td  valign="top">
					<label for="metadesc">
						<?php echo JText::_( 'Description' ); ?>:
					</label>
				</td>
				<td>
					<textarea rows="5" cols="50" style="width:500px; height:120px" class="inputbox" id="metadesc" name="metadesc"><?php echo str_replace('&','&amp;',$article->metadesc); ?></textarea>
				</td>
			</tr>
			<tr>
				<td  valign="top">
					<label for="metakey">
						<?php echo JText::_( 'Keywords' ); ?>:
					</label>
				</td>
				<td>
					<textarea rows="5" cols="50" style="width:500px; height:50px" class="inputbox" id="metakey" name="metakey"><?php echo str_replace('&','&amp;',$article->metakey); ?></textarea>
				</td>
			</tr>
			</table>

		<!-- End Article Parameters Section -->
		<?php
		$tabs->endTab();
		$tabs->endPane();
		?>

		<input type="hidden" name="images" value="" />
		<input type="hidden" name="option" value="com_content" />
		<input type="hidden" name="Returnid" value="<?php echo $Returnid; ?>" />
		<input type="hidden" name="id" value="<?php echo $article->id; ?>" />
		<input type="hidden" name="version" value="<?php echo $article->version; ?>" />
		<input type="hidden" name="sectionid" value="<?php echo $article->sectionid; ?>" />
		<input type="hidden" name="created_by" value="<?php echo $article->created_by; ?>" />
		<input type="hidden" name="referer" value="<?php echo ampReplace( @$_SERVER['HTTP_REFERER'] ); ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	function _buildEditLists()
	{
		// Get the article and database connector from the model
		$article = & $this->get('Article');
		$db 	 = & $this->get('DBO');

		// Read the JPATH_ROOT/images/stories/ folder
		$pathA = 'images/stories';
		$pathL = 'images/stories';
		$images = array ();
		$folders = array ();
		$folders[] = mosHTML::makeOption('/');
		mosAdminMenus::ReadImages($pathA, '/', $folders, $images);

		// Select List: Subfolders in the JPATH_ROOT/images/stories/ folder
		$lists['folders'] = mosAdminMenus::GetImageFolders($folders, $pathL);

		// Select List: Images in the JPATH_ROOT/images/stories/ folder
		$lists['imagefiles'] = mosAdminMenus::GetImages($images, $pathL);

		// Select List: Saved Images
		if (trim($article->images))
		{
			$article->images = explode("\n", $article->images);
		} else
		{
			$article->images = array ();
		}
		$lists['imagelist'] = mosAdminMenus::GetSavedImages($article, $pathL);

		// Images Array: Images
		$lists['images'] = $images;

		// Select List: Image Positions
		$lists['_align'] = mosAdminMenus::Positions('_align');

		// Select List: Image Caption Alignment
		$lists['_caption_align'] = mosAdminMenus::Positions('_caption_align');

		// Select List: Image Caption Position
		$pos[] = mosHTML::makeOption('bottom', JText::_('Bottom'));
		$pos[] = mosHTML::makeOption('top', JText::_('Top'));
		$lists['_caption_position'] = mosHTML::selectList($pos, '_caption_position', 'class="inputbox" size="1"', 'value', 'text');

		// Select List: Categories
		$lists['catid'] = mosAdminMenus::ComponentCategory('catid', $article->sectionid, intval($article->catid));

		// Select List: Category Ordering
		$query = "SELECT ordering AS value, title AS text"."\n FROM #__content"."\n WHERE catid = $article->catid"."\n ORDER BY ordering";
		$lists['ordering'] = mosAdminMenus::SpecificOrdering($article, $article->id, $query, 1);

		// Radio Buttons: Should the article be published
		$lists['state'] = mosHTML::yesnoradioList('state', '', $article->state);

		// Radio Buttons: Should the article be added to the frontpage
		$query = "SELECT content_id"."\n FROM #__content_frontpage"."\n WHERE content_id = $article->id";
		$db->setQuery($query);
		$article->frontpage = $db->loadResult();

		$lists['frontpage'] = mosHTML::yesnoradioList('frontpage', '', (boolean) $article->frontpage);

		// Select List: Group Access
		$lists['access'] = mosAdminMenus::Access($article);

		return $lists;
	}
}
?>