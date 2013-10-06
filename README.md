cigdocs
=======

Goggle Spreadsheet - Chart Integration in Ci Framework


Technical Details :-
-----------------------------------------

Server Scripts - PHP 5
Framework - CodeIgniter V 2.1.3
Database - MySQL 5.1.41
Client Script - jQuery V 1.8.3

Copy the whole 'cigdocs' folder to server root.



Database Configuration Changes Required :-
----------------------------------------------------------------------

Import the 'cigdocs.sql' database dump (which is under the root folder location) to your MySQL server database.

Change the database configuration file which is under -  / cigdocs / application / config  / database.php

Change the following configurations as per your database settings - 
$db['default']['hostname'] = 'localhost';
$db['default']['username'] = 'root';
$db['default']['password'] = 'toor';
$db['default']['database'] = 'cigdocs';


Network connectivity is required for the application.


Access URL :-   http://your-hostname/cigdocs