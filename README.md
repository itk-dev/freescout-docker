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
```dev:install-modules```

Server: ```server:install-modules```