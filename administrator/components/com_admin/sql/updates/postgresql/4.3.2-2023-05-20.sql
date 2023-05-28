ALTER TABLE "#__user_mfa" ADD COLUMN "tries" bigint DEFAULT 0 NOT NULL /** CAN FAIL **/;
ALTER TABLE "#__user_mfa" ADD COLUMN "last_try" timestamp without time zone /** CAN FAIL **/;
