'use strict';
module.exports = (config = {}) => {
  const _ = config._ || require('lodash/fp');
  const AWS = config.AWS || require('aws-sdk');
  const s3Stream = config.s3Stream || require('s3-stream-upload');
  const ecsGateway = config.ecsGateway || require('./ecsGateway')();
  const ec2Gateway = config.ec2Gateway || require('./ec2Gateway')();

  return {
    describeService,
    createUploadStream
  };

  function createUploadStream(bucketName, fileName, {accessKeyId, secretAccessKey}, options) {
    AWS.config.update({accessKeyId, secretAccessKey});
    return s3Stream(new AWS.S3(), _.assign({
      Bucket: bucketName,
      Key: fileName
    }, options));
  }

  function describeService(serviceName) {
    let service, tasks, taskDefinition, instances;
    return ecsGateway.getServiceByName(serviceName)
      .then(res => {
        service = res;
        return ecsGateway.getTasksByService(service);
      })
      .then(res => {
        tasks = res;
        return ecsGateway.getDefinitionByName(service.taskDefinition);
      })
      .then(res => {
        taskDefinition = res;
        return ecsGateway.getContainerInstances(service.clusterArn, _.map(task => task.containerInstanceArn, tasks));
      })
      .then(res => {
        instances = res;
        return ec2Gateway.describeInstances(_.map(instance => instance.ec2InstanceId, instances));
      })
      .then(nodes => {
        return {service, tasks, taskDefinition, instances, nodes};
      });
  }
};
