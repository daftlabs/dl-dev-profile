'use strict';


module.exports = (config = {}) => {
  const _ = config._ || require('lodash/fp');
  const ecsGateway = config.ecsGateway || require('./../services/ecsGateway')();
  const ec2Gateway = config.ec2Gateway || require('./../services/ec2Gateway')();
  const vorpal = config.vorpal;

  vorpal
    .command('describe [project] [environment]', 'Describe currently deployed project.', {})
    .action(function ({project, environment}, cb) {
      let service, tasks, instances;
      ecsGateway.getServiceByName(`${project}-${environment}`)
        .then(res => {
          service = res;
          return ecsGateway.getTasksByService(service);
        })
        .then(res => {
          tasks = res;
          return ecsGateway.getContainerInstances(service.clusterArn, _.map(task => task.containerInstanceArn, tasks));
        })
        .then(res => {
          instances = res;
          return ec2Gateway.describeInstances(_.map(instance => instance.ec2InstanceId, instances));
        })
        .then(nodes => {
          this.log(JSON.stringify({service, tasks, instances, nodes}, null, 2));
        })
        .then(cb);
    });
};
