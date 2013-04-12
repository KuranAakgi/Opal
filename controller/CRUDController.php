<?
include_once('../system/common.php');


GeneralController::requireVarialbe(array(
	'action' => NULL
));


switch($action){
case 'add':
	require('./Section/addControllerSection.php');
	break;
case 'modify':
	require('./Section/modifyControllerSection.php');
	break;
case 'delete':
	require('./Section/deleteControllerSection.php');
	break;
case 'showData':
	require('./Section/showDataControllerSection.php');
	break;
case 'showForm':
	require('./Section/showFormControllerSection.php');
	break;
default:
	die('action type error');
}