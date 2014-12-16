<?php
	namespace OpenCloud;

	define('RAXSDK_TIMEOUT', '3600');

	require_once('lib/rackspace.php');

	date_default_timezone_set('UTC');

	// Database connection settings
	try {
		$db_host = $_GET["db_host"];
		$db_user = $_GET["db_user"];
		$db_password = $_GET["db_password"];
		$db_name = $_GET["db_name"];
	} catch (Exception $e) {
		echo "Database connection settings are missing.\n";
		die();
	}

	// Rackspace Cloud Files API settings
	try {
		$username = $_GET["cf_username"];
		$key = $_GET["cf_apikey"];
		$datacenter = $_GET["cf_datacenter"];
	} catch (Exception $e) {
		echo "Cloud Files API settings are missing.\n";
		die();
	}

	$date = date("Y-m-d");
	$remotefile = "$db_name-backup-$date.zip";
	$localfile = tempnam(sys_get_temp_dir(), 'Backup');

	// Dump database into local folder
	$db_connection = mysql_connect($db_host, $db_user, $db_password);
	if (!$db_connection) {
		echo "Database connection failed.\n";
		die();
	} else {
		echo "Starting database dump ... ";
		shell_exec("mysqldump -h $db_host -u $db_user --password='$db_password' $db_name | gzip -9 > $localfile");
		echo "Database dump complete.\n";
	}

	// Try to connect to Cloud Files
	echo "Connecting to Cloud Files ... ";
	try {
		define('AUTHURL', 'https://identity.api.rackspacecloud.com/v2.0/');
		$connection = new Rackspace(AUTHURL, ['username' => $username, 'apiKey' => $key]);
		$ostore = $connection->ObjectStore('cloudFiles', "$datacenter");
		echo "Connected to Cloud Files.\n";
	} catch (HttpUnauthorizedError $e) {
		echo "Cloud Files API connection could not be established.\n";
		shell_exec("rm $localfile;");
		die();
	}

	// Create Container
	echo "Creating Cloud Files Container ... ";    
	$cont = $ostore->Container();
	$cont->Create(array('name'=>"$db_name-cron-backups"));
	echo "Cloud Files container created or already exists.\n";

	// Move backup to Cloud Files
	echo "Moving backup to Cloud Files ... ";
	$obj = $cont->DataObject();
	$obj->Create(array('name' => "$remotefile", 'content_type' => 'application/x-gzip'), $filename="$localfile");
	$etag = $obj->hash;

	// Test file integrity
	if (md5_file($localfile) != $etag) {
		$obj->Delete(array('name'=>"$remotefile"));
		echo "Backup failed integrity check.\n";
	} else {
		echo "Backup moved to Cloud Files Successful.\n";
	}

	// Local cleanup
	echo "Cleaning up local backups ... ";
	shell_exec("rm $localfile;");
	echo "Local backups cleaned up.\n";

	echo "Backup complete.\n";
?>
