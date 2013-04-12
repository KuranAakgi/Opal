<?
GeneralController::requireVarialbe(array(
	'table' => NULL
));

$dataObject = new $table();
$dataFields = $dataObject->getFieldsWithoutID();

$dataReceive = array();
GeneralController::requireVarialbe($dataFields, 'request', $dataReceive);
foreach($dataReceive as $key=>$field){
	$dataObject->$key = $field;
}

echo $dataObject->insert()?'success':'fail';
