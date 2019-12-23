# Nextcloud userexport
Simple php script to export user lists using  the Nextcloud user metadata OCS API

## Installation
- Upload userexport.php to a directory on your webserver and open it in a browser. You can rename it to index.php if you point a subdomain at it, or the like.
- **Make sure it is only accessible via https://** as you will be providing nextcloud admin credentials to it.

## Usage
- Enter the URL of the Nextcloud target instance incl. http**s**:// (do not use http:// unless you have a very good reason to do so)
- Enter a username that has admin rights
- Enter the corresponding password
- Click on "submit" and wait

API calls via curl are slow. Querying hundreds of user accounts can take several minutes. Be patient :)

## How it works
The script uses curl to make calls to nextcloud user metadata OCS API and displays them in an html table that can be easily copied to calc/excel.
https://docs.nextcloud.com/server/17/developer_manual/client_apis/OCS/ocs-api-overview.html#user-metadata

You can integrate it into nextcloud by using the external sites app and show it only to your admin group.

## Development
I will try to maintain and enhance this script as long as Nextcloud does not provide user and group export functions.
As I am not an experienced programmer any hints to enhancements/security issues are highly welcome. I you would like to contribute, please open an issue or a pull request.

## Additional Info
Inspired by comments to this github issue:
https://github.com/nextcloud/server/issues/14715