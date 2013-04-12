<?

GeneralController::requireVarialbe(array(
    'table' => NULL,
    'format' => 'html',
    'min' => -10,
    'max' => 0
));

try {
    $dataSeedObject = new $table();
    if (!isset($dataSeedObject) || !is_object($dataSeedObject))
        die('data seed object is not found, wrong table name?');
    $dataObjects = $dataSeedObject->getRange($min, $max);
    $columnNames = $dataSeedObject->getColumnNames();


    switch ($format) {
        case 'html':
            $output = dataObjectsToHtmlTableString($dataObjects, $columnNames);
            break;
        case 'json':
            header('Content-type: application/json');
            $output = dataObjectsToJsonString($dataObjects, $columnNames);
            break;
    }
    echo $output ? $output : 'no output';
} catch (Exception $error) {
    header("HTTP/1.1 301 Moved Permanently");
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $config['error']);
}

function dataObjectsToHtmlTableString($dataObjects, $columnNames) {
    $resultData = '<td>%data%</td>';
    $resultRow = '<tr>%row%</tr>';
    $resultHead = '<th>%columnName%</th>';
    $result = '<table>';

    //make thead
    $result .= '<thead>';
    foreach ($columnNames as $column) {
        $result .= str_replace('%columnName%', $column, $resultHead);
    }
    $result .= '</thead>';

    //make tbody
    $result .= '<tbody>';
    foreach ($dataObjects as $dataObject) {
        $row = '';
        foreach ($columnNames as $column) {
            $row .= str_replace('%data%', $dataObject->$column, $resultData);
        }
        $result .= str_replace('%row%', $row, $resultRow);
    }
    $result .= '</tbody>';


    $result .= '</table>';

    return $result;
}

function dataObjectsToJsonString($dataObjects) {
    $dataList = array();
    foreach ($dataObjects as $dataObject)
        $dataList[] = $dataObject->getFields();
    return json_encode($dataList);
}

