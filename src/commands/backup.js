'use strict';
module.exports = (config = {}) => {
  const _ = config._ || require('lodash/fp');
  const childProcess = config.childProcess || require('child_process');
  const moment = config.moment || require('moment');
  const ecsGateway = config.ecsGateway || require('./../services/ecsGateway')();
  const awsGateway = config.awsGateway || require('./../services/awsGateway')();
  const utils = config.utils || require('./../helpers/utils')();
  const dataStore = config.dataStore || require('./../services/dataStore')();
  const MYSQL_CREDS_MAP = {
    host: ['DB_HOST', 'MYSQL_HOST'],
    port: ['DB_PORT', 'MYSQL_PORT'],
    user: ['DB_USER', 'DB_USERNAME', 'MYSQL_USER'],
    pass: ['DB_PASS', 'DB_PASSWORD', 'MYSQL_PASSWORD'],
    name: ['DB_NAME', 'DB_DATABASE', 'MYSQL_DATABASE']
  };

  return [{
    command: 'backup [project] [environment]',
    description: "Create a MySQL backup of a project's db.",
    action: (project, environment) => {
      return ecsGateway
        .getServiceByName(`${project}-${environment}`)
        .then(service => ecsGateway.getDefinitionByName(service.taskDefinition))
        .then(({family, revision, containerDefinitions}) => {
          const names = [family, 'web', project];
          const container = _.find(_.partial(names.includes), containerDefinitions);
          const version = container.image.split(':').pop();
          return mysqlDump(
            `${project}-backups`,
            `${family}:${revision}-${version}-${new Date().getTime()}.sql`,
            getDBCreds(container.environment)
          );
        })
        .then(filePath => `Successfully backed up ${project}-${environment} to ${filePath}`)
    }
  }];

  function getDBCreds(envVars = []) {
    const creds = {port: 3306};
    for (let prop in MYSQL_CREDS_MAP) {
      if (MYSQL_CREDS_MAP.hasOwnProperty(prop)) {
        envVars.forEach(({name, value}) => {
          if (MYSQL_CREDS_MAP[prop].includes(name)) {
            creds[prop] = value;
          }
        });
      }
    }
    if (_.size(creds) < _.size(MYSQL_CREDS_MAP)) {
      throw new Error(`Missing DB ${_.difference(_.keys(MYSQL_CREDS_MAP), _.keys(creds)).join(', ')}`);
    }
    return creds;
  }

  function mysqlDump(Bucket, fileName, {host, port, user, pass, name}) {
    return dataStore.profiles
      .getCurrent()
      .then(({awsAccessKeyId, awsSecretAccessKey}) => {
        return awsGateway.createUploadStream(Bucket, fileName, {
          accessKeyId: awsAccessKeyId,
          secretAccessKey: awsSecretAccessKey,
        });
      })
      .then(s3 => {
        return new Promise((resolve, reject) => {
          const mysqlDump = childProcess.spawn('mysqldump', [
            `--host=${host}`,
            `--user=${user}`,
            `--password=${pass}`,
            `--port=${port}`,
            '--single-transaction',
            name
          ]);
          mysqlDump.stdout.pipe(s3);
          mysqlDump.stdout.on('data', data => console.log(data.toString()));
          mysqlDump.stderr.on('data', data => console.log(data.toString()));
          mysqlDump
            .on('finish', () => resolve(fileName))
            .on('error', reject);
        });
      });
  }
};
