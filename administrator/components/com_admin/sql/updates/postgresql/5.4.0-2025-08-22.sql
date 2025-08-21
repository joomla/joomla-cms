UPDATE #__scheduler_task
SET params = params - 'email'
WHERE params ? 'email';