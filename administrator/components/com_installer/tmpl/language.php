<?php

/**
* @version $Id$
* @package Joomla
* @subpackage Installer
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Static class to handle language view logic
 * 
 * @author Louis Landry <louis@webimagery.net>
 * @static
 * @package Joomla
 * @subpackage Installer
 * @category Controller
 * @since 1.1
 */
class JInstallerExtensionTasks {

	/**
	* @param string The URL option
	*/
	function showInstalled() {
		global $mainframe;
		
		$option		= JRequest::getVar( 'option' );
		$filter 	= $mainframe->getUserStateFromRequest( "$option.language.filter", 'filter', '-1' );
		$limit 		= $mainframe->getUserStateFromRequest( 'limit', 'limit', $mainframe->getCfg('list_limit') );
		$limitstart = $mainframe->getUserStateFromRequest( "$option.limitstart", 'limitstart', 0 );

		$select[] 			= mosHTML :: makeOption('-1', JText :: _('All'));
		$select[] 			= mosHTML :: makeOption('0', JText :: _('Site Languages'));
		$select[] 			= mosHTML :: makeOption('1', JText :: _('Admin Languages'));
		$lists['filter'] 	= mosHTML :: selectList($select, 'filter', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $filter);

		if ($filter == '-1') {
			$client = 'all';
			// Get the site languages
			$langBDir = JLanguage::getLanguagePath(JPATH_SITE);
			$langDirs = JFolder::folders($langBDir);
			
			for ($i=0; $i < count($langDirs); $i++) {
				$lang = new stdClass();
				$lang->folder = $langDirs[$i];
				$lang->client = 0;
				$lang->baseDir = $langBDir;
				
				$languages[] = $lang;				
			}			
			// Get the admin languages
			$langBDir = JLanguage::getLanguagePath(JPATH_ADMINISTRATOR);
			$langDirs = JFolder::folders($langBDir);
			
			for ($i=0; $i < count($langDirs); $i++) {
				$lang = new stdClass();
				$lang->folder = $langDirs[$i];
				$lang->client = 1;
				$lang->baseDir = $langBDir;
				
				$languages[] = $lang;				
			}			
		} elseif ($filter == '0') {
			$client = 'site';
			$langBDir = JLanguage::getLanguagePath(JPATH_SITE);
			$langDirs = JFolder::folders($langBDir);
			
			for ($i=0; $i < count($langDirs); $i++) {
				$lang = new stdClass();
				$lang->folder = $langDirs[$i];
				$lang->client = 0;
				$lang->baseDir = $langBDir;
				
				$languages[] = $lang;				
			}			
		} elseif ($filter == '1') {
			$client = 'administrator';
			$langBDir = JLanguage::getLanguagePath(JPATH_ADMINISTRATOR);
			$langDirs = JFolder::folders($langBDir);
			
			for ($i=0; $i < count($langDirs); $i++) {
				$lang = new stdClass();
				$lang->folder = $langDirs[$i];
				$lang->client = 1;
				$lang->baseDir = $langBDir;
				
				$languages[] = $lang;				
			}			
		}
		
		$rows = array();
		$rowid = 0;
		foreach ($languages as $language) {
			$files = JFolder::files( $language->baseDir .DS. $language->folder, '^([_A-Za-z]*)\.xml$' );
			foreach ($files as $file) {
				// Read the file to see if it's a valid template XML file
				$xmlDoc =& JFactory::getXMLParser();
				$xmlDoc->resolveErrors( true );
				if (!$xmlDoc->loadXML( $language->baseDir .DS. $language->folder . DS . $file, false, true )) {
					continue;
				}
	
				$root = &$xmlDoc->documentElement;
	
				if ($root->getTagName() != 'mosinstall' && $root->getTagName() != 'install') {
						continue;
				}
				if ($root->getAttribute( "type" ) != "language") {
					continue;
				}
	
				$row 			= new StdClass();
				$row->id 		= $rowid;
				$row->client_id = $language->client;
				$row->language 	= substr($file,0,-4);
				$element 		= &$root->getElementsByPath('name', 1 );
				$row->name 		= $element->getText();
	
				$element		= &$root->getElementsByPath('creationDate', 1);
				$row->creationdate = $element ? $element->getText() : 'Unknown';
	
				$element 		= &$root->getElementsByPath('author', 1);
				$row->author 	= $element ? $element->getText() : 'Unknown';
	
				$element 		= &$root->getElementsByPath('copyright', 1);
				$row->copyright = $element ? $element->getText() : '';
	
				$element 		= &$root->getElementsByPath('authorEmail', 1);
				$row->authorEmail = $element ? $element->getText() : '';
	
				$element 		= &$root->getElementsByPath('authorUrl', 1);
				$row->authorUrl = $element ? $element->getText() : '';
	
				$element 		= &$root->getElementsByPath('version', 1);
				$row->version 	= $element ? $element->getText() : '';
	
				$lang = ($client == 'site') ? 'lang' : 'lang_'.$client;
	
				// if current than set published
				if ( $mainframe->getCfg($lang) == $row->language) {
					$row->published	= 1;
				} else {
					$row->published = 0;
				}
	
				$row->checked_out = 0;
				$row->jname = strtolower( str_replace( " ", "_", $row->name ) );
				$rows[] = $row;
				$rowid++;
			}
		}
	
		/*
		 * Take care of the pagination
		 */	
		jimport('joomla.utilities.presentation.pagination');
		$page = new JPagination( count( $rows ), $limitstart, $limit );
		$rows = array_slice( $rows, $page->limitstart, $page->limit );
	
		JInstallerScreens_language :: showInstalled($rows, $page, $client, $lists);
	}

}

