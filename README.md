Ansible Dyninv API
==================

## Alpha state
This project is still in Alpha status, use at your own risk. Pull requests are more then welcome.

## Installation

This project is based on [api-platform](https://api-platform.com) so can be easily extended. It requires PHP7.1, so make sure it's installed.
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

## UI

The UI is provided seperately, you can find it here: [ansible-dyninv-api-ui](https://github.com/productsupcom/ansible-dyninv-api-ui)

## Dynamic Inventory Script

To use the script with Ansible you will need to use a special dynamic inventory script.
This has been provided along with the API, you can find it in `extras`.

To get started rename `api.ini.dist` to `api.ini` and modify to your needs based on what you configured for the REST API above.

### Usage
Simply call the script like the following

```
ansible-playbook -i api.py
# or
ansible -i api.py
```

Limitations also work

```
ansible-playbook -i api.py --limit foo.bar.com
ansible-playbook -i api.py --limit groupFoo
```

# LICENSE
Initial work is based on [api-platform](https://api-platform.com), the License from there applies.

Everything else (`src/AppBundle/`) is MIT license Copyright (c) 2017 Products Up GmbH, Yorick Terweijden yt@productsup.com
