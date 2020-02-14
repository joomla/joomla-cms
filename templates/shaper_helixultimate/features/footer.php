<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

class HelixUltimateFeatureFooter
{
	private $params;

	public function __construct($params)
	{
		$this->params = $params;
		$this->position = $this->params->get('copyright_position');
		$this->load_pos = $this->params->get('copyright_load_pos');
	}

	public function renderFeature()
	{
		if($this->params->get('enabled_copyright'))
		{
			$output = '';
			
			if($this->params->get('copyright'))
			{
				$output .= '<span class="sp-copyright">' . str_ireplace('{year}', date('Y'),  str_ireplace('joomla templates', '<a target="_blank" href="https://www.joomshaper.com/joomla-templates">Joomla Templates</a>', str_ireplace('joomshaper', '<a target="_blank" href="https://www.joomshaper.com">JoomShaper</a>',  $this->params->get('copyright'))))  . '</span>';
			}

			return $output;
		}
	}
}
