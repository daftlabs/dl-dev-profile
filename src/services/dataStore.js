'use strict';

module.exports = (config = {}) => {
  const fs = config.fs || require('fs');
  const _ = config._ || require('lodash/fp');
  const utils = config.utils || require('./../helpers/utils')();
  const storageFile = config.storageFile || `${__dirname}/../../.storage.json`;

  return {
    profiles: {
      getAll: getKey.bind(null, 'profiles', {}),
      setAll: setKey.bind(null, 'profiles'),
      get: (name, def = null) => getKey('profiles', {}).then(profiles => profiles.hasOwnProperty(name) ? profiles[name] : def),
      set: (name, data) => getKey('profiles', {}).then(profiles => _.assign(profiles, {[name]: data})).then(setKey.bind(null, 'profiles')),
      getCurrent: () => getKey('currentProfile').then(name => getKey('profiles', {}).then(profiles => _.assign(profiles[name], {name}))),
      setCurrent: setKey.bind(null, 'currentProfile')
    }
  };

  function getKey(key, def = null) {
    return loadAll()
      .then(data => data.hasOwnProperty(key) ? data[key] : def)
      .catch(err => def);
  }

  function setKey(key, value) {
    return loadAll()
      .then(data => _.assign(data, {[key]: value}))
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
