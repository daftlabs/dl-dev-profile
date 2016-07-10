'use strict';

module.exports = (config = {}) => {
  const _ = config._ || require('lodash/fp');
  const dataStore = config.dataStore || require('./../services/dataStore')();
  const vorpal = config.vorpal;

  const autocompleteProfileNames = {
    data: () => dataStore.profiles.getAll().then(_.keys)
  };

  return [{
    command: ['profile save <name>', 'Configure an AWS profile.', {}],
    autocomplete: autocompleteProfileNames,
    action: ({name, action}) => dataStore.profiles.get(name, {})
      .then(existing => action.prompt([{
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
      }]))
      .then(answers => dataStore.profiles.set(name, answers))
  }, {
    command: ['profile use <name>', 'Set current AWS profile for use in other commands.', {}],
    autocomplete: autocompleteProfileNames,
    action: ({name}) => dataStore.profiles.get(name)
      .then(profile => {
        if (!profile) {
          throw new Error(`Unknown profile "${name}".`);
        }
        return dataStore.profiles.setCurrent(name)
          .then(vorpal.delimiter(`${name}:`))
          .then(() => `Current profile set to "${name}".`);
      })
  }, {
    command: ['profile remove <name>', 'Delete an AWS profile.', {}],
    autocomplete: autocompleteProfileNames,
    action: ({name}) => dataStore.profiles.getCurrent()
      .then(currentProfile => {
        if (currentProfile.name === name) {
          return dataStore.profiles.setCurrent(null)
            .then(() => vorpal.delimiter('Daftswag'));
        }
      })
      .then(() => dataStore.profiles.getAll())
      .then(profiles => {
        if (!profiles.hasOwnProperty(name)) {
          throw new Error(`Unknown profile "${name}".`);
        }
        return dataStore.profiles.setAll(_.omit([name], profiles))
          .then(() => `Profile "${name}" deleted.`);
      })
  }, {
    command: ['profile list [name]', 'List all saved AWS profiles.', {}],
    autocomplete: autocompleteProfileNames,
    action: ({name}) => Promise.all([
      dataStore.profiles.getAll(),
      dataStore.profiles.getCurrent()
    ])
      .then(([profiles, currentProfile]) => {
        let profileNames;

        if (name) {
          if (!profiles.hasOwnProperty(name)) {
            throw new Error(`Unknown profile "${name}".`);
          }
          profileNames = [name];
        } else {
          profileNames = _.keys(profiles);
          if (profileNames.length < 1) {
            throw new Error('No saved profiles.');
          }
        }
        return _.reduce((profile, profiles) => profiles.concat(profile), [], _.map(name => {
          return [
            name === currentProfile.name ? `* ${name}` : name,
            JSON.stringify(profiles[name], null, 2),
            ''
          ];
        }, profileNames)).join('\n');
      })
  }];
};
