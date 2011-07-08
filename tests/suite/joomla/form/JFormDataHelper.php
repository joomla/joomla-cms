<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Data for JForm tests.
 *
 * @package		Joomla.UnitTest
 * @subpackage  Form
 */
class JFormDataHelper
{
	public static $bindDocument = '<form>
	<fields
		description="All the fields">
		<!-- Set up a group of fields called details. -->
		<field
			name="title" />
		<fields
			name="details"
			description="The Details Group">
			<field
				name="abstract" />
		</fields>
		<fields
			name="params"
			description="Optional Settings">
			<field
				name="show_title" />
			<field
				name="show_abstract" />
			<fieldset
				name="basic">
				<field
					name="show_author" />
			</fieldset>
			<field
				name="categories" />
		</fields>
		<field name="keywords" />
	</fields>
</form>';

	public static $filterDocument = '<form>
	<fields
		description="All the fields">
		<field
			name="default" />

		<field
			name="function" filter="jFormDataFilter" />

		<field
			name="int" filter="int" />

		<field
			name="method" filter="JFormDataHelper::filter" />

		<field
			name="raw" filter="raw" />

		<field
			name="safehtml" filter="safehtml" />

		<field
			name="server_utc" filter="server_utc" />

		<field
			name="unset" filter="unset" />

		<field
			name="user_utc" filter="user_utc" />

		<field
			name="word" filter="word" />

		<field
			name="tel" filter="tel" />

		<fields
			name="params"
			description="Optional Settings">
			<field
				name="show_title" filter="int" />
			<fieldset
				name="basic">
				<field
					name="show_author" filter="int" />
			</fieldset>
		</fields>
	</fields>
</form>';

	public static $findFieldDocument = '<form>
	<fields>
		<field
			name="title" type="text" place="root" />
		<fieldset>
			<field
				name="alias" type="text" />
		</fieldset>
		<fields
			name="params">
			<field
				name="title" place="child" type="password" />
			<fieldset
				label="Basic">
				<field
					name="show_title"
					default="1" />
			</fieldset>
			<fieldset
				label="Advanced">
				<field
					name="caching" />
			</fieldset>
		</fields>
	</fields>
</form>';

	public static $findFieldsByFieldsetDocument = '<form>
	<fields>
		<!-- Set up a group of fields called details. -->
		<fields
			name="details">
			<field
				name="title" />
			<field
				name="abstract" />
		</fields>
		<fields
			name="params">
			<field
				name="outlier" />
			<fieldset
				name="params-basic">
				<field
					name="show_title" />
				<field
					name="show_abstract" />
				<field
					name="show_author" />
			</fieldset>
			<fieldset
				name="params-advanced">
				<field
					name="module_prefix" />
				<field
					name="caching" />
			</fieldset>
		</fields>
	</fields>
</form>';

	public static $findFieldsByGroupDocument = '<form>
	<fields>
		<field
			name="id" />

		<fieldset
			name="metadata">
			<field
				name="date_created" />
			<field
				name="date_modified" />
		</fieldset>

		<!-- Set up a group of fields called details. -->
		<fields
			name="details">
			<field
				name="title"
				label="Title" />
			<field
				name="abstract" />
		</fields>

		<fields
			name="params">
			<field
				name="show_title" />
			<field
				name="show_abstract" />
			<fieldset
				name="basic">
				<field
					name="show_author" />
			</fieldset>
		</fields>

		<field
			name="language" />

		<fields
			name="level1">
			<field
				name="field1" />
			<fields
				name="level2">
				<field
					name="field2" />
			</fields>
		</fields>
	</fields>
</form>';

	public static $findGroupDocument = '<form>
	<fields>
		<field
			name="title" type="text" place="root" />
		<fieldset>
			<field
				name="alias" type="text" />
		</fieldset>
		<fields
			name="params">
			<field
				name="title" place="child" type="password" />
			<fieldset
				label="Basic">
				<field
					name="show_title" />
			</fieldset>
			<fieldset
				label="Advanced">
				<fields
					name="cache">
					<field
						name="enabled" />
					<field
						name="lifetime" />
				</fields>
			</fieldset>
		</fields>
	</fields>
</form>';

	public static $getFieldDocument = '<form>
	<fields>
		<field
			name="title"
			type="text"
			description="The title." />
		<fields
			name="params">
			<field
				name="show_title"
				type="text"
				default="1" />
		</fields>
	</fields>
</form>';

	public static $getFieldsetDocument = '<form>
	<fields>
		<!-- Set up a group of fields called details. -->
		<fields
			name="details">
			<field
				name="title" fieldset="params-basic" />
			<field
				name="abstract" />
		</fields>
		<fields
			name="params">
			<field
				name="outlier" />
			<fieldset
				name="params-basic">
				<field
					name="show_title" />
				<field
					name="show_abstract" />
				<field
					name="show_author" />
			</fieldset>
			<fieldset
				name="params-advanced">
				<field
					name="module_prefix" />
				<field
					name="caching" />
			</fieldset>
		</fields>
	</fields>
</form>';

