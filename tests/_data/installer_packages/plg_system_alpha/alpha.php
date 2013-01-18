<?php
/**
 * Sample System Plugin
 *
 * Primitive system plugin
 *
 * PHP5
 *
 * Created on Jul 16, 2008
 *
 * @package installer_samples
 * @license GNU/GPL http://www.gnu.org/licenses/gpl.html
 * @copyright 2008 OpenSourceMatters
 */


class plgSystemAlpha extends JPlugin {
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @since 1.5
	 */
	function plgSystemAlpha(& $subject, $config) {
		parent :: __construct($subject, $config);
	}
}
