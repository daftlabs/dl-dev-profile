#!/usr/bin/env bash

THERE=$PWD
HERE=~/.daftlabs

#download app
mkdir -p ${HERE}
curl -L https://github.com/daftlabs/dl-dev-profile/archive/master.tar.gz | tar -zx -C ${HERE} --strip-components=1
cd ${HERE}
grep -q -F 'source ~/.daftlabs/bash_profile.sh' ~/.bash_profile || echo 'source ~/.daftlabs/bash_profile.sh' >> ~/.bash_profile

#install latest node and npm
rm -rf /usr/local/lib/node_modules
brew uninstall node
brew install node
brew link --overwrite node

#install dependencies
npm install

#misc
if [ ! -e ${HERE}/.storage.json ]; then
   echo "{}" > ${HERE}/.storage.json
fi

source ~/.bash_profile
cd ${THERE}
