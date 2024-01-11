# Forge Review Apps

This CLI tools makes manage your review apps via Forge a breeze! This tool has two commands:

1. `deploy-site`:
 - Creates the site if it does not already exist
 - Creates the database if it does not already exist
 - Deploys the site
2. `delete-site`:
 - Deletes the site
 - Deletes the database

## Installation

```
composer global require springloadedco/forge-review-apps
```

## Setup

You'll need to set a few environment variables via the `.env` file:

```
FORGE_CLI_TOKEN=
FORGE_SERVER_ID=
FORGE_ROOT_DOMAIN=
FORGE_GIT_PROVIDER=
FORGE_APP_REPOSITORY=
```

The `FORGE_ROOT_DOMAIN` should be the root domain without any leading periods, ie: `example.com`
