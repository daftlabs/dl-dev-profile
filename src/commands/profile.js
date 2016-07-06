'use strict';

module.exports = (config = {}) => {
  const _ = config._ || require('lodash/fp');
  const dataStore = config.dataStore || require('./../services/dataStore')();
  const vorpal = config.vorpal;

  const autocompleteProfileNames = {
    data: () => dataStore.profiles.getAll().then(_.keys)
  };

  [save, use, remove, list].forEach(command => command(vorpal));

  function save(vorpal) {
    vorpal
      .command('profile save <name>', 'Configure an AWS profile.', {})
      .autocomplete(autocompleteProfileNames)
      .action(function ({name}, cb) {
        dataStore.profiles.get(name, {})
          .then(existing => {
            this.prompt([{
              name: 'awsAccessKeyId',
              message: 'AWS Access Key Id: ',
              default: existing.awsAccessKeyId,
              validate: Boolean
            }, {
              name: 'awsSecretAccessKey',
              message: 'AWS Secret Access Key: ',
              default: existing.awsSecretAccessKey,
              validate: Boolean
            }, {
              name: 'githubUsername',
              message: 'GitHub Username: ',
              default: existing.githubUsername,
              validate: Boolean
            }, {
              name: 'githubPassword',
              message: 'GitHub Password: ',
              default: existing.githubPassword,
              validate: Boolean
            }, {
              name: 'pivotalToken',
              message: 'Pivotal API Token: ',
              default: existing.pivotalToken,
              validate: Boolean
            }])
              .then(answers => dataStore.profiles.set(name, answers))
              .then(cb);
          });
      });
  }

  function use(vorpal) {
    vorpal
      .command('profile use <name>', 'Set current AWS profile for use in other commands.', {})
      .autocomplete(autocompleteProfileNames)
      .action(function ({name}, cb) {
        dataStore.profiles.get(name)
          .then(profile => {
            if (!profile) {
              return this.log(`Unknown profile "${name}".`);
            }
            return dataStore.profiles.setCurrent(name)
              .then(vorpal.delimiter(`${name}:`))
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
        dataStore.profiles.getCurrent()
          .then(currentProfile => {
            if (currentProfile !== name) {
              return;
            }
            return dataStore.profiles.setCurrent(null)
              .then(() => console.log(`Current profile cleared.`));
          })
          .then(() => dataStore.profiles.getAll())
          .then(profiles => {
            if (!profiles.hasOwnProperty(name)) {
              return this.log(`Unknown profile "${name}".`);
            }
            return dataStore.profiles.setAll(_.omit([name], profiles))
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
          dataStore.profiles.getAll(),
          dataStore.profiles.getCurrent()
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
              this.log(name === currentProfile.name ? `* ${name}` : name);
              this.log(JSON.stringify(profiles[name], null, 2));
              this.log('');
            });
          })
          .then(cb);
      });
  }
};
