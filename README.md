behat-testingbot
================

Behat-extension for reporting results to TestingBot.

Installation
============

1. Clone this repository.
2. Add the extension-configuration to behat.yml:
    extensions:
        ResultSubmitter\TestingBot\Extension:
            key: Your key from the testingbot-account-page.
            secret: your_secret
