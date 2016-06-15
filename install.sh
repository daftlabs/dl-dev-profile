#!/usr/bin/env bash

#download app
mkdir -p ~/.daftlabs
#curl -L https://github.com/daftlabs/dl-dev-profile/archive/master.tar.gz | tar -zx -C ~/.daftlabs --strip-components=1
touch ~/.daftlabs/misc.cfg
grep -q -F 'source ~/.daftlabs/bash_profile.sh' ~/.bash_profile || echo 'source ~/.daftlabs/bash_profile.sh' >> ~/.bash_profile
curl -sS https://getcomposer.org/installer | php
./composer.phar install

#initialize client
/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
brew install mysql

source ~/.bash_profile
