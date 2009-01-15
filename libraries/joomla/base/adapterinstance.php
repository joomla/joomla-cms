<?php

class JAdapterInstance extends JObject {

	/** Parent
	 * @var object */
	protected $parent = null;
	protected $db = null;


	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	object	$parent	Parent object [JAdapter instance]
	 * @return	void
	 * @since	1.5
	 */
	public function __construct(&$parent, &$db)
	{
		$this->parent =& $parent;
		$this->db =& $db ? $db : JFactory::getDBO(); // pull in the global dbo in case
	}
}
