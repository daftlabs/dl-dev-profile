'use strict';
module.exports = (config = {}) => {
  const google = config.google || require('googleapis');
  const googleAuth = config.googleAuth || require('google-auth-library');
  const dataStore = config.dataStore || require('./../services/dataStore')();
  const inquirer = config.inquirer || require('inquirer');

  return [{
    command: 'register <email>',
    description: "Register a daft labs employee.",
    action: (email) => {
      return dataStore.profiles
        .getCurrent()
        .then(profile => inquirer.prompt([{
          name: 'givenName',
          message: 'Given Name: ',
          validate: Boolean
        }, {
          name: 'familyName',
          message: 'Family Name: ',
          validate: Boolean
        }]))
        .then(({givenName, familyName}) => registerGoogle({
          name: {givenName, familyName},
          primaryEmail: email,
          password: 'insecure'
        }));
    }
  }];

  function registerGoogle(user) {
    google.service
  }

  function authenticateGoogle() {
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/admin-directory_v1-nodejs-quickstart.json
    var SCOPES = ['https://www.googleapis.com/auth/admin.directory.user'];
    var TOKEN_DIR = (process.env.HOME || process.env.HOMEPATH || process.env.USERPROFILE) + '/.credentials/';
    var TOKEN_PATH = TOKEN_DIR + 'admin-directory_v1-nodejs-quickstart.json';


    /**
     * Create an OAuth2 client with the given credentials, and then execute the
     * given callback function.
     *
     * @param {Object} credentials The authorization client credentials.
     * @param {function} callback The callback to call with the authorized client.
     */
    var clientSecret = credentials.installed.client_secret;
    var clientId = credentials.installed.client_id;
    var redirectUrl = credentials.installed.redirect_uris[0];
    var auth = new googleAuth();
    var oauth2Client = new auth.OAuth2(clientId, clientSecret, redirectUrl);

    // Check if we have previously stored a token.
    fs.readFile(TOKEN_PATH, function (err, token) {
      if (err) {
        getNewToken(oauth2Client, callback);
      } else {
        oauth2Client.credentials = JSON.parse(token);
        callback(oauth2Client);
      }
    });
  }

  /**
   * Get and store new token after prompting for user authorization, and then
   * execute the given callback with the authorized OAuth2 client.
   *
   * @param {google.auth.OAuth2} oauth2Client The OAuth2 client to get token for.
   * @param {getEventsCallback} callback The callback to call with the authorized
   *     client.
   */
  function getNewToken(oauth2Client, callback) {
    var authUrl = oauth2Client.generateAuthUrl({
      access_type: 'offline',
      scope: SCOPES
    });
    console.log('Authorize this app by visiting this url: ', authUrl);
    var rl = readline.createInterface({
      input: process.stdin,
      output: process.stdout
    });
    rl.question('Enter the code from that page here: ', function (code) {
      rl.close();
      oauth2Client.getToken(code, function (err, token) {
        if (err) {
          console.log('Error while trying to retrieve access token', err);
          return;
        }
        oauth2Client.credentials = token;
        storeToken(token);
        callback(oauth2Client);
      });
    });
  }

  /**
   * Store token to disk be used in later program executions.
   *
   * @param {Object} token The token to store to disk.
   */
  function storeToken(token) {
    try {
      fs.mkdirSync(TOKEN_DIR);
    } catch (err) {
      if (err.code != 'EEXIST') {
        throw err;
      }
    }
    fs.writeFile(TOKEN_PATH, JSON.stringify(token));
    console.log('Token stored to ' + TOKEN_PATH);
  }

  /**
   * Lists the first 10 users in the domain.
   *
   * @param {google.auth.OAuth2} auth An authorized OAuth2 client.
   */
  function listUsers(auth) {
    var service = google.admin('directory_v1');
    service.users.list({
      auth: auth,
      customer: 'my_customer',
      maxResults: 10,
      orderBy: 'email'
    }, function (err, response) {
      if (err) {
        console.log('The API returned an error: ' + err);
        return;
      }
      var users = response.users;
      if (users.length == 0) {
        console.log('No users in the domain.');
      } else {
        console.log('Users:');
        for (var i = 0; i < users.length; i++) {
          var user = users[i];
          console.log('%s (%s)', user.primaryEmail, user.name.fullName);
        }
      }
    });
  }

  function createEmail() {
  }

  function inviteToGithub() {
  }

  function inviteToPivotal() {
  }

  function inviteToSlack() {
  }
};
