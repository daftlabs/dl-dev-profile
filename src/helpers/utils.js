'use strict';

module.exports = () => {
  return {
    promisify
  };

  function promisify(func) {
    return new Promise((resolve, reject) => func((err, res) => err ? reject(err) : resolve(res)));
  }
};
