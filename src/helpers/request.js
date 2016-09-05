'use strict';
module.exports = (config = {}) => {
  const request = config.request || require('request');
  const qs = config.querystring || require('querystring');

  return {
    post: makeRequest.bind(null, 'post')
  };

  function makeRequest(method, url, form = {}, headers = {}) {
    if (method === 'get') {
      url += `?${qs.stringify(form)}`;
    }
    return new Promise((resolve, reject) => {
      request[method]({
        url,
        form,
        headers
      }, (err, httpResponse, body) => {
        if (err) {
          reject(err);
        }
        if (httpResponse.statusCode !== 200) {
          reject(new Error(httpResponse.statusMessage));
        }
        resolve(body);
      });
    });
  }
};
