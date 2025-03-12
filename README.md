# ITKDev Freescout docker

## Prerequisites

* [`go-task`](https://taskfile.dev)
* Docker

## Contribute

* [FreeScout modules](https://github.com/freescout-helpdesk/freescout/wiki/FreeScout-Modules)
* [FreeScout modules development guide](https://github.com/freescout-help-desk/freescout/wiki/Development-Guide)

## Local development setup

Before installation create a .task.env file with the following line:

```shell
TASK_DOCKER_COMPOSE=itkdev-docker-compose
```

## Install

Either run the full build & installation from scratch og do it step by step

### 1) First option

```shell
task build-from-scratch
```

### 2) Second option
    
#### Build the Freescout distribution version defined in .env.

```shell
task dist:build
```

#### Start docker

```shell
task start-docker
```

#### Installing Freescout

```shell
task install
```

#### Add and install custom modules

```shell
task fetch-modules
task install-modules
```

## Set up supervisor to work the queues
Add WORK_QUEUES variable to .env.docker.local

To get the correct value run
```
itkdev-docker-compose exec phpfpm php artisan schedule:run
```
The value after: `usr/bin/php8.2' 'artisan' queue:work --queue=...` is the WORK_QUEUES value we are looking for.
Something similar to :
```
WORK_QUEUES="emails,default,..."
```

## Updating

### Updating core
Set new core version release in .env then run

```shell
task dist:update
```

### Updating contrib modules
@todo Come up with something smarter.

Contributed modules can be updated through the UI in the extensions list.

### Updating custom modules
@todo Come up with something smarter.

Use git to pull the updated code.