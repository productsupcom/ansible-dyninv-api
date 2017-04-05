<?php

/***
 * One off migration script for migrating the data from ansible-dyninv-mysql
 * to the new API based system.
 *
 * Ensure the new environment is setup as you like (correct backend etc)
 * and then change information how to connect to the API and DB in the migrate.settings.php
 * The DB only needs read access Be sure that the endpoint is reachable
 * from the location you run the script (whitelisting).
 *
 * (c) Productsup 2017, Yorick Terweijden yt@productsup.com
 */

require_once('migrate.settings.php');

function curlWrap($baseURL, $endpoint, $data, $token = null) {
    $url = sprintf('%s/%s', $baseURL, $endpoint);
    $headers = array(
        'Accept: application/json',
    );
    if (!is_null($token)) {
        $data_string = json_encode($data);
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Content-Length: ' . strlen($data_string);
        $headers[] = 'Authorization: Bearer '.$token;
    } else {
        $data_string = http_build_query($data);
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $headers[] = 'Content-Length: ' . strlen($data_string);
    }
    print 'sent: ';
    var_dump($data_string);
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLINFO_HEADER_OUT, false);
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = json_decode(curl_exec($ch), true);;
    $info = curl_getinfo($ch);

    if ($info['http_code'] == 500 || $info['http_code'] == 400) {
        throw new Exception('Something failed. '.$result['detail']);
    }

    print 'result: ';
    var_dump($result);
    return $result;
}

$mysqli = new mysqli($db['host'], $db['username'], $db['password'], $db['database'], $db['port']);
$apiLogin = curlWrap($api['url'], 'login_check', ['email'=>$api['email'], 'password'=>$api['password']]);
$token = '';
if (isset($apiLogin['message'])) {
    printf("Login failed: %s\nPlease check your configuration.\n", $apiLogin['message']);
    exit(1);
} else {
    $token = $apiLogin['token'];
}

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
        . $mysqli->connect_error);
}

$groups = [];
if ($groupStmt = $mysqli->query('SELECT * FROM `group`;')) {
    printf("Found %d groups\n", $groupStmt->num_rows);

    $groupsRaw = $groupStmt->fetch_all(MYSQLI_ASSOC);
    foreach ($groupsRaw as $group) {
        $groups[$group['id']] = $group;
    }
}

$hosts = [];
if ($hostStmt = $mysqli->query('SELECT * FROM `host` WHERE `enabled` >= 0;')) {
    printf("Found %d hosts\n", $hostStmt->num_rows);

    $hostsRaw = $hostStmt->fetch_all(MYSQLI_ASSOC);
    foreach ($hostsRaw as $host) {
        $hosts[$host['id']] = $host;
    }
}

$childGroups = [];
if ($groupStmt = $mysqli->query('SELECT `child_id`, `parent_id` FROM `childgroups`;')) {
    printf("Found %d childgroups\n", $groupStmt->num_rows);

    $childgroupsRaw = $groupStmt->fetch_all(MYSQLI_ASSOC);
    foreach ($childgroupsRaw as $child) {
        $childGroups[$child['parent_id']]['children'][$child['child_id']] = $child['child_id'];
        //$groups[$child['parent_id']]['children'][$child['child_id']] = $child['child_id'];
    }
    ksort($childGroups);
}

$processedHosts;
// first add all the hosts, fetch their ID's and store them back in the $hosts array
foreach($hosts as $host) {
    $enabled = ($host['enabled']) ? true : false;
    $variables = json_decode($host['variables'], true);
    $create = [
        'hostname' => $host['hostname'],
        'enabled' => $enabled
    ];
    if (count($variables) > 0) {
        $create['variables'] = $variables;
    }
    if (!filter_var($host['host'], FILTER_VALIDATE_IP) === false) {
        $create['ip'] = $host['host'];
    } else {
        $create['fqdn'] = $host['host'];
    }

    try {
        $result = curlWrap($api['url'], 'api/hosts', $create, $token);
    } catch (Exception $e) {
        print $e->getMessage();
        continue;
    }

    $host['new_id'] = $result['id'];
    $processedHosts[$host['id']] = $host;
}

$mapping = [];
if ($mapStmt = $mysqli->query('SELECT `host_id`, `group_id` FROM `hostgroups`;')) {
    printf("Found %d mappings\n", $mapStmt->num_rows);

    $mappingsRaw = $mapStmt->fetch_all(MYSQLI_ASSOC);
    foreach ($mappingsRaw as $map) {
        $mapping[$map['group_id']][] = $map['host_id'];
        //$groups[$map['group_id']]['hosts'][] = $map['host_id'];
//        $hosts[$map['host_id']]['groups'][] = $map['group_id'];
    }
}

// first split the parent groups so we have their ID so they can be processed
// do this by just splitting them from the $groups
$parentGroups = [];

foreach ($groups as $key => $group) {
    if (isset($childGroups[$key])) {
        $group['parent'] = true;
        $parentGroups[$key] = $group;
        unset($groups[$key]);
    }
}

$superGroup = [$groups, $parentGroups];
$processedGroups = [];

foreach ($superGroup as $groups) {
    foreach ($groups as $group) {
        $groupHosts = [];
        if (isset($mapping[$group['id']])) {
            // somehow broke
            // FIXME
            foreach ($mapping[$group['id']] as $hostid) {
                if (!isset($processedHosts[$hostid]['new_id'])) {
                    continue;
                }
                $groupHosts[] = '/api/hosts/'.$processedHosts[$hostid]['new_id'];;
            }
        }
        $enabled = ($group['enabled']) ? true : false;
        $variables = json_decode($group['variables'], true);
        $create = [
            'name' => $group['name'],
            'enabled' => $enabled
        ];
        if (count($variables) > 0) {
            $create['variables'] = $variables;
        }
        if (count($groupHosts) > 0) {
            $create['hosts'] = $groupHosts;
        }

        if (isset($group['parent'])) {
            $children = [];
            foreach ($childGroups[$group['id']]['children'] as $key => $child) {
                $children[] = '/api/groups/'.$processedGroups[$child]['new_id'];
            }
            $create['childGroups'] = $children;
        }

        try {
            $result = curlWrap($api['url'], 'api/groups', $create, $token);
        } catch (Exception $e) {
            print $e->getMessage();
            continue;
        }

        $group['new_id'] = $result['id'];
        $processedGroups[$group['id']] = $group;
    }
}

print 'All done!'.PHP_EOL;
// done
$mysqli->close();
unset($mysqli);
