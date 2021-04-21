<?php

/**
  * Authentication settings
  */
// Default target URL
// $target_url = 'https://example.com';

// Default username
// $user_name = 'someuser';

// Default password (NOT RECOMMENDED - DANGEROUS)
// $user_pass = 'goodpassword';

/**
  * Security settings
  */
// Allowed frame ancestors e.g. your cloud URL incl. https:// (if you want to open the script from external sites app)
// Multiple URLs can be set by separating them with a space
// $frame_ancestors = 'https://cloud.example.com';

/**
  * Folder settings
  */
// Folder to temporarily store csv files
define('TEMP_FOLDER', 'export_temp');

/**
  * Set UI language
  * Available languages can be found in the l10n directory
  * e.g. 'en.php'
  *
  * DEFAULT 'en'
  *
  */
$language = 'en';

/**
  * Define +/- tolerance for disk space "almost equal to" filters in a
  * decimal number representing percent (e.g. 0.15 for 15 %)
  *
  * DEFAULT 30 % ('0.3')
  *
  */

$filter_tolerance = 0.3;

/**
  * Define default quota in GiB for quota filters
  *
  * DEFAULT 20 GB ('20')
  *
  */

$filter_quota = 20;

/**
  * Alter UI design
  *
  * Use html color codes or
  *
  */
/** HTML Body (Main) Background **/

// $body_background_color = '';

/** Navigation (Menu) **/

// $navigation_background_color = '';
// $navigation_text_color = '';

// $navigation_current_background_color = '';
// $navigation_current_text_color = '';

// $navigation_hover_background_color = '';
// $navigation_hover_text_color = '';

/** Buttons **/

// $button_connect_background_color = '';
// $button_connect_text_color = '';

// $button_display_background_color = '';
// $button_display_text_color = '';

// $button_download_background_color = '';
// $button_download_text_color = '';

// $button_email_background_color = '';
// $button_email_text_color = '';

/** Tables **/

// $table_header_background_color = '';
// $table_header_text_color = '';

// EOF
