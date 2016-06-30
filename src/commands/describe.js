'use strict';


module.exports = (config = {}) => {
  const ecsGateway = config.ecsGateway || require('./../services/ecsGateway')();
  const vorpal = config.vorpal;

  vorpal
    .command('describe [project] [environment]', 'Describe currently deployed project.', {})
    .action(function ({project, environment}, cb) {
      ecsGateway.getServiceByName(`${project}-${environment}`)
        .then(service => console.log(JSON.stringify({service}, null, 2)))
        .then(cb);
    });
};
