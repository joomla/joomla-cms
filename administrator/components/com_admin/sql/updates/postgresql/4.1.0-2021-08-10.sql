--
-- Table structure for table "#__cookiemanager_consents"
--

CREATE TABLE IF NOT EXISTS "#__cookiemanager_consents" (
  "id" serial NOT NULL,
  "uuid" varchar(100) NOT NULL,
  "ccuuid" varchar(100) NOT NULL,
  "consent_opt_in" varchar(255) NOT NULL,
  "consent_opt_out" varchar(255) NOT NULL,
  "consent_date" varchar(100) NOT NULL,
  "user_agent" varchar(150) NOT NULL,
  "url" varchar(100) NOT NULL,
  PRIMARY KEY ("id")
);
