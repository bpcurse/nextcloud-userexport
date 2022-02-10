<?php

/**
* ### AUTHENTICATION SETTINGS ###
*
* Default values for
* - target URL
* - username
* - password
*/

/**
* Default target URL
*
* e.g. https://example.com
*
* DEFAULT '' (empty)
*/

$target_url = '';

/**
* Default username
*
* DEFAULT '' (empty)
*/

$user_name = '';

/**
* Default password (NOT RECOMMENDED - DANGEROUS)
*
* DEFAULT '' (empty)
*/

$user_pass = '';

/**
* ### SECURITY SETTINGS ###
*
* - security token
* - disallow https override
* - set frame ancestors
*/

/**
* Security token that must be provided to access the login page (either as GET parameter or manually)
*
* e.g. 'somerandomstring'
*
* DEFAULT '' (empty)
*/

$access_token = '';

/**
* Disallow insecure http:// connections even if !http:// override option has been specified.
*
* true or false
*
* DEFAULT false
*/

$https_strict = false;

/**
* Allowed frame ancestors (incl. https://)
* e.g. your cloud URL if you want to open the script from external sites app
*
* Multiple URLs can be set by separating them with a space
* e.g. 'https://cloud.example.com https://someotherurl.com'
*
* DEFAULT '' (empty)
*/

$frame_ancestors = '';

/**
* ### MISCELLANEOUS OPTIONS ###
*/

/**
* UI language
*
* Available languages can be found in the l10n directory
* e.g. 'en.php'
*
* DEFAULT 'en'
*/

$language = 'en';

/**
* Default columns to show/export
*
* Available columns are:
* id, displayname, email, lastLogin, backend, enabled, quota, used,
* percentage_used, free, groups, subadmin, language and locale
*
* DEFAULT ['id', 'displayname', 'email', 'lastLogin']
*
* Has to be set as an array ['choice1', 'choice2', 'choice3', ...]
*/

$data_choices = ['id', 'displayname', 'email', 'lastLogin'];

/**
* Default group filter
*
* filter_group has to be an exact match with an existing groupname
* only a single group can be selected ATM
*
* DEFAULT '' (empty)
*/

$filter_group = '';

/**
* Define +/- tolerance for disk space "almost equal to" filters in a
* decimal number representing percent (e.g. 0.15 for 15 %)
*
* DEFAULT 0.3 (30 %)
*/

$filter_tolerance = 0.3;

/**
* Define default quota in GiB for quota filters
*
* DEFAULT 20 (20 GiB)
*/

$filter_quota = 20;

/**
* Define threshold under which the size is displayed as '< value' because it is negligible
*
* Set SI unit as second array value y [x,y]
* KiB = 1, MiB = 2, GiB = 3, ...
*
* DEFAULT [10,2] (10 MiB)
*/

$negligible_limit = [10,2];

/**
* Define threshold under which the usage (%) is displayed as '< value %' because it is negligible
*
* DEFAULT 0 (0 %)
*/

$negligible_limit_percent = 0;

/**
* UI DESIGN
*
* - HTML body background
* - navigation (menu bar) elements
* - buttons
* - tables
*
* Use html color codes
*/

/**
* HTML Body (Main) Background
**/

// $body_background_color = '';

/**
* Navigation (Menu)
**/

// $navigation_background_color = '';
// $navigation_text_color = '';

// $navigation_current_background_color = '';
// $navigation_current_text_color = '';

// $navigation_hover_background_color = '';
// $navigation_hover_text_color = '';

/**
* Buttons
**/

// $button_connect_background_color = '';
// $button_connect_text_color = '';

// $button_display_background_color = '';
// $button_display_text_color = '';

// $button_download_background_color = '';
// $button_download_text_color = '';

// $button_email_background_color = '';
// $button_email_text_color = '';

/**
* Tables
**/

// $table_header_background_color = '';
// $table_header_text_color = '';

/**
* ### DEBUGGING AND PERFORMANCE ###
*
* - user chunk size (how many users will be queried 'at once' by curl_multi)
* - debug log (creates a log file on each run)
*/

/**
* Maximum number of parallel requests (reducing this number can help with issues due to too many requests to the Nextcloud server, like missing user data)
* This sets curl_multi_options "CURLMOPT_MAX_TOTAL_CONNECTIONS"
*
* In case of missing user data (empty rows) try to set it to a low number (e.g. 10).
* If it works you can raise the number (or lower it otherwise) until you find a reliably working configuration for your setup.
*
* Generally lower numbers will have a negative impact on performance.
* If you set it to 1 the script performs like versions before v0.2.0 (miserably).
*
* OPTIONS false or an integer > 0
*
* DEFAULT false (no chunking, best performance)
**/

$user_chunk_size = false;

/**
* Debug logging
*
* This will create and save a debug file containing detailed information about each step on every run
* WARNING The debug log file contains sensitive data (user data)
*
* OPTIONS true, display_only, false
*
* DEFAULT false
*/

$debug_log = false;
