# Rackspace Cloud Database Backup

Create a backup of a Rackspace Cloud Database and store it in Rackspace Cloud Files.

### Requirements

- PHP 5.4
- mysql-client (for `mysqldump` command-line)
- Gzip

[Since Rackspace Cloud Databases are running under MySQL 5.6, you have to use the version 5.5 (or higher) of mysql-client.](https://community.rackspace.com/products/f/25/t/4613)

### Usage

As Rackspace Cloud Databases are on private networks you have to run the backup on a Rackspace Server.

Once installed, simply call backup.php with settings in querystring.

- db_host : Rackspace Cloud Database hostname (ex: xxx.rackspaceclouddb.com)
- db_user : Database user name
- db_password : Database user password
- db_name : Database that need to be backuped
- cf_username : your Rackspace account name
- cf_username : your Rackspace API key
- cf_datacenter : your Rackspace location (ex: ORD, DFW, LON, ...)

You can use this script in a cron job with the following command

```shell
wget -O /dev/null "http://localhost/Rackspace-Cloud-Database-Backup/backup.php?db_host=yourhostname&db_user=yourusername&db_password=yourpassword&db_name=yourdatabase&cf_username=youraccountname&cf_apikey=yourapi&cf_datacenter=yourlocation" > /dev/null
```
