ALTER TABLE "#__history"
    ADD COLUMN "is_current" SMALLINT NOT NULL DEFAULT 0 /** CAN FAIL **/;