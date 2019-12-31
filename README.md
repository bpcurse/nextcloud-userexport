# Nextcloud userexport
Simple php script to export user lists using Nextcloud's user metadata OCS API and curl.

## Installation
- Upload index.php and userexport.php to a directory on your webserver and open index.php in a browser. You can point a subdomain like https://export.cloud.example.com at it.
- **Make sure it is only accessible via https://** as you will be providing nextcloud admin credentials to it.

## General usage
- Enter the URL of the Nextcloud target instance incl. https://
- Enter a username that has admin rights
- Enter the corresponding password
- Click on "submit" and wait

Do not use http:// unless you have a very good reason to do so.
The script will block plain http connections and warn you unless you override this security measure with !http://...

API calls via curl are slow. **Querying hundreds of user accounts can take several minutes**. Be patient :)

A progress indicator isn't implemented yet, but it's on the list.

## Parameters
You can use the following GET parameters with this script:

- url (URL incl. protocol of the target instance)
- user (admin username to query the records)
- pass (user password) - NOT RECOMMENDED
- type (export type)
  - 'table' (display html formatted table)
  - 'csv' (display comma separated values)
  - 'csv_dl' (download a csv file)

If you do not supply one of the parameters you can fill in the responding fields afterwards in the form (e.g. password).
Prefilled form fields can also be edited by user input. Export default is 'csv file download'.

**Examples:**

- https://mydomain.org/userexport.php/?url=https://cloud.example.com&user=myusername&pass=goodpassword&type=csv
- https://userexport.mydomain.org/?url=https://cloud.example.com&user=myusername&type=table

## How it works
The script uses curl to make calls to Nextcloud's user metadata OCS API and displays them either through an html table or a csv list that can be easily copied to calc/excel. A csv formatted file for direct import can be downloaded, too.

https://docs.nextcloud.com/server/17/developer_manual/client_apis/OCS/ocs-api-overview.html#user-metadata

## Nextcloud integration
You can integrate it by using the external sites app and show it only to your admin group.
Prefill URL and user name by using GET parameters and a nextcloud placeholder like this:

https://export.cloud.mydomain.com/?url=https://cloud.example.com&user={uid}&type=csv_dl

## Known Issues
Error handling isn't yet implemented.

If you end up with some obscure php error messages, the most probable reason is a typo in url, username or password.

## Development
I will try to maintain and enhance this script as long as Nextcloud does not provide GUI based user and group export functions.
As I am not an experienced programmer any hints to enhancements or security issues are highly welcome.
If you would like to contribute, please open an issue or a pull request.

## Additional Info
Inspired by comments to this github issue:
https://github.com/nextcloud/server/issues/14715
