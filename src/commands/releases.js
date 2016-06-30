'use strict';

const _ = require('lodash/fp');
const storage = require('./../helpers/storage')();
const buildGithubAPI = require('./../services/github');

const autocompleteRepositories = {
  data: () => buildGithubAPI().listRepositories().then(_.map.bind(_, repo => repo.full_name))
};

module.exports = vorpal => {
  vorpal
    .command('list-releases [project]', 'Show a sorted list of release tags.', {})
    .autocomplete(autocompleteRepositories)
    .action(function ({project}, cb) {
      buildGithubAPI()
        .listTags(project)
        .then(tags => console.log(JSON.stringify(tags, null, 2)))
        .then(cb)
        .catch(err => {
          console.error(err);
          cb();
        });
    });
};
