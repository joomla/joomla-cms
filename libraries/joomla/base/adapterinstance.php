<?php

class JAdapterInstance extends JObject {

	/** Parent
	 * @var object */
	protected $parent = null;


	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	object	$parent	Parent object [JAdapter instance]
	 * @return	void
	 * @since	1.5
	 */
	public function __construct(&$parent)
	{
		$this->parent =& $parent;
	}
}
