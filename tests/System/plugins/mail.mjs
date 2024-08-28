import mailTester from 'smtp-tester';

// The mail server instance
let mailServer = null;

// The cached mails
let cachedMails = [];

/**
 * Returns all cached mails. It waits for maximum 3 seconds till a mail arrives.
 *
 * @returns a promise which resolves the cached mails
 */
async function getMails() {
  // Waiting here maximum 3 seconds to get a mail
  for (let i = 0; i < 3; i += 1) {
    if (cachedMails.length !== 0) {
      break;
    }

    // Sleep for a second
    /* eslint-disable no-await-in-loop */
    await new Promise((r) => { setTimeout(r, 1000); });
    /* eslint-enable no-await-in-loop */
  }

  return new Promise((resolve) => { resolve(cachedMails); });
}

/**
 * Clears the cached mails.
 *
 * @returns null
 */
function clearEmails() {
  cachedMails = [];

  return null;
}

/**
 * Starts the mail server.
 *
 * @returns null
 */
function startMailServer(config) {
  // Check if the mail server is already started
  if (mailServer !== null) {
    return null;
  }

  // Start the mail server on the configured port
  mailServer = mailTester.init(config.env.smtp_port);

  // Uncomment the next line when you want to see the incoming mails while writing the tests
  // mailServer.module('logAll');

  // Listen to incoming mails and add them to the internal cache
  mailServer.bind((addr, id, email) => cachedMails.push(email));

  // Reset the cached mails
  cachedMails = [];

  return null;
}

export { getMails, clearEmails, startMailServer };
