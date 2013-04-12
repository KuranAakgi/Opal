<?
GeneralController::requireVarialbe(array(
	'table' => NULL,
	'ID' => NULL
));

$dataObject = new $table($ID);
$dataFields = $dataObject->getFieldsWithoutID();

$dataReceive = array();
GeneralController::requireVarialbe($dataFields, 'request', $dataReceive);
foreach($dataReceive as $key=>$field){
	$dataObject->$key = $field;
}
echo $dataObject->update()?'success':'fail';