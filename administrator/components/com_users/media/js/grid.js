/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

window.addEvent('domready', function(){
	actions = $$('a.move_up');
	actions.merge($$('a.move_down'));
	actions.merge($$('a.grid_true'));
	actions.merge($$('a.grid_false'));
	actions.merge($$('a.grid_trash'));

	actions.each(function(a){
		a.addEvent('click', function(){
			args = Json.evaluate(this.rel);
			console.log(args);
			listItemTask(args.id, args.task);
		});
	});

	$$('input.check-all-toggle').each(function(el){
		el.addEvent('click', function(){
			if (el.checked) {
				$(this.form).getElements('input[type=checkbox]').each(function(i){
					i.checked = true;
				})
			}
			else {
				$(this.form).getElements('input[type=checkbox]').each(function(i){
					i.checked = false;
				})
			}
		});
	});
});
