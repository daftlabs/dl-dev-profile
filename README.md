Get the latest Node and NPM
```
rm -rf /usr/local/lib/node_modules
brew uninstall node
brew install node --without-npm
echo prefix=~/.npm-packages >> ~/.npmrc
curl -L https://www.npmjs.com/install.sh | sh
```

Have you been sudoing when you shouldn't?
```
sudo chown -R $(whoami) /usr/local
```
