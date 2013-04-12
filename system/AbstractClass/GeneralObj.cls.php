<?php

abstract class GeneralObj {

    protected $db;
    protected $FIELDS = array();
    protected $defaultFields = array();
    protected $relatedTableField = array();
    protected $relationship;
    protected $tableName = '';

    function __construct($tableName, $id = '') {
        global $db;
        global $config;
        $this->db = $db;
        $this->tableName = $tableName;
        if (isset($config['db']['relationship'][$this->tableName])) {
            $this->relationship = $config['db']['relationship'][$this->tableName];
        }

        //set the field names to the index of $FIELDS
        $sql = "DESCRIBE $this->tableName";
        $result = $this->db->query($sql, PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            if (strtolower($row['Field']) === 'id') {
                $row['Field'] = 'ID';
            }

            if ($row['Key'] == 'PKI') {
                $this->FIELDS[$row['Field']] = '';
            } else {
                $this->FIELDS[$row['Field']] = $row['Default'] === "NULL" ? NULL : $row['Default'];
                if (!empty($row['Default'])) {
                    $this->defaultFields[$row['Field']] = $row['Default'];
                }
            }
        }

        //set the $relatedTableField by analysing config file
        if (isset($config['db']['relationship'])) {
            foreach ($config['db']['relationship'] as $hostTableName => $relationshipArray) {
                if ($name === $this->tableName) {
                    continue;
                }

                //decode normal relationship
                if (isset($relationshipArray['normal'])) {
                    foreach ($relationshipArray['normal'] as $columnName => $targetTable) {
                        if ($targetTable === $this->tableName) {
                            $relatedTableField[$columnName] = $hostTableName;
                        }
                    }
                }else {
                    $relationshipArray['normal'] = array();
                }
                //decode combine relationship, as cr
                if (isset($relationshipArray['combine'])) {
                    foreach ($relationshipArray['combine'] as $columnName => $crArray) {
                        foreach ($crArray['tableNames'] as $targetTable) {
                            if ($targetTable === $this->tableName) {
                                $relatedTableField[$columnName] = $hostTableName;
                            }
                        }
                    }
                }else {
                    $relationshipArray['combine'] = array();
                }
            }
        }
        //get the data if $id is set
        if ($id !== '') {
            if (!$this->get($id)) {
                throw new Exception("ID $id not found in $this->tableName");
            }
        }
    }

    function __get($name) {
        if (array_key_exists($name, $this->FIELDS)) {
            return $this->FIELDS[$name];
        } else {
            $tableName = substr($name, 0, -1);
            if (substr($name, -1) === 's' and array_key_exists($tableName, $this->relatedTableField)) {
                return $this->search($this->ID, array($this->relatedTableField[$name]), '=');
            }
        }
    }

    function __set($name, $value) {
        if (array_key_exists($name, $this->FIELDS)) {
            //check constain of normal relationship
            if (isset($this->relatedTableField[$name])) {
                $relatedObj = new $this->relatedTableField[$name]();
                if (!$relatedObj->get($value)) {
                    throw new Exception("ID $value is not found in $this->relatedTableField[$name]");
                }
            }

            //check constrain of combine relationship
            if (isset($this->relationship['combine'][$name]['tableNames'])) {
                if (!in_array($value, $this->relationship['combine'][$name]['tableNames'])) {
                    throw new Exception("$name in $this->tableName should not be $value");
                }
            }

            $this->FIELDS[$name] = $value;
        } else {
            throw new Exception($name);
        }
    }

    function getFields() {
        return $this->FIELDS;
    }

    function getFieldsWithoutID() {
        $fields = $this->FIELDS;
        unset($fields['ID']);
        return $fields;
    }

    function getTablename() {
        return $this->tableName;
    }

    function getColumnNames() {
        return array_keys($this->FIELDS);
    }

    function get($id) {
        $sql = "SELECT * FROM $this->tableName WHERE ID = :id";
        $PDOStatement = $this->db->prepare($sql);
        $PDOStatement->bindParam(":id", $id);
        $PDOStatement->execute();
        if ($PDOStatement->rowCount() != 1) {
            return false;
        }

        $result = $PDOStatement->fetch(PDO::FETCH_ASSOC);

        foreach ($result as $key => $value) {
            if (strtoupper($key) === 'ID') {
                $key = 'ID';
            }
            $this->FIELDS[$key] = $value;
        }

        return true;
    }

