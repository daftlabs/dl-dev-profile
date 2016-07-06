'use strict';

const _ = require('lodash/fp');
const fs = require('fs');
const vorpal = require('vorpal')();
const utils = require('./helpers/utils')();
const COMMANDS_DIR = `${__dirname}/commands`;

utils.promisify(fs.readdir.bind(fs, COMMANDS_DIR))
  .then(_.map.bind(_, file => require(`${COMMANDS_DIR}/${file}`.replace(/\.js$/, ''))({vorpal})));

vorpal
  .delimiter('Daftswag$')
  .show();
