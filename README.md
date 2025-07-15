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

## Template overrides

> Is it possible to do template overrides (in Freescout, Ed.)?
> Nope.
> [<https://github.com/freescout-help-desk/freescout/issues/1045>]

Overriding views (templates) in Freescout is not easy, but it can be done. Modules typically use templates from two
locations (in order of priority):

1. `resources/views/modules/«module alias»`
2. `Modules/EndUserPortal/«module name»/views/`

As an example, the `EndUserPortal` module will first look for a template in `resources/views/modules/enduserportal` and
then, if no template found, in `Modules/EndUserPortal/EndUserPortal/views/`.

This means that we can surgically insert customized templates using [Docker compose
volumes](https://docs.docker.com/reference/compose-file/volumes/):

``` yaml
services:
  phpfpm:
    volumes:
      - ./freescout-resources/views/modules/enduserportal/emails/login.blade.php:/app/resources/views/modules/enduserportal/emails/login.blade.php
      - ./freescout-resources/views/modules/enduserportal/login.blade.php:/app/resources/views/modules/enduserportal/login.blade.php
      - ./freescout-resources/views/modules/enduserportal/partials/submit_form.blade.php:/app/resources/views/modules/enduserportal/partials/submit_form.blade.php
```

Freescout will now use our custom templates on `/help/…/auth` and in the email sent to the customer.
