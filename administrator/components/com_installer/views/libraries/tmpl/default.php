<?php
/**
 * Library Default view 
 * 
 * @package Joomla
 * @subpackage Installer
 * @author Sam Moffatt <pasamio@gmail.com>
 * @copyright	Copyright (C) 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *  See COPYRIGHT.php for copyright notices and details.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm">
<div id="editcell">
	<table class="adminform">
		<tbody>
			<tr>
				<td width="100%"><?php echo JText::_( 'DESCLIBRARIES' ); ?></td>
			</tr>
		</tbody>
	</table>

    <table class="adminlist">
    <thead>
        <tr>
        	<th class="title" width="10px"><?php echo JText::_( 'Num' ); ?></th>
            <th class="title">
                <?php echo JText::_( 'Library' ); ?>
            </th>
            <th class="title">
            	<?php echo JText::_( 'Version' ); ?>
            </th>
            <th class="title">
            	<?php echo JText::_( 'Author' ); ?>
            </th>
            <th class="title">
            	<?php echo JText::_( 'Packager' ); ?>
            </th>
        </tr>            
    </thead>
	<tfoot>
		<tr>
			<td colspan="6"><?php echo $this->pagination->getListFooter(); ?></td>
		</tr>
	</tfoot>
	<tbody>    
    <?php
    $k = 0;
     for ($i=0, $n=count($this->items), $rc=0; $i < $n; $i++, $rc = 1 - $rc)
    {
        $this->loadItem($i);
		echo $this->loadTemplate('item');
    }
    ?>
    </tbody>
    </table>
</div>

	<input type="hidden" name="task" value="manage" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="option" value="com_installer" />
	<input type="hidden" name="type" value="libraries" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