    function insert() {
        $sql = "INSERT INTO $this->tableName SET ";
        $request = false;

        $fieldsCopy = $this->FIELDS;
        unset($fieldsCopy['ID']);

        $tmp_arr = array();
        foreach ($fieldsCopy as $key => $value) {
            if ($key === 'ID') {
                continue;
            }
            $tmp_arr[] = " $key = :$key";
        }

        $sql .= implode(',', $tmp_arr);

        try {
            $this->db->beginTransaction();
            $PDOStatement = $this->db->prepare($sql);
            foreach ($fieldsCopy as $key => $value) {
                if (array_key_exists($key, $this->defaultFields) && $value === $this->defaultFields[$key]) {
                    $PDOStatement->bindValue(":$key", NULL, PDO::PARAM_NULL);
                } else {
                    $PDOStatement->bindParam(':' . $key, $fieldsCopy[$key]);
                }
            }
            $request = $PDOStatement->execute() or die(print_r($this->db->errorInfo(), true));
            $this->ID = $this->db->lastInsertId();
            $this->db->commit();
        } catch (PDOExecption $e) {
            $this->db->rollback();
            throw new PDOExecption($e->getMessage());
        }

        return $request;
    }

    function update() {
        $sql = "UPDATE $this->tableName SET ";
        $result = false;

        $tmp_arr = array();
        foreach ($this->FIELDS as $key => $value) {
            if (strtoupper($key) === 'ID') {
                continue;
            }
            $tmp_arr[] = " $key = :$key ";
        }
        $sql .= implode(',', $tmp_arr);
        $sql .= ' WHERE id = :ID';

        try {
            $this->db->beginTransaction();
            $PDOStatement = $this->db->prepare($sql);

            foreach ($this->FIELDS as $key => $value) {
                if ($key == 'ID') {
                    $PDOStatement->bindValue(":$key", $value);
                } else {
                    $PDOStatement->bindParam(":$key", $this->$key);
                }
            }
            $result = $PDOStatement->execute();
            $this->db->commit();
        } catch (PDOExecption $e) {
            $this->db->rollback();
            throw new PDOExecption($e->getMessage());
        }

        return $result;
    }

    function delete() {
        $result = false;
        try {
            $this->db->beginTransaction();
            $PDOStatement = $this->db->prepare("DELETE FROM $this->tableName WHERE ID = :id");
            $PDOStatement->bindParam(':id', $this->ID);

            $result = $PDOStatement->execute();
            $this->db->commit();
        } catch (PDOExecption $e) {
            $this->db->rollback();
            throw new PDOExecption($e->getMessage());
        }

        foreach ($this->getFields() as $key => $value) {
            if (isset($this->defaultFields[$key])) {
                $this->$key = $this->defaultFields[$key] === "NULL" ? NULL : $this->defaultFields[$key];
            } else {
                $this->$key = NULL;
            }
        }

        return $result;
    }

    function searchID($keyword, $fieldname_array = '', $operator = 'LIKE', $join_operator = 'OR') {
        if ($operator == 'LIKE') {
            if (is_array($keyword)) {
                foreach ($keyword as $key => $value) {
                    $keyword[$key] = "%$value%";
                }
            } else {
                $keyword = "%$keyword%";
            }
        }
        if ($fieldname_array == '') {
            $fieldname_array = array_keys($this->FIELDS);
        }

        $sql = "SELECT ID FROM $this->tableName WHERE ";

        foreach ($fieldname_array as $key => $fieldname) {
            $fieldname_array[$key] = "$fieldname $operator :" . (is_array($keyword) ? $keyword[$key] : 'keyword');
        }
        $sql .= implode(" $join_operator ", $fieldname_array);

        $PDOStatement = $this->db->prepare($sql);
        if (is_array($keyword)) {
            foreach ($keyword as $key => $value) {
                $PDOStatement->bindParam(":$keyword[$key]", $keyword[$key]);
            }
        } else {
            $PDOStatement->bindParam(':keyword', $keyword);
        }
        $PDOStatement->execute();

        $idList = array();
        while ($row = $PDOStatement->fetch(PDO::FETCH_ASSOC)) {
            $idList[] = $row['ID'];
        }
        return $idList;
    }

