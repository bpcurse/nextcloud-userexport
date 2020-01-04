# Nextcloud userexport
PHP script to export user lists using Nextcloud's user metadata OCS API and curl.

## Installation
- Upload 'index.php', 'userexport.php' and 'download.php' to a directory on your webserver and open 'index.php' in a browser. You can point a subdomain like https://export.cloud.example.com at it.
- **Make sure it is only accessible via https://** as you will be providing nextcloud admin credentials to it.

## General usage
- Enter the URL of the Nextcloud target instance incl. https://
- Enter a username that has admin rights
- Enter the corresponding password
- Change the display type (if necessary)
- Click on "submit" and wait

Do not use http:// unless you have a very good reason to do so.
The script will block outgoing plain HTTP connections and warn you unless you override this security measure with !http://...

API calls via cURL are slow. **Querying several hundred user accounts can take some minutes**. Be patient :)

CURL parallel requests have been implemented in v0.2.0 and provide a relevant speed boost, but that's about as fast as it gets.
Approximately 10-15s/100users.

A progress indicator isn't implemented yet, but it's on the list.

## Parameters
You can use the following GET parameters with this script:

- url (URL incl. protocol of the target instance)
- user (admin username to query the records)
- pass (user password) - NOT RECOMMENDED
- type (display type)
  - 'table' [default] (display html formatted table)
  - 'csv' (display comma separated values)
- msg_mode (how to configure the mass email mailto: list)
  - 'to'  
  - 'cc'
  - 'bcc' [default] recommended for privacy and legal reasons

If you do not supply one of the parameters you can fill in the corresponding fields afterwards in the form (e.g. password).
Prefilled form fields can also be edited by user input.

**Examples:**

- https://mydomain.org/userexport.php/?url=https://cloud.example.com&user=myusername&pass=goodpassword&type=csv
- https://userexport.mydomain.org/?url=https://cloud.example.com&user=myusername&msg_mode=to

You can download a CSV formatted file for direct import by clicking a button on the results page.

Simple mass mailing to all users on the list is also provided by a button on the results page.
This will open your email application with a mailto: string containing all email addresses as bcc. You can change to 'cc' or 'to' by using a GET parameter.

## How it works
The script uses cURL to make calls to Nextcloud's user metadata OCS API and displays them either through an HTML table or a CSV list that can be easily copied to calc/excel.

https://docs.nextcloud.com/server/17/developer_manual/client_apis/OCS/ocs-api-overview.html#user-metadata

## Nextcloud integration
You can integrate it by using the external sites app and show it only to your admin group.
Prefill URL and user name by using GET parameters and a nextcloud placeholder like this:

https://export.cloud.mydomain.com/?url=https://cloud.example.com&user={uid}

## Known Issues
Error handling isn't yet implemented.

If you end up with some obscure php error messages, the most probable reason is a typo in url, username or password.

## Development
I will try to maintain and enhance this script as long as Nextcloud does not provide (better) GUI based user and group export functions.
Any hints to enhancements or security issues are highly welcome.
If you would like to contribute, please open an issue or a pull request.

## Additional Info
Inspired by comments to this github issue:
https://github.com/nextcloud/server/issues/14715
