Ansible Dyninv API
==================

## Installation

This project is based on [api-platform][https://api-platform.com] so can be easily extended. It requires PHP7.1, so make sure it's installed.
After that the following is needed:

```
$ mkdir -p var/jwt # For Symfony3+, no need of the -p option
$ openssl genrsa -out var/jwt/private.pem -aes256 4096
$ openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
$ composer install
```

Ensure that your database settings are as you want (by default it's also configured for _sqlite3_, checkout the `app/config/config.yml` for options.

Also make sure your Webserver can read/write to the cache/session etc:

```
$ chown -R www-data:www-data app/data       # dir
$ chown www-data:www-data app/data/data.db3 # sqlite3 file
$ chown -R www-data:www-data var/cache      # cache
$ chown -R www-data:www-data var/logs       # logs
$ chown -R www-data:www-data var/sessions   # sessions
```
