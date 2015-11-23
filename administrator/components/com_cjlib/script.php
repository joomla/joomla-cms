<?php
/**
 * @version		$Id: script.php 74 2011-01-11 20:04:22Z maverick $
 * @package		CoreJoomla16.Surveys
 * @subpackage	Components.site
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com, Inc. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

/**
 * Script file of CommunitySurveys component
 */
class com_cjlibInstallerScript {
	
	function install($parent){
		
		$parent->getParent()->setRedirectURL('index.php?option=com_cjlib');
	}

	function uninstall($parent){
		
		echo '<p>CJLib component successfully uninstalled.</p>';
	}

	function update($parent){
		
		$db = JFactory::getDBO();
		
		if(method_exists($parent, 'extension_root')) {
			
			$sqlfile = $parent->getPath('extension_root').DS.'sql'.DS.'install.mysql.utf8.sql';
		} else {
			
			$sqlfile = $parent->getParent()->getPath('extension_root').DS.'sql'.DS.'install.mysql.utf8.sql';
		}
		
		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);
		
		if ($buffer !== false) {
			
			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);
			
			if (count($queries) != 0) {
				
				foreach ($queries as $query){
					
					$query = trim($query);
					
					if ($query != '' && $query{0} != '#') {
						
						$db->setQuery($query);
						
						if (!$db->query()) {
							
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
							return false;
						}
					}
				}
			}
		}
		
		echo '<p>CJLib component successfully updated.</p>';
	}

	function preflight($type, $parent){
		
		echo '<p>Installing CJLib component, please wait...</p>';
	}

	function postflight($type, $parent){
		
		// CJLib includes
		$cjlib = JPATH_ROOT.DS.'components'.DS.'com_cjlib'.DS.'framework.php';
		
		$db = JFactory::getDBO();
		$update_queries = array ();

		$update_queries[] = 'ALTER IGNORE TABLE `#__corejoomla_messages` ADD COLUMN `asset_id` int(10) unsigned NOT NULL';
		$update_queries[] = 'ALTER IGNORE TABLE `#__corejoomla_messagequeue` ADD COLUMN `created` DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\'';
		$update_queries[] = 'ALTER IGNORE TABLE `#__corejoomla_messagequeue` ADD COLUMN `processed` DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\'';
		$update_queries[] = 'ALTER IGNORE TABLE `#__corejoomla_messagequeue` ADD INDEX `idx_corejoomla_message_queue_status`(`status`)';
		$update_queries[] = 'ALTER IGNORE TABLE `#__corejoomla_messagequeue` ADD INDEX `idx_corejoomla_message_queue_created`(`created`)';
		
		$update_queries[] = 'ALTER IGNORE TABLE `#__corejoomla_countries` ADD COLUMN `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT, DROP PRIMARY KEY, ADD PRIMARY KEY (`id`)';
		$update_queries[] = 'ALTER IGNORE TABLE `#__corejoomla_countries` ADD COLUMN `language` VARCHAR(6) NOT NULL DEFAULT \'*\'';
		$update_queries[] = 'ALTER IGNORE TABLE `#__corejoomla_countries` ADD UNIQUE INDEX `idx_corejoomla_countries_uniq`(`country_code`, `language`)';
		$update_queries[] = 'ALTER IGNORE TABLE `#__cjblog_users` MODIFY COLUMN `about` MEDIUMTEXT DEFAULT NULL';
		
		$update_queries[] = "insert into #__corejoomla_countries(country_name, country_code) values
			('Afghanistan','AF'), ('Åland Islands','AX'), ('Albania','AL'), ('Algeria','DZ'), ('American Samoa','AS'), ('Andorra','AD'), ('Angola','AO'),
			('Anguilla','AI'), ('Antarctica','AQ'), ('Antigua And Barbuda','AG'), ('Argentina','AR'), ('Armenia','AM'), ('Aruba','AW'), ('Australia','AU'),
			('Austria','AT'), ('Azerbaijan','AZ'), ('Bahamas','BS'), ('Bahrain','BH'), ('Bangladesh','BD'), ('Barbados','BB'), ('Belarus','BY'),
			('Belgium','BE'), ('Belize','BZ'), ('Benin','BJ'), ('Bermuda','BM'), ('Bhutan','BT'), ('Bolivia','BO'), ('Bosnia And Herzegovina','BA'),
			('Botswana','BW'), ('Bouvet Island','BV'), ('Brazil','BR'), ('British Indian Ocean Territory','IO'),('Brunei Darussalam','BN'), ('Bulgaria','BG'),
			('Burkina Faso','BF'), ('Burundi','BI'), ('Cambodia','KH'), ('Cameroon','CM'), ('Canada','CA'), ('Cape Verde','CV'), ('Cayman Islands','KY'),
			('Central African Republic','CF'), ('Chad','TD'), ('Chile','CL'), ('China','CN'), ('Christmas Island','CX'), ('Cocos Keeling Islands','CC'),
			('Colombia','CO'), ('Comoros','KM'), ('Congo','CG'), ('Congo','CD'), ('Cook Islands','CK'), ('Costa Rica','CR'), ('Côte D\'ivoire','CI'),
			('Croatia','HR'), ('Cuba','CU'), ('Cyprus','CY'), ('Czech Republic','CZ'), ('Denmark','DK'), ('Djibouti','DJ'), ('Dominica','DM'),
			('Dominican Republic','DO'), ('Ecuador','EC'), ('Egypt','EG'), ('El Salvador','SV'), ('Equatorial Guinea','GQ'), ('Eritrea','ER'), ('Estonia','EE'),
			('Ethiopia','ET'), ('Falkland Islands Malvinas','FK'),('Faroe Islands','FO'), ('Fiji','FJ'), ('Finland','FI'), ('France','FR'), ('French Guiana','GF'),
			('French Polynesia','PF'), ('French Southern Territories','TF'), ('Gabon','GA'), ('Gambia','GM'), ('Georgia','GE'), ('Germany','DE'), ('Ghana','GH'),
			('Gibraltar','GI'), ('Greece','GR'), ('Greenland','GL'), ('Grenada','GD'), ('Guadeloupe','GP'), ('Guam','GU'), ('Guatemala','GT'), ('Guernsey','GG'),
			('Guinea','GN'), ('Guinea-Bissau','GW'), ('Guyana','GY'), ('Haiti','HT'), ('Heard Island And Mcdonald Islands','HM'), ('Honduras','HN'),
			('Hong Kong','HK'), ('Hungary','HU'), ('Iceland','IS'), ('India','IN'), ('Indonesia','ID'), ('Iran','IR'), ('Iraq','IQ'), ('Ireland','IE'),
			('Isle Of Man','IM'), ('Israel','IL'), ('Italy','IT'), ('Jamaica','JM'), ('Japan','JP'), ('Jersey','JE'), ('Jordan','JO'), ('Kazakhstan','KZ'),
			('Kenya','KE'), ('Kiribati','KI'), ('Korea','KP'), ('Korea','KR'), ('Kuwait','KW'), ('Kyrgyzstan','KG'), ('Lao People\'s Democratic Republic','LA'),
			('Latvia','LV'), ('Lebanon','LB'), ('Lesotho','LS'), ('Liberia','LR'), ('Libyan Arab Jamahiriya','LY'), ('Liechtenstein','LI'),
			('Lithuania','LT'), ('Luxembourg','LU'), ('Macao','MO'), ('Macedonia','MK'), ('Madagascar','MG'), ('Malawi','MW'), ('Malaysia','MY'),
			('Maldives','MV'), ('Mali','ML'), ('Malta','MT'), ('Marshall Islands','MH'), ('Martinique','MQ'), ('Mauritania','MR'), ('Mauritius','MU'),
			('Mayotte','YT'), ('Mexico','MX'), ('Micronesia','FM'), ('Moldova','MD'), ('Monaco','MC'), ('Mongolia','MN'), ('Montenegro','ME'),
			('Montserrat','MS'), ('Morocco','MA'), ('Mozambique','MZ'), ('Myanmar','MM'), ('Namibia','NA'),('Nauru','NR'), ('Nepal','NP'), ('Netherlands','NL'),
			('Netherlands Antilles','AN'), ('New Caledonia','NC'), ('New Zealand','NZ'), ('Nicaragua','NI'), ('Niger','NE'), ('Nigeria','NG'), ('Niue','NU'),
			('Norfolk Island','NF'), ('Northern Mariana Islands','MP'), ('Norway','NO'), ('Oman','OM'), ('Pakistan','PK'), ('Palau','PW'),
			('Palestinian Territory','PS'), ('Panama','PA'), ('Papua New Guinea','PG'), ('Paraguay','PY'), ('Peru','PE'), ('Philippines','PH'), ('Pitcairn','PN'),
			('Poland','PL'), ('Portugal','PT'), ('Puerto Rico','PR'), ('Qatar','QA'), ('Réunion','RE'), ('Romania','RO'), ('Russian Federation','RU'),
			('Rwanda','RW'), ('Saint Barth�lemy','BL'), ('Saint Helena','SH'), ('Saint Kitts And Nevis','KN'), ('Saint Lucia','LC'), ('Saint Martin','MF'),
			('Saint Pierre And Miquelon','PM'), ('Saint Vincent And The Grenadines','VC'), ('Samoa','WS'), ('San Marino','SM'), ('Sao Tome And Principe','ST'),
			('Saudi Arabia','SA'), ('Senegal','SN'),  ('Serbia','RS'), ('Seychelles','SC'), ('Sierra Leone','SL'), ('Singapore','SG'), ('Slovakia','SK'),
			('Slovenia','SI'), ('Solomon Islands','SB'),  ('Somalia','SO'), ('South Africa','ZA'),  ('South Georgia And The South Sandwich Islands','GS'),
			('Spain','ES'), ('Sri Lanka','LK'), ('Sudan','SD'), ('Suriname','SR'), ('Svalbard And Jan Mayen','SJ'), ('Swaziland','SZ'), ('Sweden','SE'),
			('Switzerland','CH'), ('Syrian Arab Republic','SY'), ('Taiwan','TW'), ('Tajikistan','TJ'), ('Tanzania','TZ'), ('Thailand','TH'), ('Timor-Leste','TL'),
			('Togo','TG'), ('Tokelau','TK'), ('Tonga','TO'), ('Trinidad And Tobago','TT'), ('Tunisia','TN'), ('Turkey','TR'), ('Turkmenistan','TM'),
			('Turks And Caicos Islands','TC'), ('Tuvalu','TV'), ('Uganda','UG'), ('Ukraine','UA'), ('United Arab Emirates','AE'), ('United Kingdom','GB'),
			('United States','US'), ('United States Minor Outlying Islands','UM'), ('Uruguay','UY'), ('Uzbekistan','UZ'), ('Vanuatu','VU'), ('Vatican City State','VA'),
			('Venezuela','VE'), ('Viet Nam','VN'), ('Virgin Islands British','VG'), ('Virgin Islands U.S.','VI'), ('Wallis And Futuna','WF'), ('Western Sahara','EH'),
			('Yemen','YE'), ('Zambia','ZM'), ('Zimbabwe','ZW'), ('European Union','EU'), ('United Kingdom', 'UK'), ('Ascension Island', 'AC'), ('Clipperton Island', 'CP'),
			('Diego Garcia','DG'), ('Ceuta, Melilla','EA'), ('France, Metropolitan','FX'), ('Canary Islands','IC'), ('USSR','SU'), ('Tristan da Cunha','TA'),
			('Unknown','XX') ON duplicate key update country_name=values(country_name)";
		
		foreach( $update_queries as $query ) {
			
			$db->setQuery( $query );
			
			try{$db->query();}catch(Exception $e){}
		}
		
		if(file_exists($cjlib)){
		
			require_once $cjlib;
		}else{
		
			die('CJLib (CoreJoomla API Library) component files not found. Please check if the component installed properly and try again.');
		}
		
		CJLib::import('corejoomla.framework.core');
		CJFunctions::download_geoip_databases();
		
		echo '<p>CJLib component successfully installed.</p>';
	}
}
