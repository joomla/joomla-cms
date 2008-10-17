/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * JXStandard javascript behavior
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @since		1.5
 * @version     1.0
 */
var JXStandard = new Class({

	instances : null,		

	initialize: function()
	{
	 	this.instances = $ES('object[type=application/x-xstandard]');
	
	 	var self = this;
	 	document.adminForm.onsubmit = function() {
	 		self.save();
		 }
	},

	save: function() 
	{
		this.instances.each(function(instance)
		{
			instance.EscapeUnicode = false;
			var contents = instance.value;
			$(instance.className).value = contents;
		});
	}
})

document.xstandard = null
window.addEvent('domready', function(){
  var xstandard = new JXStandard();
  document.xstandard = xstandard;
});