	public static $getFieldsetsDocument = '<form>
	<fields>
		<!-- Set up a group of fields called details. -->
		<fields
			name="details">
			<field
				name="title" fieldset="params-legacy" />
			<field
				name="abstract" />
		</fields>
		<fields
			name="params">
			<field
				name="outlier" fieldset="params-legacy" />
			<fieldset
				name="params-basic">
				<field
					name="show_title" />
				<field
					name="show_abstract" />
				<field
					name="show_author" />
			</fieldset>
			<fieldset
				name="params-advanced"
				label="Advanced Options"
				description="The advanced options">
				<field
					name="module_prefix" />
				<field
					name="caching" />
			</fieldset>
		</fields>
	</fields>
</form>';

	public static $loadDocument = '<form>
	<fields>
		<field
			name="title" />

		<field
			name="abstract" />

		<fields
			name="params">
			<field
				name="show_title"
				type="radio">
				<option value="1">JYes</option>
				<option value="0">JNo</option>
			</field>
		</fields>
	</fields>
</form>';

	public static $loadFieldDocument = '<form>
	<fields>
		<field
			name="id"
			type="hidden" />

		<field
			name="created_date"
			type="text"
			hidden="true" />

		<field
			name="title"
			type="text"
			id="title-id"
			class="inputbox"
			required="true"
			validate="none"
			label="Title"
			description="The title." />

		<fields
			name="params">
			<field
				name="show_title"
				type="radio">
				<option value="1">JYes</option>
				<option value="0">JNo</option>
			</field>
			<field
				name="colours"
				type="list"
				multiple="true">
				<option value="red">Red</option>
				<option value="blue">Blue</option>
				<option value="green">Green</option>
				<option value="yellow">Yellow</option>
			</field>
		</fields>

		<field
			type="spacer"
			label="Title"
			description="The title." />

		<field
			name="translate_default"
			default="DEFAULT_KEY"
			translate_default="true"
			type="text"/>
	</fields>
</form>';

	public static $loadMergeDocument = '<form>
	<fields>
		<field
			name="published"
			type="list">
			<option
				value="1">JYES</option>
			<option
				value="0">JNO</option>
		</field>

		<field
			name="abstract"
			label="Abstract" />

		<fields
			label="A general group">
			<field
				name="access" />
			<field
				name="ordering" />
		</fields>

		<fields
			name="params">
			<field
				name="show_abstract"
				type="radio">
				<option value="1">JYes</option>
				<option value="0">JNo</option>
			</field>
		</fields>

		<fieldset>
			<field
				name="language"
				type="text"/>
		</fieldset>
	</fields>
</form>';

	public static $loadXPathDocument = '<extension>
	<fields>
		<!-- Set up a group of fields called details. -->
		<fields
			name="details">
			<field
				name="title" />
			<field
				name="abstract" />
		</fields>
		<fields
			name="params">
			<field
				name="outlier" />
			<fieldset
				name="params-basic">
				<field
					name="show_title" />
				<field
					name="show_abstract" />
				<field
					name="show_author" />
			</fieldset>
			<fieldset
				name="params-advanced">
				<field
					name="module_prefix" />
				<field
					name="caching" />
			</fieldset>
		</fields>
	</fields>
</extension>';

	public static $loadBeforeXpathResetDocument = '<form>
	<fields>
		<field
			name="title" />

		<field
			name="abstract" />

		<fields
			name="params">
			<field
				name="show_title"
				type="radio">
				<option value="1">JYes</option>
				<option value="0">JNo</option>
			</field>
		</fields>
	</fields>
</form>';

	public static $syncPathsDocument = '<form>
	<fields name="foo" addfieldpath="/field2" addformpath="form2" addrulepath="/rule2">
		<fieldset name="bar" addfieldpath="/field3" addformpath="/form3" addrulepath="rule3">
			<field name="hum" addfieldpath="field1" addformpath="/form1" addrulepath="/rule1" />
		</fieldset>
	</fields>
</form>';

	public static $validateDocument = '<form>
	<fields
		description="All the fields">
		<field
			name="boolean"
			validate="boolean" />

		<field
			name="optional" />

		<field
			name="required"
			required="true" />

		<fields
			name="group">

			<field
				name="level1"
				required="true" />

		</fields>
	</fields>
</form>';

	public static $validateFieldDocument = '<form>
	<fields
		description="All the fields">
		<field
			name="boolean"
			validate="boolean" />

		<field
			name="missingrule"
			validate="missingrule" />

		<field
			name="optional" />

		<field
			name="required"
			required="true" />
	</fields>
</form>';

	public function filter($value)
	{
		return 'method';
	}
}

/**
 * @package		Joomla.UnitTest
 * @subpackage  Form
 */
function jFormDataFilter($value)
{
	return 'function';
}
