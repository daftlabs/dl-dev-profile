#!/usr/bin/env bash

THERE=$PWD

#download app
mkdir -p ~/.daftlabs
#curl -L https://github.com/daftlabs/dl-dev-profile/archive/master.tar.gz | tar -zx -C ~/.daftlabs --strip-components=1
cd ~/.daftlabs
grep -q -F 'source ~/.daftlabs/bash_profile.sh' ~/.bash_profile || echo 'source ~/.daftlabs/bash_profile.sh' >> ~/.bash_profile

#install latest node and npm
rm -rf /usr/local/lib/node_modules
brew uninstall node
brew install node

#install dependencies
npm install

source ~/.bash_profile
cd $THERE
