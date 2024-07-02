# ITKDev Freescout docker

## Prerequisites

* [`go-task`](https://taskfile.dev)
* Docker

## Contribute

* [FreeScout modules](https://github.com/freescout-helpdesk/freescout/wiki/FreeScout-Modules)
* [FreeScout modules development guide](https://github.com/freescout-help-desk/freescout/wiki/Development-Guide)

## Install
    
### Install a specific Freescout distribution version into freescout folder.

I.e.
```shell
task dist:build VERSION=1.8.145
```

### Dev installation

```shell
task dev:start-docker
```

```shell
task dev:install
```

### Server installation

```shell
task server:start-docker
```

```shell
task server:install
```

### Add custom modules

Local development:
```
task dev:install-modules
```

Server: 
```
task server:install-modules
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