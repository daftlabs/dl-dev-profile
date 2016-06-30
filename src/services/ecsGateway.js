'use strict';

module.exports = (config = {}) => {
  const AWS = config.AWS || require('aws-sdk');
  const _ = config._ || require('lodash/fp');
  const utils = config.utils || require('./../helpers/utils')();
  const dataStore = require('./dataStore')();

  return {
    getServiceByName: name => getClient().then(getServiceByName.bind(null, name)),
    getDefinitionByName: name => getClient().then(getDefinitionByName.bind(null, name)),
    getDefinitionByService: name => getClient().then(getDefinitionByService.bind(null, name))
  };

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

  function getServiceByName(name, ecs) {
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
        if (!service) {
          throw new Error(`Service "${name}" not found.`);
        }
        return service;
      });
  }

  function getDefinitionByService(service, ecs) {
    return getDefinitionByName(arnToName(service.taskDefinition), ecs)
      .then(task => _.head(_.filter({name: service.serviceName}, task.taskDefinition.containerDefinitions)));
  }

  function getDefinitionByName(taskDefinition, ecs) {
    return utils.promisify(ecs.describeTaskDefinition.bind(ecs, {taskDefinition}));
  }

  function listClusters(ecs) {
    return utils.promisify(ecs.listClusters.bind(ecs))
      .then(res => _.map(arnToName, res.clusterArns));
  }

  function arnToName(arn) {
    return _.last(arn.split('/'));
  }
};
