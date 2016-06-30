'use strict';

module.exports = (config = {}) => {
  const _ = config._ || require('lodash/fp');
  const dataStore = config.dataStore || require('./../services/dataStore')();
  const vorpal = config.vorpal;

  const autocompleteProfileNames = {
    data: () => getProfiles().then(_.keys)
  };

  [save, use, remove, list].forEach(command => command(vorpal));

  function save(vorpal) {
    vorpal
      .command('profile save <name>', 'Configure an AWS profile.', {})
      .autocomplete(autocompleteProfileNames)
      .action(function ({name}, cb) {
        getProfiles()
          .then(profiles => {
            const defaultProfile = profiles[name] || {};
            this.prompt([{
              name: 'awsAccessKeyId',
              message: 'AWS Access Key Id: ',
              default: defaultProfile.awsAccessKeyId,
              validate: Boolean
            }, {
              name: 'awsSecretAccessKey',
              message: 'AWS Secret Access Key: ',
              default: defaultProfile.awsSecretAccessKey,
              validate: Boolean
            }, {
              name: 'githubUsername',
              message: 'GitHub Username: ',
              default: defaultProfile.githubUsername,
              validate: Boolean
            }, {
              name: 'githubPassword',
              message: 'GitHub Password: ',
              default: defaultProfile.githubPassword,
              validate: Boolean
            }, {
              name: 'pivotalToken',
              message: 'Pivotal API Token: ',
              default: defaultProfile.pivotalToken,
              validate: Boolean
            }])
              .then(answers => dataStore.set('profiles', Object.assign({}, profiles, {[name]: answers})))
              .then(cb);
          });
      });
  }

  function use(vorpal) {
    vorpal
      .command('profile use <name>', 'Set current AWS profile for use in other commands.', {})
      .autocomplete(autocompleteProfileNames)
      .action(function ({name}, cb) {
        getProfiles()
          .then(profiles => {
            if (!profiles.hasOwnProperty(name)) {
              return this.log(`Unknown profile "${name}".`);
            }
            return dataStore.set('currentProfile', name)
              .then(() => console.log(`Current profile set to "${name}".`));
          })
          .then(cb);
      });
  }

  function remove(vorpal) {
    vorpal
      .command('profile remove <name>', 'Delete an AWS profile.', {})
      .autocomplete(autocompleteProfileNames)
      .action(function ({name}, cb) {
        dataStore.get('currentProfile')
          .then(currentProfile => {
            if (currentProfile !== name) {
              return;
            }
            return dataStore.set('currentProfile', null)
              .then(() => console.log(`Current profile cleared.`));
          })
          .then(() => getProfiles())
          .then(profiles => {
            if (!profiles.hasOwnProperty(name)) {
              return this.log(`Unknown profile "${name}".`);
            }
            return dataStore.set('profiles', _.omit([name], profiles))
              .then(() => console.log(`Profile "${name}" deleted.`));
          })
          .then(cb);
      });
  }

  function list(vorpal) {
    vorpal
      .command('profile list [name]', 'List all saved AWS profiles.', {})
      .autocomplete(autocompleteProfileNames)
      .action(function ({name}, cb) {
        Promise.all([
          getProfiles(),
          dataStore.get('currentProfile', {})
        ])
          .then(([profiles, currentProfile]) => {
            let profileNames;

            if (name) {
              if (!profiles.hasOwnProperty(name)) {
                return this.log(`Unknown profile "${name}".`);
              }
              profileNames = [name];
            } else {
              profileNames = _.keys(profiles);
              if (profileNames.length < 1) {
                return this.log('No saved profiles.');
              }
            }
            return profileNames.forEach(name => {
              this.log(name === currentProfile ? `* ${name}` : name);
              this.log(JSON.stringify(profiles[name], null, 2));
              this.log('');
            });
          })
          .then(cb);
      });
  }

  function getProfiles() {
    return dataStore.get('profiles', {});
  }
};
