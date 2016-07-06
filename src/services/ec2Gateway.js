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
    describeInstances
  });

  function getClient() {
    return dataStore.profiles.getCurrent()
      .then(profile => {
        return new AWS.EC2({
          accessKeyId: profile.awsAccessKeyId,
          secretAccessKey: profile.awsSecretAccessKey,
          region: 'us-east-1'
        });
      });
  }

  function describeInstances(ec2, InstanceIds) {
    return utils.promisify(ec2.describeInstances.bind(ec2, {InstanceIds}))
      .then(res => _.reduce((group, instances) => instances.concat(group), [], _.map(res => res.Instances, res.Reservations)));
  }
};
