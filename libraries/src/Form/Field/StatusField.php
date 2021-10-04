<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Language\Text;

\defined('JPATH_PLATFORM') or die;

/**
 * Form Field to load a list of states
 *
 * @since  3.2
 */
class StatusField extends ListField
{
	/**
	 * Available statuses with default labels.
	 *
	 * @var  array
	 * @since  __DEPLOY_VERSION__
	 */
	private const DEFAULT_OPTIONS = [
		-2  => 'JTRASHED',
		0   => 'JUNPUBLISHED',
		1   => 'JPUBLISHED',
		2   => 'JARCHIVED',
		'*' => 'JALL',
	];

	/**
	 * Available statuses with alternative labels, i.e., JDISABLED and JENABLED instead of JUNPUBLISHED and JPUBLISHED.
	 *
	 * @var array
	 * @since __DEPLOY_VERSION__
	 */
	private const ALT_OPTIONS = [
		-2  => 'JTRASHED',
		0   => 'JDISABLED',
		1   => 'JENABLED',
		2   => 'JARCHIVED',
		'*' => 'JALL',
	];

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $type = 'Status';

	/**
	 * @var  boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $altLabels;

	/**
	 * Override parent setup to set $this->altLabels
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form
	 *                                       field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as as an array container for
	 *                                       the field. For example, if the field has `name="foo"` and the group value is
	 *                                       set to "bar" then the full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null): bool
	{
		$parentResult = parent::setup($element, $value, $group);

		$this->altLabels = ((string) $element['alt_labels'] ?? '') === 'true';

		return $parentResult;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions(): array
	{
		return array_map(
			static function (string $languageConstant) {
				return Text::_($languageConstant);
			},
			$this->altLabels ? self::ALT_OPTIONS : self::DEFAULT_OPTIONS
		);
	}
}
