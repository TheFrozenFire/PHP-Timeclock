<?php
if(!class_exists('gtk')) die('You do not have PHP-GTK installed or enabled. Please enable PHP-GTK before proceeding.');
error_reporting(E_ALL);

chdir(dirname(__FILE__));

require_once('includes/TimeClock_GUI.class.php');
require_once('includes/DataSource.interface.php');
require_once('includes/DataSources/MySQL_DataSource.datasource.php');

$credentials = unserialize(file('database.cfg'));

if(!$datasource = new MySQL_DataSource('localhost', $credentials)) die('Could not initialize data source');

$gui = new TimeClock_GUI($datasource);

Gtk::main();
?>
