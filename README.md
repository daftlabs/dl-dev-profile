run this

```bash
curl -L https://raw.githubusercontent.com/daftlabs/dl-dev-profile/master/install.sh | bash
```

###Auth Tokens
- Pivotal Token: https://www.pivotaltracker.com/profile
- Slack Token: https://api.slack.com/docs/oauth-test-tokens

========================================================================

Get the latest Node and NPM
```bash
rm -rf /usr/local/lib/node_modules
brew uninstall node
brew install node
brew link --overwrite node
```

Have you been sudoing when you shouldn't?
```bash
sudo chown -R $(whoami) /usr/local
```
