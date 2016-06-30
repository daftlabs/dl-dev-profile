'use strict';


module.exports = (config = {}) => {
  const _ = config._ || require('lodash/fp');
  const buildGithubAPI = config.buildGithubAPI || require('./../services/github');
  const vorpal = config.vorpal;

  const autocompleteRepositories = {
    data: () => buildGithubAPI().listRepositories().then(_.map.bind(_, repo => repo.full_name))
  };

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
