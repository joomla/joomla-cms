<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/** common */
DEFINE('_LANGUAGE','en');
DEFINE('_NOT_AUTH','You are not authorized to view this resource.');
DEFINE('_DO_LOGIN','You need to login.');
DEFINE('_VALID_AZ09',"Please enter a valid %s.  No spaces, more than %d characters and contain 0-9,a-z,A-Z");
DEFINE('_CMN_YES','Yes');
DEFINE('_CMN_NO','No');
DEFINE('_CMN_SHOW','Show');
DEFINE('_CMN_HIDE','Hide');

DEFINE('_CMN_NAME','Name');
DEFINE('_CMN_DESCRIPTION','Description');
DEFINE('_CMN_SAVE','Save');
DEFINE('_CMN_CANCEL','Cancel');
DEFINE('_CMN_PRINT','Print');
DEFINE('_CMN_PDF','PDF');
DEFINE('_CMN_EMAIL','E-mail');
DEFINE('_ICON_SEP','|');
DEFINE('_CMN_PARENT','Parent');
DEFINE('_CMN_ORDERING','Ordering');
DEFINE('_CMN_ACCESS','Access Level');
DEFINE('_CMN_SELECT','Select');

DEFINE('_CMN_NEXT','Next');
DEFINE('_CMN_NEXT_ARROW'," &gt;&gt;");
DEFINE('_CMN_PREV','Prev');
DEFINE('_CMN_PREV_ARROW',"&lt;&lt; ");

DEFINE('_CMN_SORT_NONE','No Sorting');
DEFINE('_CMN_SORT_ASC','Sort Ascending');
DEFINE('_CMN_SORT_DESC','Sort Descending');

DEFINE('_CMN_NEW','New');
DEFINE('_CMN_NONE','None');
DEFINE('_CMN_LEFT','Left');
DEFINE('_CMN_RIGHT','Right');
DEFINE('_CMN_CENTER','Center');
DEFINE('_CMN_ARCHIVE','Archive');
DEFINE('_CMN_UNARCHIVE','Unarchive');
DEFINE('_CMN_TOP','Top');
DEFINE('_CMN_BOTTOM','Bottom');

DEFINE('_CMN_PUBLISHED','Published');
DEFINE('_CMN_UNPUBLISHED','Unpublished');

DEFINE('_CMN_EDIT_HTML','Edit HTML');
DEFINE('_CMN_EDIT_CSS','Edit CSS');

DEFINE('_CMN_DELETE','Delete');

DEFINE('_CMN_FOLDER','Folder');
DEFINE('_CMN_SUBFOLDER','Sub-folder');
DEFINE('_CMN_OPTIONAL','Optional');
DEFINE('_CMN_REQUIRED','Required');

DEFINE('_CMN_CONTINUE','Continue');

DEFINE('_CMN_NEW_ITEM_LAST','New items default to the last place. Ordering can be changed after this item is saved.');
DEFINE('_CMN_NEW_ITEM_FIRST','New items default to the first place. Ordering can be changed after this item is saved.');
DEFINE('_LOGIN_INCOMPLETE','Please complete the username and password fields.');
DEFINE('_LOGIN_BLOCKED','Your login has been blocked. Please contact the administrator.');
DEFINE('_LOGIN_INCORRECT','Incorrect username or password. Please try again.');
DEFINE('_LOGIN_NOADMINS','You cannot login. There are no administrators set up.');
DEFINE('_CMN_JAVASCRIPT','!Warning! Javascript must be enabled for proper operation.');

DEFINE('_NEW_MESSAGE','A new private message has arrived');
DEFINE('_MESSAGE_FAILED','The user has locked their mailbox. Message failed.');

DEFINE('_CMN_IFRAMES', 'This option will not work correctly.  Unfortunately, your browser does not support Inline Frames');

DEFINE('_INSTALL_WARN','For your security please completely remove the installation directory including all files and sub-folders  - then refresh this page');
DEFINE('_TEMPLATE_WARN','<font color=\"red\"><b>Template File Not Found! Looking for template:</b></font>');
DEFINE('_NO_PARAMS','There are no Parameters for this item');
DEFINE('_HANDLER','Handler not defined for type');

