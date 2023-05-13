const mailTester = require('smtp-tester');

// The mail server instance
let mailServer = null;

// The cached mails
let cachedMails = [];

/**
 * Returns all cached mails. It waits for at least 5 seconds till a mail arrives.
 *
 * @returns a promise which resolves the cached mails
 */
function getMails() {
  return new Promise(async (resolve) => {
    // Waiting here at least 5 seconds till the mail arrives
    for (let i = 0; i < 5; i++) {
      if (cachedMails.length !== 0) {
        break;
      }

      // Sleep for a second
      await new Promise(resolve => setTimeout(resolve, 1000));
    }

    resolve(cachedMails);
  });
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

  // Start he mail server on the cofnigured port
  mailServer = mailTester.init(config.env.smtp_port);

  // Listen to incoming mails and add them to the internal cache
  mailServer.bind((addr, id, email) => cachedMails.push(email));

  // Reset the cached mails
  cachedMails = [];

  return null;
}

module.exports = { getMails, clearEmails, startMailServer };
