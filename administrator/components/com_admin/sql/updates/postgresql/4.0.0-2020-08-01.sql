SELECT setval('#__workflows_id_seq', (SELECT MAX("id") FROM "#__workflows") + 1);
SELECT setval('#__workflow_stages_id_seq', (SELECT MAX("id") FROM "#__workflow_stages") + 1);
SELECT setval('#__workflow_transitions_id_seq', (SELECT MAX("id") FROM "#__workflow_transitions") + 1);
