'use strict';

const utils = require('./utils');
const fs = require('fs');

module.exports = (storageFile = `${__dirname}/../../.storage.json`) => {
  return {
    get: (key, def = null) => loadAll().then(data => data.hasOwnProperty(key) ? data[key] : def).catch(err => def),
    set: (key, value) => loadAll().then(data => Object.assign({}, data, {[key]: value})).then(storeAll)
  };

  function loadAll() {
    return utils.promisify(fs.readFile.bind(fs, storageFile))
      .then(JSON.parse);
  }

  function storeAll(data) {
    return utils.promisify(fs.writeFile.bind(fs, storageFile, JSON.stringify(data)));
  }
};
