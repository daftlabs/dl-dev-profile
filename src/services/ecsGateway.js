'use strict';
module.exports = (config = {}) => {
  const AWS = config.AWS || require('aws-sdk');
  const _ = config._ || require('lodash/fp');
  const utils = config.utils || require('./../helpers/utils')();
  const dataStore = require('./dataStore')();

  return utils.forOwn((func, key, obj) => {
    obj[key] = function () {
      var args = Array.prototype.slice.call(arguments);

      return getClient()
        .then(ecs => {
          args.unshift(ecs);
          return func.apply(func, args);
        });
    };
  }, {
    getServiceByName,
    getDefinitionByName,
    getDefinitionByService,
    getTasksByService,
    getContainerInstances,
  });

  function getClient() {
    return dataStore.profiles.getCurrent()
      .then(profile => {
        return new AWS.ECS({
          accessKeyId: profile.awsAccessKeyId,
          secretAccessKey: profile.awsSecretAccessKey,
          region: 'us-east-1'
        });
      });
  }

  function getServiceByName(ecs, name) {
    return listClusters(ecs)
      .then(clusters => {
        let serviceQueries = [];
        clusters.forEach(cluster => {
          const query = utils.promisify(ecs.describeServices.bind(ecs, {
            services: [name],
            cluster
          })).catch(null);
          serviceQueries.push(query);
        });
        return Promise.all(serviceQueries)
      })
      .then(services => {
        const service = _.flow(
          _.reduce((carry, res) => carry.concat(res.services), []),
          _.filter({status: 'ACTIVE'}),
          _.head,
          _.omit(['events'])
        )(services);
        if (service.serviceName !== name) {
          throw new Error(`Service "${name}" not found.`);
        }
        return service;
      });
  }

  function getTasksByService(ecs, service) {
    return utils
      .promisify(ecs.listTasks.bind(ecs, {
        cluster: service.clusterArn,
        serviceName: service.serviceName
      }))
      .then(({taskArns}) => utils.promisify(ecs.describeTasks.bind(ecs, {
        cluster: service.clusterArn,
        tasks: taskArns
      })))
      .then(res => res.tasks);
  }

  function getDefinitionByService(ecs, service) {
    return getDefinitionByName(arnToName(service.taskDefinition), ecs)
      .then(task => _.head(_.filter({name: service.serviceName}, task.taskDefinition.containerDefinitions)));
  }

  function getDefinitionByName(ecs, taskDefinition) {
    return utils.promisify(ecs.describeTaskDefinition.bind(ecs, {taskDefinition}))
      .then(res => res.taskDefinition);
  }

  function listClusters(ecs) {
    return utils.promisify(ecs.listClusters.bind(ecs))
      .then(res => _.map(arnToName, res.clusterArns));
  }

  function getContainerInstances(ecs, clusterArn, containerInstances) {
    return utils.promisify(ecs.describeContainerInstances.bind(ecs, {
      cluster: arnToName(clusterArn),
      containerInstances
    }))
      .then(res => res.containerInstances);
  }

  function arnToName(arn) {
    return _.last(arn.split('/'));
  }
};
