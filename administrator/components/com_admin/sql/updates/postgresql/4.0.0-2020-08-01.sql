--
-- Fix possibly broken sequence values for com_workflow tables, see
-- pull request no. 30251 for details.
--
SELECT setval('#__workflows_id_seq', (SELECT MAX("id") FROM "#__workflows") + 1, false);
SELECT setval('#__workflow_stages_id_seq', (SELECT MAX("id") FROM "#__workflow_stages") + 1, false);
SELECT setval('#__workflow_transitions_id_seq', (SELECT MAX("id") FROM "#__workflow_transitions") + 1, false);