/**
 * Static class to handle language view display
 * 
 * @author Louis Landry <louis@webimagery.net>
 * @static
 * @package Joomla
 * @subpackage Installer
 * @category View
 * @since 1.1
 */
class JInstallerScreens_language {
	
	function showInstalled( &$rows, &$page, $client, $lists ) {
		
		/*
		 * Load overlib
		 */
		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm">
			
		<div id="treecell">
			<?php require_once(dirname(__FILE__).DS.'tree.html'); ?>
		</div>
		
		<div id="datacell">
			<fieldset title="<?php echo JText::_('Installed Languages'); ?>">
				<legend>
					<?php echo JText::_('Installed Languages'); ?>
				</legend>
				
				<table class="adminform">
				<tr>
					<td width="100%">
						<?php echo JText::_( 'DESCLANGUAGES' ); ?>
					</td>
					<td align="right">
						<?php echo $lists['filter'];?>
					</td>
				</tr>
				</table>
		
				<div id="tablecell">				
					<?php
					if (count($rows)) {
						?>
						<table class="adminlist">
						<tr>
							<th class="title" width="2">
								<?php echo JText::_( 'Num' ); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'Language' ); ?>
							</th>
							<th width="7%" align="center">
								<?php echo JText::_( 'Client' ); ?>
							</th>
							<th width="10%" align="center">
								<?php echo JText::_( 'Version' ); ?>
							</th>
							<th width="15%" class="title">
								<?php echo JText::_( 'Date' ); ?>
							</th>
							<th width="25%"  class="title">
								<?php echo JText::_( 'Author' ); ?>
							</th>
						</tr>
						<?php
						$rc = 0;
						for ($i = 0, $n = count( $rows ); $i < $n; $i++) {
							$row =& $rows[$i];
		
							/*
							 * Handle currently used templates
							 */
							if ($row->language == $mainframe->getCfg('lang') || $row->language = $mainframe->getCfg('lang_administrator'))	{
								$cbd 	= 'disabled';
								$style 	= 'style="color:#999999;"';
							} else {
								$cbd 	= '';
								$style 	= '';
							}
							
							$author_info = @$row->authorEmail .'<br />'. @$row->authorUrl;
							?>
							<tr class="<?php echo "row$rc"; ?>" <?php echo $style; ?>>
								<td>
									<?php echo $page->rowNumber( $i ); ?>
								</td>
								<td>
									<input type="checkbox" id="cb<?php echo $i;?>" name="eid[]" value="<?php echo $row->language; ?>" onclick="isChecked(this.checked);" <?php echo $cbd; ?> />
									<span class="bold"><?php echo $row->name; ?></span>
								</td>
								<td align="center">
									<?php echo $row->client_id == 0 ? JText::_( 'Site' ) : JText::_( 'Admin' ); ?>
								</td>
								<td align="center">
									<?php echo @$row->version != '' ? $row->version : '&nbsp;'; ?>
								</td>
								<td>
									<?php echo @$row->creationdate != '' ? $row->creationdate : '&nbsp;'; ?>
								</td>
								<td>
									<span onmouseover="return overlib('<?php echo $author_info; ?>', CAPTION, '<?php echo JText::_( 'Author Information' ); ?>', BELOW, LEFT);" onmouseout="return nd();">
										<?php echo @$row->author != '' ? $row->author : '&nbsp;'; ?>										
									</span>
								</td>
							</tr>
							<?php
							$rc = $rc == 0 ? 1 : 0;
						}
						?>
						</table>
						<?php echo $page->getListFooter(); ?>		
						<?php
					} else {
						echo JText::_( 'No Languages installed' ); 
					}
					?>							
				</div>						
			</fieldset>
		</div>
		
		<input type="hidden" name="option" value="com_installer" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="extension" value="language" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="client" value="<?php echo $client;?>" />
		</form>
		<?php
	}
}
?>