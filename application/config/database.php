<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the "Database Connection"
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the "default" group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = "local_dev";
$active_record = TRUE;

//Production server with standard authenication
$db['production']['hostname'] = "localhost";
$db['production']['username'] = "";
$db['production']['password'] = "";
$db['production']['database'] = "";
$db['production']['dbdriver'] = "mysql";
$db['production']['dbprefix'] = "";
$db['production']['pconnect'] = TRUE;
$db['production']['db_debug'] = FALSE;
$db['production']['cache_on'] = FALSE;
$db['production']['cachedir'] = "";
$db['production']['char_set'] = "utf8";
$db['production']['dbcollat'] = "utf8_general_ci";

//Dev server with standard authenication 
$db['local_dev']['hostname'] = "localhost";
$db['local_dev']['username'] = "kyleh";
$db['local_dev']['password'] = "";
$db['local_dev']['database'] = "fb_highrise";
$db['local_dev']['dbdriver'] = "mysql";
$db['local_dev']['dbprefix'] = "";
$db['local_dev']['pconnect'] = TRUE;
$db['local_dev']['db_debug'] = TRUE;
$db['local_dev']['cache_on'] = FALSE;
$db['local_dev']['cachedir'] = "";
$db['local_dev']['char_set'] = "utf8";
$db['local_dev']['dbcollat'] = "utf8_general_ci";

//Dev server with oAuth authenication 
$db['local_dev_oauth']['hostname'] = "localhost";
$db['local_dev_oauth']['username'] = "kyleh";
$db['local_dev_oauth']['password'] = "";
$db['local_dev_oauth']['database'] = "fb_highrise_oauth";
$db['local_dev_oauth']['dbdriver'] = "mysql";
$db['local_dev_oauth']['dbprefix'] = "";
$db['local_dev_oauth']['pconnect'] = TRUE;
$db['local_dev_oauth']['db_debug'] = TRUE;
$db['local_dev_oauth']['cache_on'] = FALSE;
$db['local_dev_oauth']['cachedir'] = "";
$db['local_dev_oauth']['char_set'] = "utf8";
$db['local_dev_oauth']['dbcollat'] = "utf8_general_ci";

/* End of file database.php */
/* Location: ./system/application/config/database.php */