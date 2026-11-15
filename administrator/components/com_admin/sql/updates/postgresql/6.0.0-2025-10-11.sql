ALTER TABLE "#__history"
    ADD COLUMN "is_current" SMALLINT NOT NULL DEFAULT 0 /** CAN FAIL **/;
ALTER TABLE "#__history"
    ADD COLUMN "is_legacy" SMALLINT NOT NULL DEFAULT 0 /** CAN FAIL **/;
UPDATE "#__history" SET "is_legacy" = 1;
