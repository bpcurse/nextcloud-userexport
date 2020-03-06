# Nextcloud userexport
PHP script to export user and group lists using Nextcloud's user metadata OCS API and cURL.

## How it works
The script uses cURL to make calls to Nextcloud's user metadata OCS API and displays the results either through an HTML table or a CSV list that can be easily copied to calc/excel. You can download a CSV formatted file as well.

https://docs.nextcloud.com/server/17/developer_manual/client_apis/OCS/ocs-api-overview.html#user-metadata

## Installation
- Upload all files to a directory on your webserver and open `index.php` in a browser. You can point a subdomain like `https://export.cloud.example.com` at it.
- **Make sure it is only accessible via https://** as you will be providing nextcloud admin credentials to it.

## General usage
- Enter the URL of the Nextcloud target instance incl. https://
- Enter a username that has admin (or group admin) rights
- Enter the corresponding password
- Click on "connect" and wait

After the script has successfully downloaded user and group data you can access other options from the top navigation bar.

## Security
- Do not use `http://` unless you have a very good reason to do so.

The script will block outgoing plain HTTP connections and warn you unless you override this security measure with `!http://...`

- Group admins (as long as they do not belong themselves to the `admin` group) will not receive information on users that are members of `admin` group, even if they are members of the group the group admin manages.

## Performance
API calls via cURL are slow. **Querying several hundred user accounts can take some minutes**. Be patient :)

CURL parallel requests have been implemented in v0.2.0 and provide a relevant speed boost, but that's about as fast as it gets.
Approximately 10-15s/100users.

A progress indicator isn't implemented yet, but it's on the list.

## "Users" view:
- Choose which user metadata should be displayed or downloaded by selecting checkboxes
- Change display type (if necessary) and click on display **OR**
- Download data as CSV file by clicking the download button

## "Groups" view:
- Change display type (if necessary) and click on display **OR**
- Download data as CSV file by clicking the download button

## "Email" view: 
- Simple mass mailing to all users on the list is provided by clicking a button (Javascript needs to be enabled).

This will open your email application with a mailto: string containing all email addresses as bcc. You can change to 'cc' or 'to' by using the GET parameter `msg_mode` (see chapter "Parameters").

Enhanced functionality regarding emails is planned.

## Parameters
You can use the following GET parameters with this script:

**Nextcloud target instance and user credentials**
- `url`     URL incl. protocol
- `user`    admin username to query the records
- `pass`    user password - not recommended

**Display type** (display of the results page)
- `type`
  - `table` display html formatted table [default]
  - `csv`   display comma separated values

**Message Mode** (how to send mass email)
- `msg_mode`
  - `bcc`   recommended for privacy and legal reasons [default]
  - `to`  
  - `cc`

**Select data to export**
- `select`
  - `id`, `displayname`, `email`, `lastLogin`[case sensitive!], `backend`, `enabled`, `total`, `used`, `free`, `groups`, `subadmin`, `language`, `locale`

**Examples:**
```
https://mydomain.org/userexport.php/?url=https://cloud.example.com&user=myusername&pass=goodpassword&type=csv
https://userexport.mydomain.org/?url=https://cloud.example.com&user=myusername&msg_mode=to&select=id,displayname,enabled,used,lastLogin
```

If you do not supply one of the parameters you can fill in the corresponding fields afterwards in the form (e.g. password).
Prefilled form fields can also be edited by user input.

## Nextcloud integration
You can integrate it by using the external sites app and show it only to your admin group.
Prefill URL and user name by using GET parameters and a nextcloud placeholder like this:

`https://export.cloud.mydomain.com/?url=https://cloud.example.com&user={uid}`

## Known Issues
It seems that Nextcloud 18 under some circumstances doesn't correctly respond to an API call when using a wrong username/password.
If you have a typo in your user credentials `Error: The API response was empty` will be displayed in this case.

## Development
I will try to maintain and enhance this script as long as Nextcloud does not provide (better) GUI based user and group export functions.
Any hints to enhancements or security issues are highly welcome.
If you would like to contribute, please open an issue or a pull request.

You can still use the simpler v0.4.1, if you prefer.

## Additional Info
Inspired by comments to this github issue:
https://github.com/nextcloud/server/issues/14715

## Screenshots
**Login page**
![nextcloud-userexport_v1 0 0_login_page](https://user-images.githubusercontent.com/29312856/75972989-bcb0c300-5ed4-11ea-9024-401e0f13d87c.png)

**Data selection**
![nextcloud-userexport_v1 0 0_users_page](https://user-images.githubusercontent.com/29312856/75974056-7b211780-5ed6-11ea-890f-ca5c35a82631.png)

**Userlist**
![nextcloud-userexport_v1 0 0_users_details_page](https://user-images.githubusercontent.com/29312856/75973031-ce926600-5ed4-11ea-9f12-360c9dfdc10a.png)
