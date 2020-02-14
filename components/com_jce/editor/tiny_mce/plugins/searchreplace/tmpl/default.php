<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('WF_EDITOR') or die('RESTRICTED');

?>
<form onsubmit="return false;" action="#">
	<div>
		<div class="uk-form-row">
			<label class="uk-form-label uk-width-3-10" for="search_string"><?php echo JText::_('WF_SEARCHREPLACE_FINDWHAT'); ?></label>
			<div class="uk-form-controls uk-width-7-10">
				<input type="text" id="search_string" />
			</div>
		</div>
		<div class="uk-form-row">
			<label class="uk-form-label uk-width-3-10" for="replace_string"><?php echo JText::_('WF_SEARCHREPLACE_REPLACEWITH'); ?></label>
			<div class="uk-form-controls uk-width-7-10">
				<input type="text" id="replace_string" />
			</div>
		</div>
		<div class="uk-form-row">
			<div class="uk-width-5-10">
				<input id="matchcase" type="checkbox" />
				<label for="matchcase"><?php echo JText::_('WF_SEARCHREPLACE_MCASE'); ?></label>
			</div>
			<div class="uk-width-5-10">
				<input id="wholewords" type="checkbox" />
				<label for="wholewords"><?php echo JText::_('WF_SEARCHREPLACE_WHOLEWORDS', 'Whole Words'); ?></label>
			</div>
		</div>
	</div>
	<div class="mceActionPanel">
		<div class="uk-float-left uk-width-4-5 uk-flex uk-flex-wrap">
			<button type="submit" class="uk-button uk-button-primary uk-margin-small-bottom uk-margin-small-right" id="find"><i class="uk-icon-search uk-margin-small-right"></i><?php echo JText::_('WF_SEARCHREPLACE_FIND', 'Find'); ?></button>
			<button type="button" class="uk-button uk-button-danger uk-margin-small-bottom uk-margin-small-right" id="replace" disabled><i class="uk-icon-exchange uk-margin-small-right"></i><?php echo JText::_('WF_SEARCHREPLACE_REPLACE', 'Replace'); ?></button>
			<button type="button" class="uk-button uk-button-danger uk-margin-small-bottom uk-margin-small-right" id="replaceAll" disabled><i class="uk-icon-loop uk-margin-small-right"></i><?php echo JText::_('WF_SEARCHREPLACE_REPLACEALL', 'Replace All'); ?></button>
			<button type="button" class="uk-button uk-button-primary uk-margin-small-bottom uk-margin-small-right" id="prev" disabled><i class="uk-icon-arrow-left uk-margin-small-right"></i><?php echo JText::_('WF_SEARCHREPLACE_PREV', 'Previous'); ?></button>
			<button type="button" class="uk-button uk-button-primary uk-margin-small-bottom" id="next" disabled><i class="uk-icon-arrow-right uk-margin-small-right"></i><?php echo JText::_('WF_SEARCHREPLACE_NEXT', 'Next'); ?></button>
		</div>
		<div class="uk-float-right">
			<button type="button" id="cancel" class="uk-button uk-hidden-mini"><?php echo JText::_('WF_LABEL_CANCEL'); ?></button>
		</div>
	</form>