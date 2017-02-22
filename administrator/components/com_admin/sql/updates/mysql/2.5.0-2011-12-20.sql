SELECT @old_params:= CONCAT(SUBSTRING_INDEX(SUBSTRING(params, LOCATE('"filters":', params)), '}}', 1), '}}') as filters
FROM `#__extensions` 
WHERE name="com_content";

UPDATE `#__extensions`
SET params=CONCAT('{',SUBSTRING(params, 2, CHAR_LENGTH(params)-2),IF(params='','',','),@old_params,'}')
WHERE name="com_config";