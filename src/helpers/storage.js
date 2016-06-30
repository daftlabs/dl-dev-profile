'use strict';

const utils = require('./utils');
const fs = require('fs');

module.exports = (storageFile = `${__dirname}/../../.storage.json`) => {
  return {
    get: getKey,
    set: setKey,
    getCurrentProfile
  };

  function getKey(key, def = null) {
    return loadAll()
      .then(data => data.hasOwnProperty(key) ? data[key] : def)
      .catch(err => def);
  }

  function setKey(key, value) {
    return loadAll()
      .then(data => Object.assign({}, data, {[key]: value}))
      .then(storeAll);
  }

  function getCurrentProfile() {
    return getKey('currentProfile')
      .then(name => getKey('profiles', {}).then(profiles => profiles[name]));
  }

  function loadAll() {
    return utils.promisify(fs.readFile.bind(fs, storageFile))
      .then(JSON.parse);
  }

  function storeAll(data) {
    return utils.promisify(fs.writeFile.bind(fs, storageFile, JSON.stringify(data)));
  }
};