/** mambots */
DEFINE('_TOC_JUMPTO','Article Index');

/**  content */
DEFINE('_READ_MORE','Read more...');
DEFINE('_READ_MORE_REGISTER','Register to read more...');
DEFINE('_MORE','More...');
DEFINE('_ON_NEW_CONTENT', "A new content item has been submitted by [ %s ]  titled [ %s ]  from section [ %s ]  and category  [ %s ]" );
DEFINE('_SEL_CATEGORY','- Select Category -');
DEFINE('_SEL_SECTION','- Select Section -');
DEFINE('_SEL_AUTHOR','- Select Author -');
DEFINE('_SEL_POSITION','- Select Position -');
DEFINE('_SEL_TYPE','- Select Type -');
DEFINE('_EMPTY_CATEGORY','This Category is currently empty');
DEFINE('_EMPTY_BLOG','There are no items to display');
DEFINE('_NOT_EXIST','The page you are trying to access does not exist.<br />Please select a page from the main menu.');

/** classes/html/modules.php */
DEFINE('_BUTTON_VOTE','Vote');
DEFINE('_BUTTON_RESULTS','Results');
DEFINE('_USERNAME','Username');
DEFINE('_LOST_PASSWORD','Forgotten your password?');
DEFINE('_PASSWORD','Password');
DEFINE('_BUTTON_LOGIN','Login');
DEFINE('_BUTTON_LOGOUT','Logout');
DEFINE('_NO_ACCOUNT','No account yet?');
DEFINE('_CREATE_ACCOUNT','Create one');
DEFINE('_VOTE_POOR','Poor');
DEFINE('_VOTE_BEST','Best');
DEFINE('_USER_RATING','User Rating');
DEFINE('_RATE_BUTTON','Rate');
DEFINE('_REMEMBER_ME','Remember me');

/** contact.php */
DEFINE('_ENQUIRY','Enquiry');
DEFINE('_ENQUIRY_TEXT','This is an enquiry e-mail via %s from:');
DEFINE('_COPY_TEXT','This is a copy of the following message you sent to %s via %s ');
DEFINE('_COPY_SUBJECT','Copy of: ');
DEFINE('_THANK_MESSAGE','Thank you for your e-mail');
DEFINE('_CLOAKING','This email address is being protected from spam bots, you need Javascript enabled to view it');
DEFINE('_CONTACT_HEADER_NAME','Name');
DEFINE('_CONTACT_HEADER_POS','Position');
DEFINE('_CONTACT_HEADER_EMAIL','Email');
DEFINE('_CONTACT_HEADER_PHONE','Phone');
DEFINE('_CONTACT_HEADER_FAX','Fax');
DEFINE('_CONTACTS_DESC','The Contact list for this Website.');

/** classes/html/contact.php */
DEFINE('_CONTACT_TITLE','Contact');
DEFINE('_EMAIL_DESCRIPTION','Send an Email to this Contact:');
DEFINE('_NAME_PROMPT',' Enter your name:');
DEFINE('_EMAIL_PROMPT',' E-mail address:');
DEFINE('_MESSAGE_PROMPT',' Enter your message:');
DEFINE('_SEND_BUTTON','Send');
DEFINE('_CONTACT_FORM_NC','Please make sure the form is complete and valid.');
DEFINE('_CONTACT_TELEPHONE','Telephone: ');
DEFINE('_CONTACT_MOBILE','Mobile: ');
DEFINE('_CONTACT_FAX','Fax: ');
DEFINE('_CONTACT_EMAIL','Email: ');
DEFINE('_CONTACT_NAME','Name: ');
DEFINE('_CONTACT_POSITION','Postition: ');
DEFINE('_CONTACT_ADDRESS','Address: ');
DEFINE('_CONTACT_MISC','Information: ');
DEFINE('_CONTACT_SEL','Select Contact:');
DEFINE('_CONTACT_NONE','There are no Contact Details listed.');
DEFINE('_EMAIL_A_COPY','Email a copy of this message to your own address');
DEFINE('_CONTACT_DOWNLOAD_AS','Download information as a');
DEFINE('_VCARD','VCard');

