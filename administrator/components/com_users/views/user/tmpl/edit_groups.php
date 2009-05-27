<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
?>

<script>
window.addEvent('domready', function(){
	$('user-groups').getElements('input').each(function(i){
		// Event to check all child groups.
		i.addEvent('check', function(e){
			// Check the child groups.
			$('user-groups').getElements('input').each(function(c){
				if (this.getProperty('rel') == c.id) {
					c.setProperty('checked', true);
					c.setProperty('disabled', true);
					c.fireEvent('check');
				}
			}.bind(this));
		}.bind(i));

		// Event to uncheck all the parent groups.
		i.addEvent('uncheck', function(e){
			// Uncheck the parent groups.
			$('user-groups').getElements('input').each(function(c){
				if (c.getProperty('rel') == this.id) {
					c.setProperty('checked', false);
					c.setProperty('disabled', false);
					c.fireEvent('uncheck');
				}
			}.bind(this));
		}.bind(i));

		// Bind to the click event to check/uncheck child/parent groups.
		i.addEvent('click', function(e){
			// Check the child groups.
			$('user-groups').getElements('input').each(function(c){
				if (this.getProperty('rel') == c.id) {
					c.setProperty('checked', true);
					if (this.getProperty('checked')) {
						c.setProperty('disabled', true);
					} else {
						c.setProperty('disabled', false);
					}
					c.fireEvent('check');
				}
			}.bind(this));

			// Uncheck the parent groups.
			$('user-groups').getElements('input').each(function(c){
				if (c.getProperty('rel') == this.id) {
					c.setProperty('checked', false);
					c.setProperty('disabled', false);
					c.fireEvent('uncheck');
				}
			}.bind(this));
		}.bind(i));

		// Initialize the widget.
		if (i.getProperty('checked')) {
			i.fireEvent('click');
		}
	});
});
</script>
<?php echo JHtml::_('access.usergroups', 'groups', array_keys($this->groups)); ?>
