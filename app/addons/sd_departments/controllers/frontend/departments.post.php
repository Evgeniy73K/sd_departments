<?php
use Tygh\Registry;
use Tygh\BlockManager\ProductTabs;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode === 'departments') {
    Tygh::$app['session']['continue_url'] = "departments.departments";

    $params = $_REQUEST;

    list($departments, $search) = fn_get_departments($params, Registry::get('settings.Appearance.products_per_page'), CART_LANGUAGE);

    Tygh::$app['view']->assign('departments', $departments);
    Tygh::$app['view']->assign('search', $search);
    Tygh::$app['view']->assign('columns', 3);

    fn_add_breadcrumb(__('departments'));

} elseif ($mode === 'department') {
    $department_data = [];
    $department_id = !empty($_REQUEST['department_id']) ? $_REQUEST['department_id'] : 0;
    $department_data = fn_get_department_data($department_id, CART_LANGUAGE);
    
    if (empty($department_data)) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }
    
    Tygh::$app['view']->assign('department_data', $department_data);
    Tygh::$app['view']->assign('user_info', $user_info);

    fn_add_breadcrumb(__('departments'), "departments.departments");
    }