    function search($keyword, $fieldname_array = '', $operator = 'LIKE', $join_operator = 'OR') {
        if ($operator == 'LIKE') {
            if (is_array($keyword)) {
                foreach ($keyword as $key => $value) {
                    $keyword[$key] = "%$value%";
                }
            } else {
                $keyword = "%$keyword%";
            }
        }

        if ($fieldname_array == '') {
            $fieldname_array = array_keys($this->FIELDS);
        }

        $sql = "SELECT * FROM $this->tableName WHERE ";

        foreach ($fieldname_array as $key => $fieldname) {
            $fieldname_array[$key] = "$fieldname $operator :" . (is_array($keyword) ? $keyword[$key] : 'keyword');
        }
        $sql .= implode(" $join_operator ", $fieldname_array);

        $PDOStatement = $this->db->prepare($sql);
        if (is_array($keyword)) {
            foreach ($keyword as $key => $value) {
                $PDOStatement->bindParam(":$keyword[$key]", $keyword[$key]);
            }
        } else {
            $PDOStatement->bindParam(':keyword', $keyword);
        }
        $PDOStatement->execute();

        $resultList = array();
        $thisTableName = $this->tableName;
        while ($row = $PDOStatement->fetch(PDO::FETCH_ASSOC)) {
            $currentObject = new $thisTableName();
            foreach ($row as $key => $value) {
                if ($key === 'id') {
                    $key = 'ID';
                }
                $currentObject->$key = $value;
            }
            $resultList[] = $currentObject;
        }

        return $resultList;
    }

    function searchIDWithStatment($statment = '') {
        if ($statment == '') {
            $sql = "SELECT ID FROM $this->tableName";
        } else {
            $sql = "SELECT ID FROM $this->tableName WHERE $statment";
        }
        $PDOStatement = $this->db->prepare($sql);
        $PDOStatement->execute();

        $idList = array();
        while ($row = $PDOStatement->fetch(PDO::FETCH_ASSOC)) {
            $idList[] = $row['ID'];
        }
        return $idList;
    }

    function searchWithStatment($statment = '') {
        if ($statment == '') {
            $sql = "SELECT ID FROM $this->tableName";
        } else {
            $sql = "SELECT ID FROM $this->tableName WHERE $statment";
        }
        $PDOStatement = $this->db->prepare($sql);
        $PDOStatement->execute();

        $resultList = array();
        $thisTableName = $this->tableName();
        while ($row = $PDOStatement->fetch(PDO::FETCH_ASSOC)) {
            $currentObject = new $thisTableName();
            foreach ($row as $key => $value) {
                if ($key === 'id') {
                    $key = 'ID';
                }
                $currentObject->$key = $value;
            }
            $resultList[] = $currentObject;
        }

        return $resultList;
    }

    function getRange($start, $end = 0) {
        $result = array();
        if ($start < 0 && $end == 0) {
            $sql = 'SELECT id FROM ' . $this->tableName . ' ORDER BY id DESC LIMIT ' . abs($start);
        } elseif ($start >= 0 && $end == 0) {
            $sql = 'SELECT id FROM ' . $this->tableName . ' LIMIT ' . abs($start);
        } else
            $sql = 'SELECT id FROM ' . $this->tableName . ' LIMIT ' . abs($start) . ',' . abs($end);
        $PDOStatement = $this->db->prepare($sql);
//        if ($end != 0)
//            $PDOStatement->bindParam(':end', $end);
//        $PDOStatement->bindParam(':start', abs($start));
        $PDOStatement->execute();
        $idList = array();
        while ($row = $PDOStatement->fetch(PDO::FETCH_ASSOC)) {
            $idList[] = $row['id'];
        }
        foreach ($idList as $id) {
            $tmp = new $this->tableName($id);
            $result[] = $tmp;
        }
        return $result;
    }

}