/** pageNavigation */
DEFINE('_PN_PAGE','Page');
DEFINE('_PN_OF','of');
DEFINE('_PN_START','Start');
DEFINE('_PN_PREVIOUS','Prev');
DEFINE('_PN_NEXT','Next');
DEFINE('_PN_END','End');
DEFINE('_PN_DISPLAY_NR','Display #');
DEFINE('_PN_RESULTS','Results');

/** emailfriend */
DEFINE('_EMAIL_TITLE','E-mail a friend');
DEFINE('_EMAIL_FRIEND','E-mail this to a friend.');
DEFINE('_EMAIL_FRIEND_ADDR',"Your friend's E-mail:");
DEFINE('_EMAIL_YOUR_NAME','Your Name:');
DEFINE('_EMAIL_YOUR_MAIL','Your E-mail:');
DEFINE('_SUBJECT_PROMPT',' Message subject:');
DEFINE('_BUTTON_SUBMIT_MAIL','Send e-mail');
DEFINE('_BUTTON_CANCEL','Cancel');
DEFINE('_EMAIL_ERR_NOINFO','You must enter your valid e-mail and the valid e-mail to send to.');
DEFINE('_EMAIL_MSG',' The following page from the "%s" website has been sent to you by %s ( %s ).

You can access it at the following url:
%s');
DEFINE('_EMAIL_INFO','Item sent by');
DEFINE('_EMAIL_SENT','This item has been sent to');
DEFINE('_PROMPT_CLOSE','Close Window');

/** classes/html/content.php */
DEFINE('_AUTHOR_BY', ' Contributed by');
DEFINE('_WRITTEN_BY', ' Written by');
DEFINE('_LAST_UPDATED', 'Last Updated');
DEFINE('_BACK','[ Back ]');
DEFINE('_LEGEND','Legend');
DEFINE('_DATE','Date');
DEFINE('_ORDER_DROPDOWN','Order');
DEFINE('_HEADER_TITLE','Item Title');
DEFINE('_HEADER_AUTHOR','Author');
DEFINE('_HEADER_SUBMITTED','Submitted');
DEFINE('_HEADER_HITS','Hits');
DEFINE('_E_EDIT','Edit');
DEFINE('_E_ADD','Add');
DEFINE('_E_WARNUSER','Please either Cancel or Save the current change');
DEFINE('_E_WARNTITLE','Content item must have a title');
DEFINE('_E_WARNTEXT','Content item must have intro text');
DEFINE('_E_WARNCAT','Please select a category');
DEFINE('_E_CONTENT','Content');
DEFINE('_E_TITLE','Title:');
DEFINE('_E_CATEGORY','Category:');
DEFINE('_E_INTRO','Intro Text');
DEFINE('_E_MAIN','Main Text');
DEFINE('_E_MOSIMAGE','INSERT {mosimage}');
DEFINE('_E_IMAGES','Images');
DEFINE('_E_GALLERY_IMAGES','Gallery Images');
DEFINE('_E_CONTENT_IMAGES','Content Images');
DEFINE('_E_EDIT_IMAGE','Edit Image');
DEFINE('_E_INSERT','Insert');
DEFINE('_E_UP','Up');
DEFINE('_E_DOWN','Down');
DEFINE('_E_REMOVE','Remove');
DEFINE('_E_SOURCE','Source:');
DEFINE('_E_ALIGN','Align:');
DEFINE('_E_ALT','Alt Text:');
DEFINE('_E_BORDER','Border:');
DEFINE('_E_CAPTION','Caption');
DEFINE('_E_APPLY','Apply');
DEFINE('_E_PUBLISHING','Publishing');
DEFINE('_E_STATE','State:');
DEFINE('_E_AUTHOR_ALIAS','Author Alias:');
DEFINE('_E_ACCESS_LEVEL','Access Level:');
DEFINE('_E_ORDERING','Ordering:');
DEFINE('_E_START_PUB','Start Publishing:');
DEFINE('_E_FINISH_PUB','Finish Publishing:');
DEFINE('_E_SHOW_FP','Show on Front Page:');
DEFINE('_E_HIDE_TITLE','Hide Item Title:');
DEFINE('_E_METADATA','Metadata');
DEFINE('_E_M_DESC','Description:');
DEFINE('_E_M_KEY','Keywords:');
DEFINE('_E_SUBJECT','Subject:');
DEFINE('_E_EXPIRES','Expiry Date:');
DEFINE('_E_VERSION','Version:');
DEFINE('_E_ABOUT','About');
DEFINE('_E_CREATED','Created:');
DEFINE('_E_LAST_MOD','Last Modified:');
DEFINE('_E_HITS','Hits:');
DEFINE('_E_SAVE','Save');
DEFINE('_E_CANCEL','Cancel');
DEFINE('_E_REGISTERED','Registered Users Only');
DEFINE('_E_ITEM_INFO','Item Information');
DEFINE('_E_ITEM_SAVED','Item succesfully saved.');
DEFINE('_ITEM_PREVIOUS','&lt; Prev');
DEFINE('_ITEM_NEXT','Next &gt;');


/** content.php */
DEFINE('_SECTION_ARCHIVE_EMPTY','There are currently no Archived Entries for this Section, please come back later');
DEFINE('_CATEGORY_ARCHIVE_EMPTY','There are currently no Archived Entries for this Category, please come back later');
DEFINE('_HEADER_SECTION_ARCHIVE','Section Archives');
DEFINE('_HEADER_CATEGORY_ARCHIVE','Category Archives');
DEFINE('_ARCHIVE_SEARCH_FAILURE','There are no Archived entries for %s %s');	// values are month then year
DEFINE('_ARCHIVE_SEARCH_SUCCESS','Here are the Archived entries for %s %s');	// values are month then year
DEFINE('_FILTER','Filter');
DEFINE('_ORDER_DROPDOWN_DA','Date asc');
DEFINE('_ORDER_DROPDOWN_DD','Date desc');
DEFINE('_ORDER_DROPDOWN_TA','Title asc');
DEFINE('_ORDER_DROPDOWN_TD','Title desc');
DEFINE('_ORDER_DROPDOWN_HA','Hits asc');
DEFINE('_ORDER_DROPDOWN_HD','Hits desc');
DEFINE('_ORDER_DROPDOWN_AUA','Author asc');
DEFINE('_ORDER_DROPDOWN_AUD','Author desc');
DEFINE('_ORDER_DROPDOWN_O','Ordering');

/** poll.php */
DEFINE('_ALERT_ENABLED','Cookies must be enabled!');
DEFINE('_ALREADY_VOTE','You already voted for this poll today!');
DEFINE('_NO_SELECTION','No selection has been made, please try again');
DEFINE('_THANKS','Thanks for your vote!');
DEFINE('_SELECT_POLL','Select Poll from the list');

/** classes/html/poll.php */
DEFINE('_JAN','January');
DEFINE('_FEB','February');
DEFINE('_MAR','March');
DEFINE('_APR','April');
DEFINE('_MAY','May');
DEFINE('_JUN','June');
DEFINE('_JUL','July');
DEFINE('_AUG','August');
DEFINE('_SEP','September');
DEFINE('_OCT','October');
DEFINE('_NOV','November');
DEFINE('_DEC','December');
DEFINE('_POLL_TITLE','Poll - Results');
DEFINE('_SURVEY_TITLE','Poll Title:');
DEFINE('_NUM_VOTERS','Number of Voters');
DEFINE('_FIRST_VOTE','First Vote');
DEFINE('_LAST_VOTE','Last Vote');
DEFINE('_SEL_POLL','Select Poll:');
DEFINE('_NO_RESULTS','There are no results for this poll.');

/** registration.php */
DEFINE('_ERROR_PASS','Sorry, no corresponding user was found');
DEFINE('_NEWPASS_MSG','The user account $checkusername has this email associated with it.\n'
.'A web user from $mosConfig_live_site has just requested that a new password be sent.\n\n'
.' Your New Password is: $newpass\n\nIf you didn\'t ask for this, don\'t worry.'
.' You are seeing this message, not them. If this was an error just login with your'
.' new password and then change your password to what you would like it to be.');
DEFINE('_NEWPASS_SUB','$_sitename :: New password for - $checkusername');
DEFINE('_NEWPASS_SENT','New User Password created and sent!');
DEFINE('_REGWARN_NAME','Please enter your name.');
DEFINE('_REGWARN_UNAME','Please enter a user name.');
DEFINE('_REGWARN_MAIL','Please enter a valid e-mail address.');
DEFINE('_REGWARN_PASS','Please enter a valid password.  No spaces, more than 6 characters and contain 0-9,a-z,A-Z');
DEFINE('_REGWARN_VPASS1','Please verify the password.');
DEFINE('_REGWARN_VPASS2','Password and verification do not match, please try again.');
DEFINE('_REGWARN_INUSE','This username/password already in use. Please try another.');
DEFINE('_REGWARN_EMAIL_INUSE', 'This e-mail is already registered. If you forgot the password click on "Lost your Password" and new password will be sent to you.');
DEFINE('_SEND_SUB','Account details for %s at %s');
DEFINE('_USEND_MSG_ACTIVATE', 'Hello %s,

Thank you for registering at %s. Your account is created and must be activated before you can use it.
To activate the account click on the following link or copy-paste it in your browser:
%s

After activation you may login to %s using the following username and password:

Username - %s
Password - %s');
DEFINE('_USEND_MSG', "Hello %s,

Thank you for registering at %s.

You may now login to %s using the username and password you registered with.");
DEFINE('_USEND_MSG_NOPASS','Hello $name,\n\nYou have been added as a user to $mosConfig_live_site.\n'
.'You may login to $mosConfig_live_site with the username and password you registered with.\n\n'
.'Please do not respond to this message as it is automatically generated and is for information purposes only\n');
DEFINE('_ASEND_MSG','Hello %s,

A new user has registered at %s.
This email contains their details:

Name - %s
e-mail - %s
Username - %s

Please do not respond to this message as it is automatically generated and is for information purposes only');
DEFINE('_REG_COMPLETE_NOPASS','<div class="componentheading">Registration Complete!</div><br />&nbsp;&nbsp;'
.'You may now login.<br />&nbsp;&nbsp;');
DEFINE('_REG_COMPLETE', '<div class="componentheading">Registration Complete!</div><br />You may now login.');
DEFINE('_REG_COMPLETE_ACTIVATE', '<div class="componentheading">Registration Complete!</div><br />Your account has been created and activation link has been sent to the e-mail address you entered. Note that you must activate the account by clicking on the activation link when you get the e-mail before you can login.');
DEFINE('_REG_ACTIVATE_COMPLETE', '<div class="componentheading">Activation Complete!</div><br />Your account has been successfully activated. You can now login using the username and password you choose during the registration.');
DEFINE('_REG_ACTIVATE_NOT_FOUND', '<div class="componentheading">Invalid Activation Link!</div><br />There is no such account in our database or the account has already been activated.');

/** classes/html/registration.php */
DEFINE('_PROMPT_PASSWORD','Lost your Password?');
DEFINE('_NEW_PASS_DESC','Please enter your Username and e-mail address then click on the Send Password button.<br />'
.'You will receive a new password shortly.  Use this new password to access the site.');
DEFINE('_PROMPT_UNAME','Username:');
DEFINE('_PROMPT_EMAIL','E-mail Address:');
DEFINE('_BUTTON_SEND_PASS','Send Password');
DEFINE('_REGISTER_TITLE','Registration');
DEFINE('_REGISTER_NAME','Name:');
DEFINE('_REGISTER_UNAME','Username:');
DEFINE('_REGISTER_EMAIL','E-mail:');
DEFINE('_REGISTER_PASS','Password:');
DEFINE('_REGISTER_VPASS','Verify Password:');
DEFINE('_REGISTER_REQUIRED','Fields marked with an asterisk (*) are required.');
DEFINE('_BUTTON_SEND_REG','Send Registration');
DEFINE('_SENDING_PASSWORD','Your password will be sent to the above e-mail address.<br />Once you have received your'
.' new password you can login in and change it.');

/** classes/html/search.php */
DEFINE('_SEARCH_TITLE','Search');
DEFINE('_PROMPT_KEYWORD','Search Keyword');
DEFINE('_SEARCH_MATCHES','returned %d matches');
DEFINE('_CONCLUSION','Total $totalRows results found.  Search for <b>$searchword</b> with');
DEFINE('_NOKEYWORD','No results were found');
DEFINE('_IGNOREKEYWORD','One or more common words were ignored in the search');
DEFINE('_SEARCH_ANYWORDS','Any words');
DEFINE('_SEARCH_ALLWORDS','All words');
DEFINE('_SEARCH_PHRASE','Exact phrase');
DEFINE('_SEARCH_NEWEST','Newest first');
DEFINE('_SEARCH_OLDEST','Oldest first');
DEFINE('_SEARCH_POPULAR','Most popular');
DEFINE('_SEARCH_ALPHABETICAL','Alphabetical');
DEFINE('_SEARCH_CATEGORY','Section/Category');

/** templates/*.php */
/* DEFINE('_ISO','charset=utf-8'); */
DEFINE('_ISO','charset=iso-8859-1');
DEFINE('_DATE_FORMAT','l, F d Y');  //Uses PHP's DATE Command Format - Depreciated
/**
* Modify this line to reflect how you want the date to appear in your site
*
*e.g. DEFINE("_DATE_FORMAT_LC","%A, %d %B %Y %H:%M"); //Uses PHP's strftime Command Format
*/
DEFINE('_DATE_FORMAT_LC',"%A, %d %B %Y"); //Uses PHP's strftime Command Format
DEFINE('_DATE_FORMAT_LC2',"%A, %d %B %Y %H:%M");
DEFINE('_SEARCH_BOX','search...');
DEFINE('_NEWSFLASH_BOX','Newsflash!');
DEFINE('_MAINMENU_BOX','Main Menu');

/** classes/html/usermenu.php */
DEFINE('_UMENU_TITLE','User Menu');
DEFINE('_HI','Hi, ');

/** user.php */
DEFINE('_SAVE_ERR','Please complete all the fields.');
DEFINE('_THANK_SUB','Thanks for your submission. Your submission will now be reviewed before being posted to the site.');
DEFINE('_UP_SIZE','You cannot upload files greater than 15kb in size.');
DEFINE('_UP_EXISTS','Image $userfile_name already exists. Please rename the file and try again.');
DEFINE('_UP_COPY_FAIL','Failed to copy');
DEFINE('_UP_TYPE_WARN','You may only upload a gif, or jpg image.');
DEFINE('_MAIL_SUB','User Submitted');
DEFINE('_MAIL_MSG','Hello $adminName,\n\n\nA user submitted $type:\n [ $title ]\n has been just been submitted by user:\n [ $author ]\n'
.' for $mosConfig_live_site.\n\n\n\n'
.'Please go to $mosConfig_live_site/administrator to view and approve this $type.\n\n'
.'Please do not respond to this message as it is automatically generated and is for information purposes only\n');
DEFINE('_PASS_VERR1','If changing your password please enter the password again to verify.');
DEFINE('_PASS_VERR2','If changing your password please make sure the password and verification match.');
DEFINE('_UNAME_INUSE','This username already in use.');
DEFINE('_UPDATE','Update');
DEFINE('_USER_DETAILS_SAVE','Your settings have been saved.');
DEFINE('_USER_LOGIN','User Login');

/** components/com_user */
DEFINE('_EDIT_TITLE','Edit Your Details');
DEFINE('_YOUR_NAME','Your Name:');
DEFINE('_EMAIL','e-mail:');
DEFINE('_UNAME','User Name:');
DEFINE('_PASS','Password:');
DEFINE('_VPASS','Verify Password:');
DEFINE('_SUBMIT_SUCCESS','Submission Success!');
DEFINE('_SUBMIT_SUCCESS_DESC','Your item has been successfully submitted to our administrators. It will be reviewed before being published on this site.');
DEFINE('_WELCOME','Welcome!');
DEFINE('_WELCOME_DESC','Welcome to the user section of our site');
DEFINE('_CONF_CHECKED_IN','Checked out items have now been all checked in');
DEFINE('_CHECK_TABLE','Checking table');
DEFINE('_CHECKED_IN','Checked in ');
DEFINE('_CHECKED_IN_ITEMS',' items');
DEFINE('_PASS_MATCH','Passwords do not match');

/** components/com_banners */
DEFINE('_BNR_CLIENT_NAME','You must select a name for the client.');
DEFINE('_BNR_CONTACT','You must select a contact for the client.');
DEFINE('_BNR_VALID_EMAIL','You must select a valid email for the client.');
DEFINE('_BNR_CLIENT','You must select a client,');
DEFINE('_BNR_NAME','You must select a name for the banner.');
DEFINE('_BNR_IMAGE','You must select a image for the banner.');
DEFINE('_BNR_URL','You must select a URL/Custom banner code for the banner.');

/** components/com_login */
DEFINE('_ALREADY_LOGIN','You are already logged in!');
DEFINE('_LOGOUT','Click here to logout');
DEFINE('_LOGIN_TEXT','Use the login and password fields opposite to gain full access');
DEFINE('_LOGIN_SUCCESS','You have succesfully Logged In');
DEFINE('_LOGOUT_SUCCESS','You have successfully Logged Out');
DEFINE('_LOGIN_DESCRIPTION','To access the Private area of this site please Login');
DEFINE('_LOGOUT_DESCRIPTION','You are currently Logged in to the private area of this site');


/** components/com_weblinks */
DEFINE('_WEBLINKS_TITLE','Web Links');
DEFINE('_WEBLINKS_DESC','We are regularly out on the web. When we find a great site we list'
.' it here for you to enjoy.  <br />From the list below choose one of our weblink topics, then select a URL to visit.');
DEFINE('_HEADER_TITLE_WEBLINKS','Web Link');
DEFINE('_SECTION','Section:');
DEFINE('_SUBMIT_LINK','Submit A Web Link');
DEFINE('_URL','URL:');
DEFINE('_URL_DESC','Description:');
DEFINE('_NAME','Name:');
DEFINE('_WEBLINK_EXIST','There is a weblink already with that name, please try again.');
DEFINE('_WEBLINK_TITLE','Your Weblink must contain a title.');

/** components/com_newfeeds */
DEFINE('_FEED_NAME','Feed Name');
DEFINE('_FEED_ARTICLES','# Articles');
DEFINE('_FEED_LINK','Feed Link');

/** whos_online.php */
DEFINE('_WE_HAVE', 'We have ');
DEFINE('_AND', ' and ');
DEFINE('_GUEST_COUNT','$guest_array guest');
DEFINE('_GUESTS_COUNT','$guest_array guests');
DEFINE('_MEMBER_COUNT','$user_array member');
DEFINE('_MEMBERS_COUNT','$user_array members');
DEFINE('_ONLINE',' online');
DEFINE('_NONE','No Users Online');

/** modules/mod_stats.php */
DEFINE('_TIME_STAT','Time');
DEFINE('_MEMBERS_STAT','Members');
DEFINE('_HITS_STAT','Hits');
DEFINE('_NEWS_STAT','News');
DEFINE('_LINKS_STAT','WebLinks');
DEFINE('_VISITORS','Visitors');

/** /adminstrator/components/com_menus/admin.menus.html.php */
DEFINE('_MAINMENU_HOME','* The 1st Published item in this menu [mainmenu] is the default `Homepage` for the site *');
DEFINE('_MAINMENU_DEL','* You cannot `delete` this menu as it is required for the proper operation of Joomla! *');
DEFINE('_MENU_GROUP','* Some `Menu Types` appear in more than one group *');


/** administrators/components/com_users */
DEFINE('_NEW_USER_MESSAGE_SUBJECT', 'New User Details' );
DEFINE('_NEW_USER_MESSAGE', 'Hello %s,


You have been added as a user to %s by an Administrator.

This email contains your username and password to log into the %s

Username - %s
Password - %s


Please do not respond to this message as it is automatically generated and is for information purposes only');

/** administrators/components/com_massmail */
DEFINE('_MASSMAIL_MESSAGE', "This is an email from '%s'

Message:
" );

?>