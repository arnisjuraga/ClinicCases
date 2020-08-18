<?php

// namespace App;




//scripts for case data tab in case detail
session_start();
require_once dirname(__FILE__) . '/../../../db.php';
require_once(CC_PATH . '/lib/php/auth/session_check.php');
require_once(CC_PATH . '/lib/php/utilities/convert_times.php');


if (isset($_REQUEST['id'])) {

    $case_id = $_REQUEST['id'];
}

if (isset($_REQUEST['type'])) {
    $type = $_REQUEST['type'];
}

$data = Casefile::getCaseOld($case_id);


if (!$_SESSION['mobile']) {
    include '../../../html/templates/interior/cases_case_data.php';
}
