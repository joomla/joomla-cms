--
-- Change the column types before adding foreign keys
--
ALTER TABLE "#__user_usergroup_map" ALTER COLUMN "user_id" TYPE integer;
ALTER TABLE "#__user_usergroup_map" ALTER COLUMN "group_id" TYPE integer;
ALTER TABLE "#__user_usergroup_map" ALTER COLUMN "user_id" DROP DEFAULT;
ALTER TABLE "#__user_usergroup_map" ALTER COLUMN "group_id" DROP DEFAULT;

ALTER TABLE `#__user_usergroup_map` ADD CONSTRAINT "fk_user_group_user_id" FOREIGN KEY ("user_id") REFERENCES "#__users" ("id") ON DELETE CASCADE;
ALTER TABLE `#__user_usergroup_map` ADD CONSTRAINT "fk_user_group_group_id" FOREIGN KEY ("group_id") REFERENCES "#__usergroups" ("id")  ON DELETE CASCADE;
