<?php
/**
* @version $Id: admin.newsfeeds.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Newsfeeds
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
 * @subpackage News Feeds
 */
class newsfeedsScreens {
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

		$tmpl = newsfeedsScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'view.html' );

		$tmpl->addVar( 'body2', 'search', $lists['search'] );

		// temp lists --- these can be done in pat a lot better
		$tmpl->addVar( 'body2', 'lists_state', $lists['state'] );
		$tmpl->addVar( 'body2', 'lists_catid', $lists['catid'] );

		//$tmpl->addObject( )
		$tmpl->displayParsedTemplate( 'body2' );
	}

	function editFeeds() {
		global $mosConfig_lang;

		$tmpl = newsfeedsScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'editFeeds.html' );

		//$tmpl->addObject( )
		$tmpl->displayParsedTemplate( 'body2' );
	}
}

/**
* @package Joomla
* @subpackage Newsfeeds
*/
class HTML_newsfeeds {

	function showNewsFeeds( &$rows, &$lists, $pageNav, $option ) {
		global $my;
  		global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm" id="newsfeedsform" class="adminform">

		<?php
		newsfeedsScreens::view( $lists );
		?>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="10">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="10">
						<input type="checkbox" name="toggle" value="" />
					</th>
					<th class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'News Feed' ), 'a.name' ); ?>
					</th>
					<th class="title" width="20%">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Category' ), 'category' ); ?>
					</th>
					<th width="50" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Published' ), 'a.published' ); ?>
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
					<th width="5%" nowrap="nowrap">
						<?php echo $_LANG->_( 'Num Articles' ); ?>
					</th>
					<th width="70">
						<?php echo $_LANG->_( 'Cache time' ); ?>
					</th>
					<th width="30" align="center" nowrap="nowrap">
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

					$link 	= 'index2.php?option=com_newsfeeds&amp;task=editA&amp;id='. $row->id;

					$img 	= $row->published ? 'tick.png' : 'publish_x.png';
					$task 	= $row->published ? 'unpublish' : 'publish';
					$alt 	= $row->published ? $_LANG->_( 'Published' ) : $_LANG->_( 'Unpublished' );

					$checked 	= mosAdminHTML::checkedOutProcessing( $row, $i );

					$row->cat_link 	= 'index2.php?option=com_categories&amp;section=com_newsfeeds&amp;task=editA&amp;id='. $row->catid;
					?>
					<tr class="<?php echo 'row'. $k; ?>">
						<td align="center" width="10">
							<?php echo $pageNav->rowNumber( $i ); ?>
						</td>
						<td width="10">
							<?php echo $checked; ?>
						</td>
						<td>
						<?php
						if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
							?>
							<?php echo $row->name; ?>
							[ <i><?php echo $_LANG->_( 'Checked Out' ); ?></i> ]
							<?php
						} else {
							?>
							<a href="<?php echo $link; ?>" title="<?php echo $_LANG->_( 'Edit Newsfeed' ); ?>" class="editlink">
								<?php echo $row->name; ?>
							</a>
							<?php
						}
						?>
						</td>
						<td>
							<a href="<?php echo $row->cat_link; ?>" title="<?php echo $_LANG->_( 'Edit Category' ); ?>" class="editlink">
								<?php echo $row->catname;?>
							</a>
						</td>
						<td align="center">
							<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')">
								<img src="images/<?php echo $img;?>" border="0" alt="<?php echo $alt; ?>" />
							</a>
						</td>
						<td align="center">
							<?php
							if ( $lists['tOrder'] == 'category' && ( $lists['tOrderDir'] == 'DESC' )  ) {
								echo $pageNav->orderUpIcon( $i );
							}
							?>
						</td>
						<td align="center">
							<?php
							if ( $lists['tOrder'] == 'category' && ( $lists['tOrderDir'] == 'DESC' )  ) {
								echo $pageNav->orderDownIcon( $i, $n );
							}
							?>
						</td>
						<td align="center" colspan="2">
							<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
						</td>
						<td align="center">
							<?php echo $row->numarticles;?>
						</td>
						<td align="center">
							<?php echo $row->cache_time;?>
						</td>
						<td align="center">
							<?php echo $row->id;?>
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


	function editNewsFeed( &$row, &$lists, $option ) {
  		global $_LANG;

		mosMakeHtmlSafe( $row, ENT_QUOTES );
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if (form.name.value == '') {
				alert( "<?php echo $_LANG->_( 'Please fill in the newsfeed name.' ); ?>" );
			} else if (form.catid.value == 0) {
				alert( "<?php echo $_LANG->_( 'Please select a Category.' ); ?>" );
			} else if (form.link.value == '') {
				alert( "<?php echo $_LANG->_( 'Please fill in the newsfeed link.' ); ?>" );
			} else if (getSelectedValue('adminForm','catid') < 0) {
				alert( "<?php echo $_LANG->_( 'Please select a category.' ); ?>" );
			} else if (form.numarticles.value == "" || form.numarticles.value == 0) {
				alert( "<?php echo $_LANG->_( 'VALIDFILLNUMBERARTICLESDISPLAY' ); ?>" );
			} else if (form.cache_time.value == "" || form.cache_time.value == 0) {
				alert( "<?php echo $_LANG->_( 'Please fill in the cache refresh time.' ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">
		<?php
		newsfeedsScreens::editFeeds();
		?>
		<div align="center" style="width: 100%">
			<table class="adminform" id="editpage">
			<thead>
			<tr>
				<th colspan="2">
					<?php echo $_LANG->_( 'Details' ); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th colspan="2">
				</th>
			</tr>
			</tfoot>

			<tr>
				<td>
					<?php echo $_LANG->_( 'Name' ); ?>
				</td>
				<td>
					<input class="inputbox" type="text" size="40" name="name" value="<?php echo $row->name; ?>">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $_LANG->_( 'Category' ); ?>
				</td>
				<td>
					<?php echo $lists['category']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $_LANG->_( 'Link' ); ?>
				</td>
				<td>
					<input class="inputbox" type="text" size="60" name="link" value="<?php echo $row->link; ?>">
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
			<tr>
				<td>
					<?php echo $_LANG->_( 'Number of Articles' ); ?>
				</td>
				<td>
					<input class="inputbox" type="text" size="3" name="numarticles" value="<?php echo $row->numarticles; ?>">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $_LANG->_( 'Cache time (in seconds)' ); ?>
				</td>
				<td>
					<input class="inputbox" type="text" size="6" name="cache_time" value="<?php echo $row->cache_time; ?>">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $_LANG->_( 'Ordering' ); ?>
				</td>
				<td>
					<?php echo $lists['ordering']; ?>
				</td>
			</tr>
			</table>
			</fieldset>
		</div>

		<input type="hidden" name="referer" value="<?php echo @$_SERVER['HTTP_REFERER']; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>">
		<input type="hidden" name="option" value="<?php echo $option; ?>">
		<input type="hidden" name="task" value="">
		</form>
	<?php
	}
}
?>