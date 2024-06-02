ALTER TABLE "#__modules" ADD COLUMN "menu_assignment" TEXT NULL DEFAULT NULL AFTER `params`;

-- --------------------------------------------------------
-- Migrate the values from #__modules_menu to #__modules.menu_assignment
--
-- Will be a JSON format, like {"assigned": [103, 102], "assignment": -1}
-- where "assigned" is the menu items currently assigned to the module and
-- "assignment" is the option selected inside the module menu assignment option
--
UPDATE "#__modules" AS m
SET m."menu_assignment" = s."menu_assignment"
FROM (
    SELECT t1."moduleid",
      json_build_object(
        'assigned',
          CASE
            WHEN MIN(t1."menuid")=0 THEN json_build_array()
            ELSE array_agg(ABS(t1."menuid"))
          END,
        'assignment',
          CASE
            WHEN MIN(t1."menuid")>0 THEN 1
            WHEN MIN(t1."menuid")<0 THEN -1
            ELSE 0
          END
      ) AS menu_assignment
    FROM "#__modules_menu" AS t1
    INNER JOIN "#__modules" AS t2 ON t2."id"=t1."moduleid"
    WHERE t2."client_id"=0
    GROUP BY t1."moduleid"
  ) AS s
WHERE m."id"=s."moduleid";

-- --------------------------------------------------------
-- DROP TABLE IF EXISTS `#__modules_menu`;
