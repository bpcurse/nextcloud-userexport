# Nextcloud userexport
PHP script to export users, groups and groupfolders using Nextcloud's OCS APIs `user metadata`, `capabilities` and `groupfolders` via cURL.

## How it works
The script uses cURL to make calls to Nextcloud's OCS APIs and displays the results either through an HTML table or a CSV list that can be easily copied to calc/excel. You can download a CSV formatted file as well.

https://docs.nextcloud.com/server/latest/developer_manual/client_apis/OCS/ocs-api-overview.html#user-metadata
https://docs.nextcloud.com/server/latest/developer_manual/client_apis/OCS/ocs-api-overview.html#capabilities-api
https://github.com/nextcloud/groupfolders#api

## Installation
- Upload all files to a directory on your webserver and open `index.php` in a browser. You can point a subdomain like `https://export.cloud.example.com` at it.
- **Make sure it is only accessible via https://** as you will be providing Nextcloud admin credentials to it.

## Update
- If you overwrite an existing installation, remove the old files from the folder before you upload. Else you might be left with unnecessary files (although it's not an issue).
Remember to backup `config.php` if you have set non-default values.

## General usage
- Enter the URL of the Nextcloud target instance (https:// will be prepended automatically, if no protocol is specified)
- Enter a username (userID) that has admin (or group admin) rights
- Enter the corresponding password
- Click on "connect" and wait (there is no progress indicator yet)

After the script has successfully downloaded user, group (and groupfolder) data you can access other options from the top navigation bar.

## Security
- Do not use `http://` unless you have a very good reason to do so.
The script will block outgoing plain HTTP connections and warn you unless you override this security measure with `!http://...`

- Group admins (as long as they do not belong themselves to the `admin` group) will not receive information on users that are members of `admin` group, even if they are members of the group the group admin manages. This is a restriction of Nextcloud itself.

## Performance
API calls via cURL are slow. **Querying several hundred user accounts can take some minutes**. Be patient :)

CURL parallel requests have been implemented since v0.2.0 and provide a relevant speed boost, but that's about as fast as it gets.
Approximately 10-15s/100users.

A progress indicator is on the wish list.

## Menu items in top nav bar

### `Users`
- Choose which user metadata should be displayed or downloaded by selecting checkboxes
- Change display type (if necessary) and click on display **OR**
- Change the column headers option (if necessary) and download data as CSV file by clicking the download button

### `Groups`
- Change display type (if necessary) and click on display **OR**
- Change the column headers option (if necessary) and download data as CSV file by clicking the download button

### `Groupfolders`
(only visible if groupfolders app is active and at least one groupfolder is in use)
- Change display type (if necessary) and click on display **OR**
- Change the column headers option (if necessary) and download data as CSV file by clicking the download button

All tables can be sorted by clicking on the column headers (although not by size).

### `Email`
- Select send mode ('bcc', 'cc' or 'to')
- Select all users or a specific group from the dropdown list
- Set filters (can be combined)
  - last login between two dates (including the selected days)
  - quota usage over a certain amount of Gigabytes (selectable in 0.5 GB steps)

Clicking 'create list' will open your email application with a 'mailto:' string containing all (filtered) email addresses.

### `Statistics`
- Simple overview of user/group/(groupfolder) count and quotas.

### `Logout`
- This will unset (clear) php session data

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

## Configuration
Several options can be set in config.php. The options are described in comments.
- Authentication
- Folders
- UI language
- UI colors

## Localization
This software can be translated by adding a language file to the `l10n` directory. To translate, duplicate an existing {ISO lang code}.php file, preferably en.php, rename it to the new language code and start translating by editing the lines inside.
An overview of ISO language codes can be found in the `l10n` directory, too.

At present, included languages are English (default) and German. Set the language option inside config.php to select a language.

## Nextcloud integration
You can integrate it by using the external sites app.
- Show it only to your admin group
- Set UI colors to match your theming
- Prefill URL and username by using GET parameters and a nextcloud placeholder
`https://export.cloud.mydomain.com/?url=https://cloud.example.com&user={uid}`

## Known Issues
Sorting tables by sizes (quota) is not possible, yet. (The sort function cannot handle human readable formats like `50 GB`)

## Development
Any hints to enhancements or security issues are highly welcome.
If you would like to contribute, please open an issue or a pull request.

Minor version updates and bugfixes (x.x.1) are not always released separately. If you want to use the latest version please download/clone from master.

You can still use the simpler v0.4.1, if you prefer.

## Additional Info
Inspired by comments to this github issue:
https://github.com/nextcloud/server/issues/14715

## Screenshots (still v1.0.0, TODO)
**Login page**
![nextcloud-userexport_v1 0 0_login_page](https://user-images.githubusercontent.com/29312856/75972989-bcb0c300-5ed4-11ea-9024-401e0f13d87c.png)

**Data selection**
![nextcloud-userexport_v1 0 0_users_page](https://user-images.githubusercontent.com/29312856/75974056-7b211780-5ed6-11ea-890f-ca5c35a82631.png)

**Userlist**
![nextcloud-userexport_v1 0 0_users_details_page](https://user-images.githubusercontent.com/29312856/75973031-ce926600-5ed4-11ea-9f12-360c9dfdc10a.png)
