<?php

/**
 * Class for the back-end control panel screen.
 *
 */
class GenericAdminPage extends AdminPage
{
	protected $waitForXpath =  "//button[contains(@onclick, 'option=com_help&keyref=Help')]";

}
