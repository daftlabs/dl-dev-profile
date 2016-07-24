'use strict';
const _ = require('lodash/fp');
const fs = require('fs');
const utils = require('./helpers/utils')();
const program = require('commander');

const COMMANDS_DIR = `${__dirname}/commands`;

program
  .command('*')
  .description('Command missing fallthrough.')
  .action(name => program.parse(process.argv.slice(0, 2).concat('-h')));

utils.promisify(fs.readdir.bind(fs, COMMANDS_DIR))
  .then(_.map.bind(_, file => require(`${COMMANDS_DIR}/${file}`.replace(/\.js$/, ''))()))
  .then(groups => _.reduce((group, commands) => commands.concat(group), [], groups))
  .then(_.each.bind(_, ({command, description, options, action}) => {
    const cmd = program
      .command(command)
      .description(description || command)
      .action(function () {
        try {
          (action || _.noop)
            .apply(action, arguments)
            .then(console.log)
            .catch(handleError);
        } catch (err) {
          handleError(err);
        }
      });
    (options || []).forEach(option => cmd.option(...option));
  }))
  .then(program.parse.bind(program, process.argv));

function handleError(err) {
  console.error(err);
}
