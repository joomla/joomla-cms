CREATE TEMPORARY TABLE "content_params" (
  "filters" text NOT NULL
);
INSERT INTO "content_params"
SELECT split_part(
substring(params, CASE WHEN 
					POSITION('"filters":' IN params) <>0 THEN POSITION('"filters":' IN params) 
					ELSE CHAR_LENGTH(params)+1
				  END
		)
, '}}', 1)	|| '}}' as filters
FROM "#__extensions" 
WHERE name='com_content';


UPDATE "#__extensions"
SET params='{' || SUBSTRING(params, 2, CHAR_LENGTH(params)-2) || CASE WHEN params='' THEN '' ELSE ',' END || (SELECT filters FROM "content_params")	|| '}' 
WHERE name='com_config';