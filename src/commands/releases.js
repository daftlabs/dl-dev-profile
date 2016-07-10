'use strict';


module.exports = (config = {}) => {
  const _ = config._ || require('lodash/fp');
  const buildGitHubGateway = config.buildGithubAPI || require('./../services/gitHubGateway');

  const autocompleteRepositories = {
    data: () => buildGitHubGateway().listRepositories().then(_.map.bind(_, repo => repo.full_name))
  };

  return [{
    command: ['list-releases [project]', 'Show a sorted list of release tags.', {}],
    autocomplete: autocompleteRepositories,
    action: ({project}, cb) => buildGitHubGateway()
      .listTags(project)
      .then(tags => JSON.stringify(tags, null, 2))
  }];
};
