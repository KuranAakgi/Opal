<?
GeneralController::requireVarialbe(array(
	'table' => NULL,
	'ID' => NULL
));

$dataObject = new $table();

$dataObject->ID = $ID;

echo $dataObject->delete()?'success':'fail';