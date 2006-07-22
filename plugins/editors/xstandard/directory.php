<?php
/*************************************************************************************
** - Copyright (c) 2006 Belus Technology Inc.
** -
** - By using the software and documentation, the user expressly agrees that
** - the use of the software documentation is at its sole risk. The software
** - and documentation is made available on an "as is" basis. Copyright owner
** - does not warrant that the software and documentation will meet the user's
** - requirements, or that the operation of the software will be uninterrupted
** - or error-free and does not make any warranty whatsoever regarding the
** - software and documentation, any information, services or products provided
** - through or in connection with the software and documentation, or any
** - results to be obtained through the use thereof, and hereby expressly
** - disclaims on behalf of itself and all suppliers any and all warranties,
** - including without limitation: any express or implied warranties of:
** - 1) merchantability; 2) fitness for a particular purpose; 3) effort to
** - achieve purpose; 4) quality; 5) accuracy; 6) non-infringement. Copyright
** - owner shall not be liable to the user, or to any third party, for any loss
** - of data, profits, loss of use, interruption of business, error, omission,
** - deletion, defect, delay in operation or transmission, computer virus,
** - communications line failure, theft or destruction or unauthorized access to,
** - alteration of, or use of records, whether for breach of contract, tortious
** - behavior, negligence, or under any other cause of action.
** -
** - All right, title and interest including, but not limited to, copyright and
** - other intellectual property rights in and to the software and documentation
** - are owned by Copyright owner and the use of or modification to the software
** - and documentation does not pass to the user any title to or any proprietary
** - rights in the software and documentation.
** -
** - Permission is granted to copy, modify and distribute the software and
** - documentation for any purpose and royalty-free, subject to the following:
** - copyright and other intellectual property rights in and to the software and
** - documentation must not be misrepresented and this notice may not be removed
** - from any source distribution of the software or documentation.
*************************************************************************************/

/****************************************************************************************
** - Purpose: Directory
** - Version: 1.00
** - Date: 2006-01-30
** - Documentation: http://xstandard.com/xstandard-lite-for-partner-cms/
****************************************************************************************/

function xs_xhtml_escape($text) {
	return str_replace(array("&", "<", ">", "\""), array("&amp;", "&lt;", "&gt;", "&quot;"), $text);
}

function read_from_file($path) {
	return @file_get_contents($path);
}



//Process request
$id = "";
$metadata = "";

if (isset($_SERVER["HTTP_X_CMS_DIRECTORY_ID"])) {
	$id = $_SERVER["HTTP_X_CMS_DIRECTORY_ID"];
}

if (isset($_SERVER["HTTP_X_CMS_DIRECTORY_METADATA"])) {
	$metadata = $_SERVER["HTTP_X_CMS_DIRECTORY_METADATA"];
}


// Respond
if (get_magic_quotes_runtime() != 0) {
	set_magic_quotes_runtime(0);
}

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>";
echo "<library>";
	echo "<containers>";
		// Process folders
		if ($id == "") {
			echo ("<container>");
				echo ("<label>Articles</label>");
				echo ("<id>a</id>");
				echo ("<metadata></metadata>");
				echo ("<location></location>");
				echo ("<icon></icon>");
			echo ("</container>");
			echo ("<container>");
				echo ("<label>Special Characters &amp; Symbols</label>");
				echo ("<id>c</id>");
				echo ("<metadata></metadata>");
				echo ("<location>cms-directory-xhtml-entities.xml</location>");
				echo ("<icon></icon>");
			echo ("</container>");
			/*echo ("<container>");
				echo ("<label>Placeholders</label>");
				echo ("<id>d</id>");
				echo ("<metadata></metadata>");
				echo ("<location></location>");
				echo ("<icon>flag</icon>");
			echo ("</container>");*/
		}
		echo ("<object>");
			echo ("<label>Page break placeholder</label>");
			echo ("<data>&lt;joomla:pagebreak /&gt;</data>");
			echo ("<icon>pagebreak</icon>");
		echo ("</object>");
		echo ("<object>");
			echo ("<label>Read more placeholder</label>");
			echo ("<data>&lt;joomla:readmore /&gt;</data>");
			echo ("<icon>readmore</icon>");
		echo ("</object>");
		echo ("<object>");
			echo ("<label>Image placeholder</label>");
			echo ("<data>&lt;joomla:image /&gt;</data>");
			echo ("<icon>image</icon>");
		echo ("</object>");
	echo ("</containers>");
	echo ("<objects>");

		// Process items
		if ($id == "a")
		{
			echo ("<object>");
				echo ("<label>test</label>");
				echo ("<data>test</data>");
				echo ("<icon>test</icon>");
			echo ("</object>");
		}
		else if ($id == "b")
		{
			//This is an example of how to read XHTML from a file.
			echo ("<object>");
				echo ("<label>Cordless Phone</label>");
				echo ("<data>" . xs_xhtml_escape(read_from_file("cms-directory-example-product-1.txt")) . "</data>");
				echo ("<icon></icon>");
			echo ("</object>");

			echo ("<object>");
				echo ("<label>Hand Vacuum</label>");
				echo ("<data>" . xs_xhtml_escape(read_from_file("cms-directory-example-product-2.txt")) . "</data>");
				echo ("<icon></icon>");
			echo ("</object>");

			echo ("<object>");
				echo ("<label>Toaster</label>");
				echo ("<data>" . xs_xhtml_escape(read_from_file("cms-directory-example-product-3.txt")) . "</data>");
				echo ("<icon></icon>");
			echo ("</object>");

			echo ("<object>");
				echo ("<label>Indoor Grill</label>");
				echo ("<data>" . xs_xhtml_escape(read_from_file("cms-directory-example-product-4.txt")) . "</data>");
				echo ("<icon></icon>");
			echo ("</object>");
		}
		else if ($id == "d")
		{
			//This is an example of how create XHTML on the fly.
			echo ("<object>");
				echo ("<label>Temperature</label>");
				echo ("<data>&lt;p&gt;The current temperature in Vancouver is &lt;temperature location=&quot;Vancouver, BC, Canada&quot; title=&quot;Placeholder for temperature.&quot;/&gt;.&lt;/p&gt;</data>");
				echo ("<icon>thermometer</icon>");
			echo ("</object>");
			echo ("<object>");
				echo ("<label>Stock Price</label>");
				echo ("<data>&lt;p&gt;The current stock price for IBM is &lt;stock symbol=&quot;IBM&quot; exchange=&quot;NYSE&quot; title=&quot;Placeholder for stock price.&quot; /&gt;.&lt;/p&gt;</data>");
				echo ("<icon>certificate</icon>");
			echo ("</object>");
		}
	echo "</objects>";
echo "</library>";
?>
