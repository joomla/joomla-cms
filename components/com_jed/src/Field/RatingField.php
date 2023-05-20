<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\Field\ListField;

/**
// https://www.cssscript.com/star-rating-component-javascript-rater/
// https://github.com/fredolss/rater-js

/*
Rating star script methods
// disable
myRating.disable();

// enable
myRating.enable();

// set the rating value
myRating.setRating(rating:number);

// get the rating value
myRating.getRating();

// clear the rating
myRating.clear();

// removes event handlers
myRating.dispose();

// gets the element
myRating.element();

 *
 * @since  4.0.0
 */
class RatingField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  4.0.0
     */
    protected string $type = 'rating';

    protected string $layout = 'joomla.form.field.list-fancy-select';


    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   4.0.0
     * @throws Exception
     */
    protected function getInput($options = []): string
    {
        HTMLHelper::_('jquery.framework');
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->getRegistry()
            ->addExtensionRegistryFile('com_jed');
        $wa->useScript('com_jed.reviewForm-rateJS')
        //$wa->useScript('com_jed.rater-pure-js')
            ->useScript('com_jed.reviewForm-ratingFieldJS')
            //  ->useScript('com_jed.rating-pure-field-js')
            ->useStyle('com_jed.raterJSstyle');

        //  $value = $this->prepareInputValue();
        $value = 0;
        $value = $value == -1 ? 0 : $value;

        $attr = '';

        // Initialize some field attributes.
        $attr .= ' class="review-rating-star ' . $this->class . '"';
        $attr .= $this->required ? ' required aria-required="true"' : '';
        $attr .= $this->autofocus ? ' autofocus' : '';
        $idname = str_replace("[", "_", $this->name);
        $idname = str_replace("]", "", $idname);

        return "
			<div class='rating-selector'>
				<input type='hidden'  id='" . $idname . "' name='" . $this->name . "' value='" . $value . "' />
				<input class='rating-stars-empty' type='hidden' name='" . $this->name . "___is_empty' value='1' data-role='is-empty'>
				<a class='clear-rating' title='Clear' href='#' data-role='clear-rating'>
					<span class='fa fa-minus-circle'></span>
				</a>
				<span class='rating-stars'></span><span class='live-rating'></span>
				<div class='no-score muted textInfo'><small>" . Text::_('Not Scored') . "</small></div>
			</div>
		";
    }
}
