'use strict';
module.exports = (config = {}) => {
  const _ = config._ || require('lodash/fp');
  const github = config.github || require('octonode');
  const utils = config.utils || require('./../helpers/utils')();
  const dataStore = require('./dataStore')();

  return {
    listTags: name => getClient().then(client => listTags(client.repo(name)))
  };

  function listTags(ghrepo) {
    return utils.promisify(ghrepo.tags.bind(ghrepo))
      .then(_.map.bind(_, _.omit(['zipball_url', 'tarball_url'])))
      .then(_.map.bind(_, tag => Object.assign({name: tag.name}, tag.commit)))
      .then(_.filter.bind(_, tag => tag.name.match(/v[0-9.]+/)))
      .then(_.reverse);
  }

  function getClient() {
    return dataStore.profiles.getCurrent()
      .then(currentProfile => {
        return github.client({
          username: currentProfile.githubUsername,
          password: currentProfile.githubPassword
        });
      });
  }
};
