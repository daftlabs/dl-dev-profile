run this

```bash
curl -L https://raw.githubusercontent.com/daftlabs/dl-dev-profile/master/install.sh | bash
```

========================================================================

Get the latest Node and NPM
```bash
rm -rf /usr/local/lib/node_modules
brew uninstall node
brew install node
```

Have you been sudoing when you shouldn't?
```bash
sudo chown -R $(whoami) /usr/local
```
