# ITKDev Freescout docker

## Prerequisites

* [`go-task`](https://taskfile.dev)
* Docker

## Contribute

* [FreeScout modules](https://github.com/freescout-helpdesk/freescout/wiki/FreeScout-Modules)
* [FreeScout modules development guide](https://github.com/freescout-help-desk/freescout/wiki/Development-Guide)
## Install
    
### Clone freescout distribution version into freescout folder.

```shell
task dist:clone
```

### Start docker

```shell
task dev:start-docker
```

### Finish installation

To complete the installation open http://freescout.local.itkdev.dk/install and follow the web-installer.
If a .env file exists in the freescout base dir this path returns 403.
