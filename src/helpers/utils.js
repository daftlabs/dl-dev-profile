'use strict';

module.exports = () => {
  return {
    promisify,
    forOwn
  };

  function forOwn(cb, obj) {
    let prop;
    for (prop in obj) {
      if (obj.hasOwnProperty(prop)) {
        cb(obj[prop], prop, obj);
      }
    }
    return obj;
  }

  function promisify(func) {
    return new Promise((resolve, reject) => func((err, res) => err ? reject(err) : resolve(res)));
  }
};
