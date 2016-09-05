'use strict';
module.exports = (config = {}) => {
  const _ = config._ || require('lodash/fp');
  const request = config.request || require('./../helpers/request')();
  const dataStore = config.dataStore || require('./../services/dataStore')();

  return [{
    command: 'register <email> <githubUsername> <pivotalProjectId>',
    description: "Register a DL employee.",
    action: (email, githubUsername, pivotalProjectId) => {
      let profile;
      return dataStore.profiles.getCurrent()
        .then(currentProfile => {
          profile = currentProfile;
          return registerSlack(email, profile.slackToken);
        })
        .then(() => registerPivotal(pivotalProjectId, email, profile.pivotalToken))
        .then(() => registerGitHub(githubUsername, profile.githubUsername, profile.githubPassword));
    }
  }];

  function registerSlack(email, token) {
    return request.post('https://daftlabs.slack.com/api/users.admin.invite', {
      set_active: true,
      email,
      token
    });
  }

  function registerPivotal(projectId, email, token) {
    return request.post(`https://www.pivotaltracker.com/services/v5/projects/${projectId}/memberships`, {
      role: 'member',
      name: _.head(email.split('@')),
      initials: email.substr(0, 3),
      email
    }, {
      'X-TrackerToken': token,
      'Content-Type': 'application/json'
    });
  }

  function registerGitHub(username, authUser, authPass) {
    const GITHUB_ENGINEERS = 894740;
    const token = [authUser, authPass].join(':');
    return request.put(`https://api.github.com/teams/${GITHUB_ENGINEERS}/memberships/${username}`, {}, {
      Authorization: 'Basic ' + new Buffer(token).toString('base64')
    });
  }
};
