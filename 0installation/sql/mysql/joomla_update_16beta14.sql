# $Id: joomla_update_16beta14.sql 19563 2010-11-18 10:02:12Z eddieajau $

#
# Database updates for 1.6 Beta 13 to Beta 14
#

ALTER TABLE `#__template_styles`
 CHANGE `home` `home` char(7) NOT NULL DEFAULT '0';


