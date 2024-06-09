ALTER TABLE "#__modules" ADD COLUMN "menu_assignment" text /** CAN FAIL **/;

-- --------------------------------------------------------
-- Migrate the values from #__modules_menu to #__modules.menu_assignment
--
-- Will be a JSON format, like {"assigned": [103, 102], "assignment": -1}
-- where "assigned" is the menu items currently assigned to the module and
-- "assignment" is the option selected inside the module menu assignment option
--
WITH module_menu_assignment AS (
  SELECT
    t1."moduleid", array_to_json(array_agg(ABS(t1."menuid")::integer)) AS mids,
    CASE
      WHEN MIN(t1."menuid") > 0 THEN 1
      WHEN MIN(t1."menuid") < 0 THEN -1
      ELSE 0
    END AS modsign
	FROM
    "#__modules_menu" AS t1
	  INNER JOIN "#__modules" AS t2 ON t2."id"=t1."moduleid"
  WHERE
    t2."client_id"=0
	GROUP BY
    t1."moduleid"
)
UPDATE "#__modules"
SET
  "menu_assignment" = mma."menu_assignment"
FROM (
	SELECT moduleid,
		json_build_object(
			'assigned',
      CASE
				WHEN mids::jsonb @? '$[0] ? (@ == 0)' THEN mids::jsonb - 0
				ELSE mids::jsonb
			END,
			'assignment', modsign
		) AS menu_assignment
	FROM module_menu_assignment
) AS mma
WHERE
  "id"=mma."moduleid";

-- --------------------------------------------------------
-- DROP TABLE IF EXISTS "#__modules_menu";
