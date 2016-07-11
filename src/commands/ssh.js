'use strict';


module.exports = (config = {}) => {
  const _ = config._ || require('lodash/fp');
  const ecsGateway = config.ecsGateway || require('./../services/ecsGateway')();
  const ec2Gateway = config.ec2Gateway || require('./../services/ec2Gateway')();
  const utils = config.utils || require('./../helpers/utils')();
  const fs = config.fs || require('fs');
  const SSH = config.SSH || require('simple-ssh');
  const vorpal = config.vorpal;

  return [{
    command: ['ssh [project] [environment]', 'Establish SSH connections on all instances of a given project', {}],
    options: [
      ['-u [user]', 'SSH Username'],
      ['-k [keyFile]', 'SSH Key file']
    ],
    action: ({project, environment, options, action}) => {
      const user = options.u || 'ec2-user';
      const keyFile = options.k || `${process.env.HOME}/.ssh/ecs.pem`;
      let service, hosts, sshInstances;
      return ecsGateway.getServiceByName(`${project}-${environment}`)
        .then(res => {
          service = res;
          return ecsGateway.getTasksByService(service);
        })
        .then(tasks => ecsGateway.getContainerInstances(service.clusterArn, _.map(task => task.containerInstanceArn, tasks)))
        .then(instances => ec2Gateway.describeInstances(_.map(instance => instance.ec2InstanceId, instances)))
        .then(nodes => {
          hosts = _.map(node => node.PublicIpAddress, nodes);
          return utils.promisify(fs.readFile.bind(fs, keyFile));
        })
        .then(key => {
          return Promise.all(_.map(
            host => new Promise((resolve, reject) => buildSSHInstance(host, user, key, resolve, reject)),
            hosts
          ))
            .then(instances => {
              sshInstances = instances;
              return action.prompt({name: 'command', message: 'command: ', validate: Boolean});
            })
            .then(({command}) => {
              if (command === 'exit') {
                return 'Exited.'
              }

              sshInstances.forEach(instance => instance.exec(command));
            })
        });
    }
  }];

  function buildSSHInstance(host, user, key, resolve, reject) {
    const sshInstance = new SSH({host, user, key});
    sshInstance.on('ready', resolve);
    sshInstance.on('error', err => {
      reject(err);
      throw new Error(err);
    });

    return sshInstance;
  }
};
