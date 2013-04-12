<?

GeneralController::requireVarialbe(array(
    'table' => NULL,
    'ID' => -1
));

if ($ID == -1) {
    $formTargetAction = 'add';
    $dataSeedObject = new $table();
} else {
    $formTargetAction = 'modify';
    $dataSeedObject = new $table($ID);
}

$columns = $dataSeedObject->getColumnNames();
$output = '<form method="GET" action="./CRUDController.php"><input type="hidden" name="table" value="' . $table . '" /><input type="hidden" name="action" value="' . $formTargetAction . '" /><table>';

$tableData = '<td>%data%</td>';
$tableRow = '<tr>%row%</tr>';
$tableHead = '<th>%columnName%</th>';
$inputTag = '<input type="%inputType%" name="%columnName%" value="%columnValue%" %readonly% />';


//build table body
$output .= '<tbody>';
foreach ($columns as $columnName) {
    if ($columnName == 'ID') {
        continue;
    }

    $row = '';

    //set the column name
    $row .= GeneralController::stringArrayReplace($tableData, array('%data%' => $columnName));

    //get input type of this column from config
    $inputType = $config['db']['dataField'][$table][$columnName];
    if (!isset($inputType)) {
        $inputType = 'text';
    }

    //get readonly of this column from config
    $readonly = $config ['db']['readOnly'][$table][$columnName];
    if (isset($readonly) and $readonly) {
        $readonly = 'readonly="readonly"';
    } else {
        $readonly = '';
    }

    $inputStatement = GeneralController::stringArrayReplace($inputTag, array(
                '%inputType%' => $inputType,
                '%columnName%' => $columnName,
                '%columnValue%' => $dataSeedObject->$columnName,
                '%readonly%' => $readonly
                    )
    );
    $row .= GeneralController::stringArrayReplace($tableData, array('%data%' => $inputStatement));

    $row = GeneralController::stringArrayReplace($tableRow, array('%row%' => $row));

    $output .= $row;
}

//add the id as a hidden input type
$output .= GeneralController::stringArrayReplace($inputTag, array(
            '%inputType%' => 'hidden',
            '%columnName%' => 'ID',
            '%columnValue%' => $ID,
            '%readonly%' => 'readonly="readonly"'
                )
);
$output .= '</tbody>';

$output .= '</table><input type="submit" value="submit" /></form>';

echo $output;