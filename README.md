Behat / TestingBot
================

Behat-extension for reporting results to TestingBot.

Installation
============

1. Clone this repository.
2. Add the extension-configuration to behat.yml.

```yaml
    extensions:
        ResultSubmitter\TestingBot\Extension:
            key: some_secret_key
            secret: some_secret
```
