'use strict';


module.exports = (config = {}) => {
  const _ = config._ || require('lodash/fp');
  const buildGitHubGateway = config.buildGithubAPI || require('./../services/gitHubGateway');

  return [{
    command: 'list-releases [project]',
    description: 'Show a sorted list of release tags.',
    action: project => buildGitHubGateway()
      .listTags(project)
      .then(tags => JSON.stringify(tags, null, 2))
  }];
};
