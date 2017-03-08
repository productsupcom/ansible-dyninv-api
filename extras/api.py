#!/usr/bin/env python

"""
REST API Backend external inventory script
==========================================

External inventory using a REST API Backend.

Modify api.ini to match your login credentials.

Extended upon the Cobbler Inventory script.

"""

# Copyright (c) 2017 Productsup GmbH, Yorick Terweijden yt@products-up.de
#
# As it is mostly based on the original Cobbler Dynamic Inventory
# https://github.com/ansible/ansible/blob/devel/contrib/inventory/cobbler.py
# the same license, the GPL-3 applies.
#
######################################################################

import argparse
import ConfigParser
import os
import re
from time import time

try:
    import json
except ImportError:
    import simplejson as json

import requests

class AnsibleDynInv(object):

    def __init__(self):

        """ Main execution path """
        self.inventory = dict()  # A list of groups and the hosts in that group
        self.token = ""

        # Read settings and parse CLI arguments
        self.read_settings()
        self.parse_cli_args()

        # Cache
        if self.args.refresh_cache:
            self.update_cache()
        elif not self.is_cache_valid():
            self.update_cache()
        else:
            self.load_inventory_from_cache()

        data_to_print = ""

        # Data to print
        if self.args.host:
            data_to_print += self.get_host_info()
        else:
            data_to_print += self.json_format_dict(self.inventory, True)

        print(data_to_print)

    def is_cache_valid(self):
        """ Determines if the cache files have expired, or if it is still valid """

        if os.path.isfile(self.cache_path_inventory):
            mod_time = os.path.getmtime(self.cache_path_inventory)
            current_time = time()
            if (mod_time + self.cache_max_age) > current_time:
                if os.path.isfile(self.cache_path_inventory):
                    return True

        return False

    def read_settings(self):
        """ Reads the settings from the api.ini file """

        config = ConfigParser.SafeConfigParser()
        config.read(os.path.dirname(os.path.realpath(__file__)) + '/api.ini')

        self.server = config.get('server', 'server')
        self.email = config.get('server', 'email')
        self.password = config.get('server', 'pass')

        # Cache related
        cache_path = config.get('config', 'cache_path')
        self.cache_path_inventory = cache_path + "/ansible-rest-api.cachefile"
        self.cache_max_age = config.getint('config', 'cache_max_age')

    def parse_cli_args(self):
        """ Command line argument processing """

        parser = argparse.ArgumentParser(description='Produce an Ansible Inventory file based on the REST API Backend')
        parser.add_argument('--list', action='store_true', default=True, help='List instances (default: True)')
        parser.add_argument('--host', action='store', help='Get all the variables about a specific instance')
        parser.add_argument('--refresh-cache', action='store_true', default=False,
                            help='Force refresh of cache by making API requests to the REST API Backend (default: False - use cache files)')
        self.args = parser.parse_args()

    def get_host_info(self):
        """ Get variables about a specific host """

        if not self.inventory or len(self.inventory) == 0:
            # Need to load index from cache
            self.load_inventory_from_cache
        if not self.args.host in self.inventory['_meta']['hostvars']:
            # try updating the cache
            self.update_cache()

            if not self.args.host in self.inventory['_meta']['hostvars']:
            #if not self.args.host in self.inventory:
                # host might not exist anymore
                return self.json_format_dict({}, True)

        return self.json_format_dict(self.inventory['_meta']['hostvars'][self.args.host], True)

    def update_cache(self):
        """ Fetch the Inventory from the REST API Backend """

        self.get_request_token()
        headers = {
            'Accept':'application/json',
            'Authorization': 'Bearer %s' % (self.token)
        }
        resp = requests.get('%s%s' % (self.server, '/api/inventory'), headers=headers)
        if resp.status_code != 200:
            # This means something went wrong.
            raise StandardError('POST /login_check {}'.format(resp.status_code))

        self.inventory = resp.json()
        self.write_to_cache(self.inventory, self.cache_path_inventory)

    def get_request_token(self):
        """ Get the Token needed to request the inventory """

        login_data = {'email': self.email, 'password': self.password}
        login = requests.post('%s%s' % (self.server, '/login_check'),
                              data=login_data,
                              headers={'Accept':'application/json'})
        if login.status_code != 200:
            # This means something went wrong.
            raise StandardError('GET /api/inventory {}'.format(login.status_code))
        self.token = login.json()['token']


    def load_inventory_from_cache(self):
        """ Reads the index from the cache file sets self.index """

        cache = open(self.cache_path_inventory, 'r')
        json_inventory = cache.read()
        self.inventory = json.loads(json_inventory)

    def write_to_cache(self, data, filename):
        """ Writes data in JSON format to a file """
        json_data = self.json_format_dict(data, True)
        cache = open(filename, 'w')
        cache.write(json_data)
        cache.close()

    def to_safe(self, word):
        """ Converts 'bad' characters in a string to underscores so they can be used as Ansible groups """

        return re.sub("[^A-Za-z0-9\-]", "_", word)

    def json_format_dict(self, data, pretty=False):
        """ Converts a dict to a JSON object and dumps it as a formatted string """

        if pretty:
            return json.dumps(data, sort_keys=True, indent=2)
        else:
            return json.dumps(data)

AnsibleDynInv()
