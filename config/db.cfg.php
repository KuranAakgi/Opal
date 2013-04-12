<?php

$config['db']['type'] = 'mysql';
$config['db']['host'] = '127.0.0.1';
$config['db']['dbname'] = 'quiz';
$config['db']['charset'] = 'utf8';

$config['db']['user'] = 'root';
$config['db']['password'] = 'aesopq';

/*
 * table name of Model List Table.
 * Expected it is not exsiting in the database before first installation of Opal.
 * 
 * Normally, it is not necessary to change it.
 * However, in case the database is holding a table with this name, in any cases,
 * it should be changed to other any that not existing in the database as a table name.
 */
$config['db']['MLT'] = 'opal_MLT';