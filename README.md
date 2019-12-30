# Nextcloud userexport
Simple php script to export user lists using the Nextcloud user metadata OCS API

## Installation
- Upload userexport.php to a directory on your webserver and open it in a browser. You can rename it to index.php if you point a subdomain at it, or the like.
- **Make sure it is only accessible via https://** as you will be providing nextcloud admin credentials to it.

## General usage
- Enter the URL of the Nextcloud target instance incl. https://
- Enter a username that has admin rights
- Enter the corresponding password
- Click on "submit" and wait

Do not use http:// unless you have a very good reason to do so.
The script will block plain http connections and warn you unless you override this security measure with !http://...

API calls via curl are slow. **Querying hundreds of user accounts can take several minutes**. Be patient :)

## Parameters
You can use the following GET parameters with this script:

- url (url incl. protocol of the target instance)
- user (admin username to query the records)
- pass (user password) | NOT RECOMMENDED via GET!
- type (export type: 'table' for an html table or 'csv' for comma separated values)

If you do not supply one of the parameters you can fill in the responding fields in the form (e.g. password).
The prefilled form fields can also be edited by user input.

**Examples:**

- https://mydomain.org/userexport.php/?url=https://cloud.example.com&user=myusername&pass=goodpassword&type=csv
- https://userexport.mydomain.org/?url=https://cloud.example.com&user=myusername&type=table

## How it works
The script uses curl to make calls to nextcloud user metadata OCS API and displays them in an html table that can be easily copied to calc/excel.

https://docs.nextcloud.com/server/17/developer_manual/client_apis/OCS/ocs-api-overview.html#user-metadata

## Nextcloud integration
You can integrate it by using the external sites app and show it only to your admin group.
Prefill url and user names by using GET parameters and nextcloud placeholders:

https://mydomain.com/userexport.php/?url=https://cloud.example.com&user={uid}&type=csv

## Known Issues
Error handling is not yet implemented.
If you end up with some obscure php error messages, the most probable reason is a typo in url, username or password.

## Development
I will try to maintain and enhance this script as long as Nextcloud does not provide GUI based user and group export functions.
As I am not an experienced programmer any hints to enhancements or security issues are highly welcome. If you would like to contribute, please open an issue or a pull request.

## Additional Info
Inspired by comments to this github issue:
https://github.com/nextcloud/server/issues/14715
