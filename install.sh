mkdir -p ~/.daftlabs
curl -L https://github.com/daftlabs/dl-dev-profile/archive/master.tar.gz | tar -zx -C ~/.daftlabs --strip-components=1

grep -q -F 'source ~/.daftlabs/bash_aliases' ~/.bash_profile || echo 'source ~/.daftlabs/bash_aliases' >> ~/.bash_profile
grep -q -F 'source ~/.daftlabs/bash_prompt' ~/.bash_profile || echo 'source ~/.daftlabs/bash_prompt' >> ~/.bash_profile

touch ~/.daftlabs/config

source ~/.bash_profile
