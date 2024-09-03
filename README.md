# CodeIgniter Vercel App

## Table of Contents

- [Overview](#overview)
- [Live Site](#live-site)
- [Features](#features)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Using Makefile](#using-makefile)
- [Environment Variables](#environment-variables)
- [Deployment](#deployment)
- [Accessing Services](#accessing-services)
- [Troubleshooting](#troubleshooting)
- [License](#license)

## Overview

This project is a Dockerized CodeIgniter 3 application designed to run on Vercel. It utilizes PostgreSQL as its database and provides a set of Docker Compose commands for managing the development environment.

## Live Site

You can view a live demo of this application [here](https://codeigniter-vercel-pi.vercel.app/).

## Features

- Dockerized environment for PHP and PostgreSQL
- Easily deployable on Vercel
- Managed with Docker Compose and Makefile
- Environment variable support
- PostgreSQL database integration

## Prerequisites

- Docker
- Docker Compose

## Installation

To get started with this project, clone the repository and navigate to the project directory.

```bash
git clone <repository-url>
cd <repository-directory>
```

## Configuration

Ensure you have the necessary environment variables configured in your `.env` file. or copy from the `.env_sample` Here is an example:

```env
# Database configuration
POSTGRES_DB=codeigniter
POSTGRES_USER=codeigniter_user
POSTGRES_PASSWORD=codeigniter_password

# Additional configuration
POSTGRES_HOST=host.docker.internal
POSTGRES_PORT=5433

# Application-specific environment variables
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost
```

## Using Makefile

The project includes a `Makefile` to simplify common Docker Compose tasks. Below is a list of available commands:

### Start Docker containers

```bash
make up
```

This command starts the PHP and PostgreSQL containers in detached mode.

### Stop Docker containers

```bash
make down
```

This command stops and removes the running containers.

### Access the PHP container's shell

```bash
make shell
```

This command opens a Bash shell in the PHP container.

### Run Composer install

```bash
make composer-install
```

This command installs PHP dependencies using Composer inside the PHP container.

### Run Composer update

```bash
make composer-update
```

### Run Add migration

```bash
make generate-migration name=add-users-table
```

### Migrate to the latest

```bash
make migrate
```

This command updates PHP dependencies using Composer inside the PHP container.

### View logs

```bash
make logs
```

This command displays the logs from the Docker containers.

### Restart services

```bash
make restart
```

This command restarts the Docker containers.

### Access the PostgreSQL container's shell

```bash
make postgres-shell
```

This command opens a Bash shell in the PostgreSQL container.

## Environment Variables

Make sure to set up a `.env` file with your database configuration and other environment-specific settings.

## Deployment

To deploy this application to Vercel, follow these steps:

Install Vercel CLI

```bash
npm install -g vercel
```

Login to your Vercel account

```bash
vercel login
```

Configure `vercel.json` as follows:

```json
{
  "version": 2,
  "builds": [
    { "src": "api/*.php", "use": "vercel-php@0.7.1" },
    { "src": "/user_guide/**", "use": "@vercel/static" }
  ],
  "routes": [
    { "src": "/(.*)", "dest": "/api/index.php" },
    { "src": "/user_guide/(.*)", "dest": "/user_guide/$1/$2/" }
  ]
}
```

This configuration will ensure that all your PHP files under api/ are deployed and processed correctly, and all incoming requests are routed to api/index.php

Deploy to Vercel

```bash
vercel --prod
```

## Accessing Services

After starting the Docker containers, you can access the following services:

- **PHP application**: http://localhost:8081 (or the port you configured)
- **PostgreSQL database**: Accessible via `psql` or a database client using the credentials provided in the `.env` file.

## Troubleshooting

- **Container Issues**: If you encounter conflicts with container names, ensure you stop any existing containers that might be using the same names or ports.
- **Environment Variables**: Make sure your `.env` file is correctly set up with all required variables.
- **Vercel Deployment**: Ensure your build files are in the correct directory and that your `vercel.json` is configured properly.
- **Vercel Error**: try to change the Node.js Version to 18.x to fix this issue

## License

This project is licensed under the MIT License.
