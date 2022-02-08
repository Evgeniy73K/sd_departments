<?php
use Illuminate\Support\Collection;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\ProfileDataTypes;
use Tygh\Enum\ProfileFieldLocations;
use Tygh\Enum\UsergroupLinkStatuses;
use Tygh\Enum\UsergroupStatuses;
use Tygh\Enum\UsergroupTypes;
use Tygh\Enum\UserTypes;
use Tygh\Enum\YesNo;
use Tygh\Exceptions\DeveloperException;
use Tygh\Http;
use Tygh\Languages\Languages;
use Tygh\Location\Manager;
use Tygh\Navigation\LastView;
use Tygh\Registry;
use Tygh\Storage;
use Tygh\Tools\SecurityHelper;
use Tygh\Tools\Url;

defined('BOOTSTRAP') or die('Access denied');

function fn_get_departments($params = array(), $items_per_page, $lang_code = CART_LANGUAGE)
{
    $default_params = array(
        'page' => 1,
        'items_per_page' => 3
    );
    $params = array_merge($default_params, $params);
    if (AREA == 'C') {
        $params['status'] = 'A';
    }
    $sortings = array(
        'timestamp' => '?:departments.timestamp',
        'name' => '?:departments_descriptions.department',
        'status' => '?:departments.status',
    );
    $condition = $limit = $join = '';
    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }
    $sorting = db_sort($params, $sortings, 'name', 'asc');
    if (!empty($params['item_ids'])) {
        $condition .= db_quote(' AND ?:departments.department_id IN (?n)', explode(',', $params['item_ids']));
    }
    if (!empty($params['department_id'])) {
        $condition .= db_quote(' AND ?:departments.department_id = ?i', $params['department_id']);
    }
    if (!empty($params['status'])) {
        $condition .= db_quote(' AND ?:departments.status = ?s', $params['status']);
    }
    $fields = array (
        '?:departments.department_id',
        '?:departments.status',
        '?:departments.timestamp',
        '?:departments.lead_user_id',
        '?:departments_descriptions.department',
        '?:departments_descriptions.description',
    );
    $join .= db_quote(' LEFT JOIN ?:departments_descriptions ON ?:departments_descriptions.department_id = ?:departments.department_id AND ?:departments_descriptions.lang_code = ?s', $lang_code);
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:departments $join WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }
    $departments = db_get_hash_array(
        "SELECT ?p FROM ?:departments " .
        $join .
        "WHERE 1 ?p ?p ?p",
        'department_id', implode(', ', $fields), $condition, $sorting, $limit
    );
    $department_image_ids = array_keys($departments);
    $images = fn_get_image_pairs($department_image_ids, 'department', 'M', true, false, $lang_code);
    foreach ($departments as $department_id => $department) {
        $departments[$department_id]['main_pair'] = !empty($images[$department_id]) ? reset($images[$department_id]) : array();
    }
    foreach ($departments as $department_id => $department) {
        $user_info = fn_get_user_info($department['lead_user_id'], false);
        $departments[$department_id]['user_info'] = $user_info;
    }
    $cache_key = "departments";
    $cache_tables = ['departments'];
    Registry::registerCache(
        $cache_key,
        $cache_tables,
        Registry::cacheLevel('static')
    );   
    Registry::set($cache_key, $departments);
    $departments = Registry::get($cache_key, $departments);
    return array($departments, $params);
}
function fn_get_department_data ($department_id = 0, $lang_code = CART_LANGUAGE)
{
    $department = [];
    if (!empty($department_id)){
        list($departments) = fn_get_departments([
            'department_id' => $department_id
        ], 1, $lang_code);
        if (!empty($departments)) {
            $department = reset($departments);
            $department['worker_ids'] = fn_department_get_links($department['department_id']);
            foreach ($department['worker_ids'] as $worker) {
                $department['workers'][] = fn_get_user_info($worker, false);
               }
       }
    }
    return $department;
}
function fn_update_department($data, $department_id, $lang_code = DESCR_SL)
{

    if (isset($data['timestamp'])) {
        $data['timestamp'] = fn_parse_date($data['timestamp']);
    }

    if (!empty($department_id)) {
        db_query("UPDATE ?:departments SET ?u WHERE department_id = ?i", $data, $department_id);
        db_query("UPDATE ?:departments_descriptions SET ?u WHERE department_id = ?i AND lang_code = ?s", $data, $department_id, $lang_code);
    } else {
        $department_id = $data['department_id'] = db_replace_into ('departments', $data);

        foreach (Languages::getAll() as $data['lang_code'] => $v) {
            db_query("REPLACE INTO ?:departments_descriptions ?e", $data);
        } 
    }
    if (!empty($department_id)) {
        fn_attach_image_pairs ('department','department', $department_id, $lang_code);

    }
    $worker_id = !empty ($data['workers_ids']) ? $data['workers_ids'] : [];
    $worker_ids = explode(",", $worker_id);
    fn_department_delete_links($department_id);
    fn_department_add_links($department_id, $worker_ids);

    return $department_id;
}

function fn_department_add_links($department_id, $worker_ids)
{
    if (!empty($worker_ids)) {
        foreach ($worker_ids as $worker_id) {
            db_query("REPLACE INTO ?:departments_links ?e", [
                'worker_id' => $worker_id,
                'department_id' => $department_id,
            ]);
            
        }
    }
}

function fn_department_get_links($department_id)
{
    return !empty($department_id) ? db_get_fields('SELECT worker_id from `?:departments_links` WHERE  `department_id` = ?i', $department_id) : [];
}

function fn_delete_department($department_id)
{
    if (!empty($department_id)) {
        $res = db_query("DELETE FROM ?:departments WHERE department_id = ?i", $department_id);
        db_query("DELETE FROM ?:departments_descriptions WHERE department_id = ?i", $department_id);
        fn_department_delete_links($department_id);
    }
}
function fn_department_delete_links($department_id) 
{
    db_query("DELETE FROM ?:departments_links WHERE department_id = ?i", $department_id);
}

