<?php
class ContentArchiveViewCommon
{
	/**
	 * Helper method to print the content item's title block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @param string $linkOn 	Menu link for the content item
	 * @param object $access 	Access object for the content item
	 * @return void
	 * @since 1.0
	 */
	function title($row, $params, $linkOn, $access)
	{
		if ($params->get('item_title')) {
			?>
			<td class="contentheading<?php echo $params->get( 'pageclass_sfx' ); ?>" width="100%">
				<?php
				if ($params->get('link_titles') && $linkOn != '') {
					?>
					<a href="<?php echo $linkOn;?>" class="contentpagetitle<?php echo $params->get( 'pageclass_sfx' ); ?>">
						<?php echo $row->title;?></a>
					<?php
				} else {
					echo $row->title;
				}
				?>
			</td>
			<?php
		}
	}

	/**
	 * Helper method to print a container for a category and section blocks
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The item to display
	 * @param object $params 	The item to display's parameters object
	 * @return void
	 * @since 1.0
	 */
	function sectionCategory($row, $params)
	{
		if (($params->get('section') && $row->sectionid) || ($params->get('category') && $row->catid)) {
			?>
			<tr>
				<td>
				<?php
		}

		// displays Section Name
		JContentHTMLHelper::section($row, $params);

		// displays Section Name
		JContentHTMLHelper::category($row, $params);

		if (($params->get('section') && $row->sectionid) || ($params->get('category') && $row->catid)) {
				?>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Helper method to print the section block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The section item
	 * @param object $params 	The section item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function section($row, $params)
	{
		if ($params->get('section') && $row->sectionid) {
			?>
			<span>
				<?php
				echo $row->section;
				// writes dash between section & Category Name when both are active
				if ($params->get('category')) {
					echo ' - ';
				}
				?>
			</span>
			<?php
		}
	}

	/**
	 * Helper method to print the category block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The category item
	 * @param object $params 	The category's parameters object
	 * @return void
	 * @since 1.0
	 */
	function category($row, $params)
	{
		if ($params->get('category') && $row->catid) {
			?>
			<span>
				<?php
				echo $row->category;
				?>
			</span>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's author block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function author($row, $params)
	{
		if (($params->get('author')) && ($row->author != "")) {
			?>
			<tr>
				<td width="70%"  valign="top" colspan="2">
					<span class="small">
					<?php JText::printf( 'Written by', ($row->created_by_alias ? $row->created_by_alias : $row->author) ); ?>
					</span>
					&nbsp;&nbsp;
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's URL block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function url($row, $params)
	{
		if ($params->get('url') && $row->urls) 	{
			?>
			<tr>
				<td valign="top" colspan="2">
					<a href="http://<?php echo $row->urls ; ?>" target="_blank">
						<?php echo $row->urls; ?></a>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's created date block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function createDate($row, $params)
	{
		$create_date = null;
		if (intval($row->created) != 0) {
			$create_date = mosFormatDate($row->created);
		}
		if ($params->get('createdate')) {
			?>
			<tr>
				<td valign="top" colspan="2" class="createdate">
					<?php echo $create_date; ?>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's modified date block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function modifiedDate($row, $params)
	{
		$mod_date = null;
		if (intval($row->modified) != 0) {
			$mod_date = mosFormatDate($row->modified);
		}
		if (($mod_date != '') && $params->get('modifydate')) {
			?>
			<tr>
				<td colspan="2"  class="modifydate">
					<?php echo JText::_( 'Last Updated' ); ?> ( <?php echo $mod_date; ?> )
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's table of contents block if
	 * present.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row The content item
	 * @return void
	 * @since 1.0
	 */
	function toc($row)
	{
		if (isset ($row->toc)) {
			echo $row->toc;
		}
	}

	/**
	 * Helper method to print the content item's read more button if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param string $linkOn 	Button link for the read more button
	 * @param string $linkText 	Text for read more button
	 * @return void
	 * @since 1.0
	 */
	function readMore($params, $linkOn, $linkText)
	{
		if ($params->get('readmore')) {
			if ($params->get('intro_only') && $linkText) {
				?>
				<tr>
					<td  colspan="2">
						<a href="<?php echo $linkOn;?>" class="readon<?php echo $params->get( 'pageclass_sfx' ); ?>">
							<?php echo $linkText;?></a>
					</td>
				</tr>
				<?php
			}
		}
	}
}
?>
