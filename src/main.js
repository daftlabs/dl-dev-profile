'use strict';

const _ = require('lodash/fp');
const fs = require('fs');
const vorpal = require('vorpal')();
const utils = require('./helpers/utils')();
const COMMANDS_DIR = `${__dirname}/commands`;
const dataStore = require('./services/dataStore')();

utils.promisify(fs.readdir.bind(fs, COMMANDS_DIR))
  .then(_.map.bind(_, file => require(`${COMMANDS_DIR}/${file}`.replace(/\.js$/, ''))({vorpal})))
  .then(groups => _.reduce((group, commands) => commands.concat(group), [], groups)
    .forEach(command => {
      const cmd = vorpal.command(command.command);
      (command.options || []).forEach(args => cmd.option.apply(cmd, args));
      return cmd.autocomplete(command.autocomplete)
        .action(function (args, cb) {
          return command.action(_.merge(args, {action: this}))
            .then(res => res ? console.log(res) : console.error('No response defined.'))
            .then(cb)
            .catch(err => console.error(err.stack))
        })
    }));

dataStore.profiles
  .getCurrent()
  .then(({name}) => vorpal.delimiter(`${name || 'daftswag'}:`).show());
