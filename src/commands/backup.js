'use strict';
module.exports = (config = {}) => {
  const _ = config._ || require('lodash/fp');
  const childProcess = config.childProcess || require('child_process');
  const moment = config.moment || require('moment');
  const fs = config.fs || require('fs');
  const ecsGateway = config.ecsGateway || require('./../services/ecsGateway')();
  const utils = config.utils || require('./../helpers/utils')();
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
          const creds = getDBCreds(_.get('environment', _.find(_.partial(names.includes), containerDefinitions)));
          return mysqlDump(`${__dirname}/../../backups/${family}:${revision}-${new Date().getTime()}.sql`, creds);
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

  function mysqlDump(fileName, {host, port, user, pass, name}) {
    return new Promise(function (resolve, reject) {
      const dumpFileStream = fs.createWriteStream(fileName);
      const mysqlDump = childProcess.spawn('mysqldump', [
        `--host=${host}`,
        `--user=${user}`,
        `--password=${pass}`,
        `--port=${port}`,
        '--single-transaction',
        name
      ]);
      mysqlDump.stdout.pipe(dumpFileStream);
      mysqlDump.stdout.on('data', data => console.log(data.toString()));
      mysqlDump.stderr.on('data', data => console.log(data.toString()));
      mysqlDump
        .on('finish', () => resolve(fileName))
        .on('error', reject);
    });
  }
};
