<?php
/**
* @version $Id: admin.weblinks.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Joomla
 * @subpackage POlls
 */
class weblinksScreens {
	/**
	 * Static method to create the template object
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $files=null) {

		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );

		return $tmpl;
	}

	/**
	* List languages
	* @param array
	*/
	function view( &$lists ) {
		global $mosConfig_lang;

		$tmpl =& weblinksScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'view.html' );

		$tmpl->addVar( 'body2', 'search', $lists['search'] );

		// temp lists --- these can be done in pat a lot better
		$tmpl->addVar( 'body2', 'lists_state', $lists['state'] );
		$tmpl->addVar( 'body2', 'lists_catid', $lists['catid'] );

		//$tmpl->addObject( )
		$tmpl->displayParsedTemplate( 'body2' );
	}

	function editWeblinks() {
		global $mosConfig_lang;

		$tmpl =& weblinksScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'editWeblinks.html' );

		$tmpl->displayParsedTemplate( 'body2' );
	}
}

/**
* @package Joomla
* @subpackage Weblinks
*/
class HTML_weblinks {

	function showWeblinks( $option, &$rows, &$lists, &$pageNav ) {
		global $my;
 	 	global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm" id="weblinksform" class="adminform">

		<?php
		weblinksScreens::view( $lists );
		?>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="5">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" />
					</th>
					<th class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Title' ), 'a.title' ); ?>
					</th>
					<th width="25%" align="left">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Category' ), 'category' ); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Published' ), 'a.published' ); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Approved' ), 'a.approved' ); ?>
					</th>
					<th colspan="2" width="5%">
						<?php echo $_LANG->_( 'Reorder' ); ?>
					</th>
					<th width="2%" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Order' ), 'a.ordering' ); ?>
					</th>
					<th width="1%">
						<?php mosAdminHTML::saveOrderIcon( $rows ); ?>
					</th>
					<th width="5%" nowrap="nowrap" align="center">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Hits' ), 'a.hits' ); ?>
					</th>
					<th width="20" nowrap="nowrap" align="center">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'ID' ), 'a.id' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
					<tr>
						<th colspan="13" class="center">
							<?php echo $pageNav->getPagesLinks(); ?>
						</th>
					</tr>
					<tr>
						<td colspan="13" class="center">
							<?php echo $_LANG->_( 'Display Num' ) ?>
							<?php echo  $pageNav->getLimitBox() ?>
							<?php echo $pageNav->getPagesCounter() ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php
				$k = 0;
				for ($i=0, $n=count( $rows ); $i < $n; $i++) {
					$row = &$rows[$i];

					$link 	= 'index2.php?option=com_weblinks&amp;task=editA&amp;id='. $row->id;

					$task 	= $row->published ? 'unpublish' : 'publish';
					$img 	= $row->published ? 'tick.png' : 'publish_x.png';
					$alt 	= $row->published ? $_LANG->_( 'Published' ) : $_LANG->_( 'Unpublished' );
					$img1 	= $row->approved ? 'tick.png' : 'publish_x.png';
					$alt1 	= $row->published ? $_LANG->_( 'Approved' ) : $_LANG->_( 'Denied' );

					$checked 	= mosAdminHTML::checkedOutProcessing( $row, $i );

					$row->cat_link 	= 'index2.php?option=com_categories&amp;section=com_weblinks&amp;task=editA&amp;id='. $row->catid;
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td>
							<?php echo $pageNav->rowNumber( $i ); ?>
						</td>
						<td>
							<?php echo $checked; ?>
						</td>
						<td>
							<?php
							if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
								echo $row->title;
							} else {
								?>
								<a href="<?php echo $link; ?>" title="<?php echo $_LANG->_( 'Edit Weblinks' ); ?>" class="editlink">
									<?php echo $row->title; ?>
								</a>
								<?php
							}
							?>
						</td>
						<td>
							<a href="<?php echo $row->cat_link; ?>" title="<?php echo $_LANG->_( 'Edit Category' ); ?>">
								<?php echo $row->category; ?>
							</a>
						</td>
						<td align="center">
							<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')">
								<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>" />
							</a>
						</td>
						<td align="center">
							<img src="images/<?php echo $img1;?>" width="12" height="12" border="0" alt="<?php echo $alt1; ?>" />
						</td>
						<td>
							<?php
							if ( $lists['tOrder'] == 'category' && ( $lists['tOrderDir'] == 'DESC' )  ) {
								echo $pageNav->orderUpIcon( $i, ($row->catid == @$rows[$i-1]->catid) );
							}
						?>
						</td>
			 			<td>
							<?php
							if ( $lists['tOrder'] == 'category' && ( $lists['tOrderDir'] == 'DESC' )  ) {
								echo $pageNav->orderDownIcon( $i, $n, ($row->catid == @$rows[$i+1]->catid) );
							}
							?>
						</td>
						<td align="center" colspan="2">
							<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
						</td>
						<td align="center">
							<?php echo $row->hits; ?>
						</td>
						<td align="center">
							<?php echo $row->id; ?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
				}
				?>
				</tbody>
				</table>
			</fieldset>
		</div>

		<input type="hidden" name="tOrder" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrder_old" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrderDir" value="" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	/**
	* Writes the edit form for new and existing record
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param mosWeblink The weblink object
	* @param array An array of select lists
	* @param object Parameters
	* @param string The option
	*/
	function editWeblink( &$row, &$lists, &$params, $option ) {
	  	global $_LANG;

		mosMakeHtmlSafe( $row, ENT_QUOTES, 'description' );
		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if (form.title.value == ""){
				alert( "<?php echo $_LANG->_( 'Weblink item must have a title' ); ?>" );
			} else if (form.catid.value == "0"){
				alert( "<?php echo $_LANG->_( 'You must select a category' ); ?>" );
			} else if (form.url.value == ""){
				alert( "<?php echo $_LANG->_( 'You must have a url.' ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<?php
		weblinksScreens::editWeblinks( $lists );
		?>
		<form action="index2.php" method="post" name="adminForm" id="adminForm">

		<table width="100%">
		<tr>
			<td width="60%" valign="top">
				<table class="adminform">
				<tr>
					<th colspan="2">
					<?php echo $_LANG->_( 'Details' ); ?>
					</th>
				</tr>
				<tr>
					<td width="20%" align="right">
					<?php echo $_LANG->_( 'Name' ); ?>:
					</td>
					<td width="80%">
					<input class="text_area" type="text" name="title" size="50" maxlength="250" value="<?php echo $row->title;?>" />
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo $_LANG->_( 'Category' ); ?>:
					</td>
					<td>
					<?php echo $lists['catid']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo $_LANG->_( 'URL' ); ?>:
					</td>
					<td>
					<input class="text_area" type="text" name="url" value="<?php echo $row->url; ?>" size="50" maxlength="250" />
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo $_LANG->_( 'Description' ); ?>:
					</td>
					<td>
					<textarea class="text_area" cols="50" rows="5" name="description" style="width:500px"><?php echo $row->description; ?></textarea>
					</td>
				</tr>

				<tr>
					<td valign="top" align="right">
					<?php echo $_LANG->_( 'Ordering' ); ?>:
					</td>
					<td>
					<?php echo $lists['ordering']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo $_LANG->_( 'Approved' ); ?>:
					</td>
					<td>
					<?php echo $lists['approved']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo $_LANG->_( 'Published' ); ?>:
					</td>
					<td>
					<?php echo $lists['published']; ?>
					</td>
				</tr>
				</table>
			</td>
			<td width="40%" valign="top">
				<table class="adminform">
				<tr>
					<th colspan="1">
					<?php echo $_LANG->_( 'Parameters' ); ?>
					</th>
				</tr>
				<tr>
					<td>
					<?php echo $params->render( 'params', 0 );?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
		</fieldset>
		</div>

		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>