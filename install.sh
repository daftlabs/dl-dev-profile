#!/usr/bin/env bash

mkdir -p ~/.daftlabs
curl -L https://github.com/daftlabs/dl-dev-profile/archive/master.tar.gz | tar -zx -C ~/.daftlabs --strip-components=1

grep -q -F 'source ~/.daftlabs/bash_aliases' ~/.bash_profile || echo 'source ~/.daftlabs/bash_aliases' >> ~/.bash_profile
grep -q -F 'source ~/.daftlabs/bash_prompt' ~/.bash_profile || echo 'source ~/.daftlabs/bash_prompt' >> ~/.bash_profile

touch ~/.daftlabs/config

#install AWS cli
curl "https://s3.amazonaws.com/aws-cli/awscli-bundle.zip" -o "awscli-bundle.zip"
unzip awscli-bundle.zip
sudo ./awscli-bundle/install -i /usr/local/aws -b /usr/local/bin/aws

#install homebrew
/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
#install mysql
brew install mysql

source ~/.bash_profile
