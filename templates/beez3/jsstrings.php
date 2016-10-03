<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.beez3
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

JText::script('TPL_BEEZ3_ALTOPEN');
JText::script('TPL_BEEZ3_ALTCLOSE');
JText::script('TPL_BEEZ3_TEXTRIGHTOPEN');
JText::script('TPL_BEEZ3_TEXTRIGHTCLOSE');
JText::script('TPL_BEEZ3_FONTSIZE');
JText::script('TPL_BEEZ3_BIGGER');
JText::script('TPL_BEEZ3_RESET');
JText::script('TPL_BEEZ3_SMALLER');
JText::script('TPL_BEEZ3_INCREASE_SIZE');
JText::script('TPL_BEEZ3_REVERT_STYLES_TO_DEFAULT');
JText::script('TPL_BEEZ3_DECREASE_SIZE');
JText::script('TPL_BEEZ3_OPENMENU');
JText::script('TPL_BEEZ3_CLOSEMENU');


$jsSVBig         = (int) $this->params->get('wrapperLarge');
$jsSVSmall       = (int) $this->params->get('wrapperSmall');
$jsSVTemplateUrl = $this->baseurl . '/templates/' . $this->template;
$jsSVRightOpen   = JText::_('TPL_BEEZ3_TEXTRIGHTOPEN', true);
$jsSVRightClose  = JText::_('TPL_BEEZ3_TEXTRIGHTCLOSE', true);
$jsSVAltOpen     = JText::_('TPL_BEEZ3_ALTOPEN', true);
$jsSVAltClose    = JText::_('TPL_BEEZ3_ALTCLOSE', true);


$this->addScriptDeclaration(
/** @lang JavaScript */
	<<<JS
	var big        = '$jsSVBig%';
	var small      = '$jsSVSmall%';
	var bildauf    = '$jsSVTemplateUrl/images/plus.png';
	var bildzu     = '$jsSVTemplateUrl/images/minus.png';
	var rightopen  = '$jsSVRightOpen';
	var rightclose = '$jsSVRightClose';
	var altopen    = '$jsSVAltOpen';
	var altclose   = '$jsSVAltClose';
JS
);

unset($jsSVBig, $jsSVSmall, $jsSVTemplateUrl, $jsSVRightOpen, $jsSVRightClose, $jsSVAltOpen, $jsSVAltClose);
