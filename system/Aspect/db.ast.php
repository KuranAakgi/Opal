<?

$db = new PDO(
                $config['db']['type'] . ':host=' . $config['db']['host']
                . ';dbname=' . $config['db']['dbname']
                . ';charset=' . $config['db']['charset']
                , $config['db']['user']
                , $config['db']['password']
        ) or die("Error in select DB");