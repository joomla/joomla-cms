# Alters the scheme of #__users
ALTER TABLE #__users 
ADD `firstname` VARCHAR(150) NOT NULL DEFAULT '' AFTER `name`, 
ADD `middlename` VARCHAR(150) NOT NULL DEFAULT '' AFTER `firstname`, 
ADD `surname` VARCHAR(150) NOT NULL DEFAULT '' AFTER `middlename`, 
ADD KEY `idx_firstname` (`firstname`) AFTER `idx_name`, 
ADD KEY `idx_middlename` (`middlename`) AFTER `idx_firstname`, 
ADD KEY `idx_surname` (`surname`) AFTER `idx_middlename`;

# Migrates the data - by splitting the name field data into the 3 fields below.
# It is done after convention that one can only have a one word first name and surname,
# everything else are middle names.
UPDATE #__users SET 
`firstname` = SUBSTRING_INDEX(`name`, ' ', 1), 
`middlename` = SUBSTRING_INDEX(SUBSTRING_INDEX(`name`, ' ', -2),' ', 1), 
`surname` = SUBSTRING_INDEX(`name`, ' ', -1); 

# Removes the name field and key
ALTER TABLE #__users DROP COLUMN `name`, DROP KEY `idx_name`;