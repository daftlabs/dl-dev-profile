'use strict';
module.exports = (config = {}) => {
  const _ = config._ || require('lodash/fp');
  const awsGateway = config.awsGateway || require('./../services/awsGateway')();

  return [{
    command: 'describe <project> <environment>',
    description: 'Describe currently deployed project.',
    options: [
      ['-e, --events [n]', 'include n events', 3]
    ],
    action: (project, environment, command) => awsGateway
      .describeService(`${project}-${environment}`)
      .then(({service, tasks, taskDefinition, nodes}) => {
        const environment = {};
        const names = [taskDefinition.family, 'web', project];
        (_.get('environment', _.find(_.partial(names.includes), taskDefinition.containerDefinitions)) || [])
          .forEach(({name, value}) => environment[name] = value);
        return JSON.stringify({
          service: {
            deployments: _.map(
              _.pick(['desiredCount', 'runningCount', 'pendingCount', 'createdAt']),
              service.deployments
            ),
            events: _.map(({createdAt, message}) => {
              return {[createdAt]: message};
            }, service.events.slice(0, command.events))
          },
          tasks: tasks.length,
          taskDefinition: {
            revision: taskDefinition.revision,
            containers: _.map(_.pick(['name', 'image']), taskDefinition.containerDefinitions),
            environment
          },
          nodes: _.map(_.pick(['PublicIpAddress']), nodes)
        }, null, 2);
      })
  }];
};
