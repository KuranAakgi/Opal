Opal PHP framework v0.1 build 1001
developed by B1@K
latest update date: 10/4/2013

=====================
 INSTALLATION GUIDE
=====================
1. Place all files of Opal in the WWW file of your http server. Make sure your server is supporting php 5.1+.
2. Modify the database config file which is ./config/db.cfg.php which that Opal can connnect to your databse.
3. Modify the type of displaying echo table field, the config file is ./config/dbTable.cfg.php.


============
 USE GUIDE
============
1. All php files shorld include ./system/common.php such that they can use the classes, fuctions, config and variable provided by Opal.
2. All data objects of each table in the database will be automatically generated. You may initiate them by the name exactly the same of it's table name in database. The data objects will have properties as the columns in it's table, and methods pre-defined.
3. An api of requiring paramaters and files uploaded by GET or POST method is provided in class GeneralController, it will be included as the ./system/common.php. $_GET, $_POST, $_REQUEST etc, are not recommended to use for security reason.

======
 NEWS
======
v0.1 build 1001
	1. Automatic generate model class is now avaliable.
	2. Basic CRUD functions is able.
	3. Combined relationship of database is still not finish and maybe buggy
	4. Constrain checking is not done.
	5. GeneralObj should be able to work smoothly.