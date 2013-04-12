<?php

function renewAllModelObject() {
    //init variables
    global $db, $config;
    $modelDirectory = DIRECTORY_ROOT . DIRECTORY_SEPARATOR . 'model';
    $modeldefiningStine = <<<MDS
<?
class %modelName% extends GeneralObj {
    public function __construct(\$id = '') {
        parent::__construct('%modelName%', \$id);
    }
}
MDS;

    //create table if not exists to contain opal's model list
    $sql = 'CREATE TABLE IF NOT EXISTS ' . $config['db']['MLT'] . ' (table_name VARCHAR(64) NOT NULL PRIMARY KEY)';
    $stmt = $db->prepare($sql);
    $stmt->execute();

    //get the current model list
    $stmt = $db->prepare("SELECT table_name FROM :MLTName");
    $stmt->bindParam(':MLTName', $config['db']['MLT']);
    $stmt->execute();
    $modelNameList = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $modelNameList[] = $row['table_name'];
    }

    //get the current table list
    $stmt = $db->prepare('show tables');
    $stmt->execute();
    $tableNameList = array();
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tableNameList[] = $row[0];
    }

    //remove the table that opal used to contain model list
    foreach ($tableNameList as $i => $tableName) {
        if ($tableName == strtolower($config['db']['MLT'])) {
            unset($tableNameList[$i]);
        }
    }

    //find out tables that has no model and models that has no table
    $nonModeledTable = array_diff($tableNameList, $modelNameList);
    $modelOfNonExistingTable = array_diff($modelNameList, $tableNameList);

    // process table that has no model
    foreach ($nonModeledTable as $tableName) {
        $classPath = $modelDirectory . DIRECTORY_SEPARATOR . $tableName . '.cls.php';

        // auto-create the file
        if (file_exists($classPath)) {
            unlink($classPath);
        }
        if (!file_exists($classPath)) {
            file_put_contents($classPath, str_replace('%modelName%', $tableName, $modeldefiningStine));
        }

        //insert the record of this model 
        $sql = 'INSERT INTO  ' . $config['db']['MLT'] . ' (table_name)VALUES (:modelName)';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':modelName', $tableName);
        $stmt->execute();
    }

    // process model that has no table
    foreach ($modelOfNonExistingTable as $modelName) {
        $classPath = $modelDirectory . DIRECTORY_SEPARATOR . $modelName . '.cls.php';
        unlink($classPath);

        //delete the record of this model 
        $sql = 'DELETE FROM ' . $config['db']['MLT'] . ' WHERE table_name = :modelName LIMIT 1;';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':modelName', $modelName);
        $stmt->execute();
    }
}
