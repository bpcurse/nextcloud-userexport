<?php

// Save the script's start timestamp to measure execution time
define('TIMESTAMP_SCRIPT_START', microtime(true));
// Set temporary folder to store csv files in
define('TEMP_FOLDER', 'export_temp');

// Set constants to POST values
define('USER_NAME', $_POST['user']);
define('USER_PASS', $_POST['pass']);
define('EXPORT_TYPE', $_POST['export_type']);
define('MESSAGE_MODE', $_POST['msg_mode']);

// Filter POST array and save keys with value 'true' as constant
define('EXPORT_CHOICES', array_keys($_POST,'true'));

// Check if plain HTTP is used without override command and exit if not
define('TARGET_URL', check_https($_POST['url']));

// Initialize cURL handle to fetch user id list and set options
$ch = curl_init();
curl_set_options($ch);

// Fetch raw userlist and store user_ids in $users
$users_raw = json_decode(curl_exec($ch), true);

// Drop cURL handle
curl_close($ch);

// Check if the userlist has been received and save user IDs to $users
if (isset($users_raw['ocs']['data']['users'])) {
  $users = $users_raw['ocs']['data']['users'];

  // Initialize cURL multi handle for parallel requests
  $mh = curl_multi_init();

  /**
    * Initialize individual cURL handles, set options and append them to
    * multi handle list
    */
  // Iterate through userlist
  foreach ($users as $key => $user_id) {
    // Initialize cURL handle
    $curl_requests[$key] = curl_init();
    // Set cURL options for this handle
    curl_set_options($curl_requests[$key], $user_id);
    // Add created handle to multi handle list
    curl_multi_add_handle($mh, $curl_requests[$key]);
  }

  /**
    * Fetch user data via cURL using parallel connections (curl_multi_*)
    */
  do {
    $status = curl_multi_exec($mh, $active);
    if ($active) {
      curl_multi_select($mh);
    }
  } while ($active && $status == CURLM_OK);

  /**
    * Save content to $selected_user_data
    */
  //Iterate through $curl_requests (the cURL handle list)
  foreach ($curl_requests as $key => $request) {
    // Get content of one user data request, store in $single_user_data
    $single_user_data = json_decode(
      curl_multi_getcontent($curl_requests[$key]),
      true);

    // Call select_data function to filter/format request data
    $selected_user_data[] = select_data($single_user_data, $key);

    // Call select_data function again to filter/format request data as utf8 for csv file creation
    $selected_user_data_utf8[] = select_data($single_user_data, $key, 'utf8');

    // Remove processed cURL handle
    curl_multi_remove_handle($mh, $curl_requests[$key]);
  }
  // Drop cURL multi handle
  curl_multi_close($mh);

  // Create and populate CSV file with selected user data and set filename constant
  define('CSV_FILENAME', build_csv_file());

  // Show some status information (processing time, number of exported accounts, ...)
  print_status_message();
  // Show buttons for downloading csv file and mass email function
  show_control_buttons();

  /**
    * Display results page either as HTML table or comma separated values (CSV)
    */
  if (EXPORT_TYPE == 'table') {
    echo build_table_user_data();
  } elseif (EXPORT_TYPE == 'csv') {
      echo build_csv_user_data();
  }
}

/***************************************************************************/

/**
  * Set cURL options
  *
  * @param $ch          cURL handle
  * @param $user_id     User ID of the target user
  * OPTIONAL            DEFAULT: null
  *
  */
function curl_set_options($ch, $user_id = null) {
  if($user_id !== null) {
    $user_id = '/' . rawurlencode($user_id);
  }
  curl_setopt($ch, CURLOPT_URL, TARGET_URL . '/ocs/v1.php/cloud/users' . $user_id);
  curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
  curl_setopt($ch, CURLOPT_USERPWD, USER_NAME . ':' . USER_PASS);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'OCS-APIRequest: true',
    'Accept: application/json'
  ]);
}

/**
  * Check secure outgoing connection
  *
  * Depending on the first five chars of the supplied URL:
  * - In case 'https' -> return unchanged URL
  * - In case '!http' -> remove '!' and return trimmed URL
  * - In case 'http:' or anything else -> exit with insecure connection warning
  *
  * @param  $url  URL to be processed
  *
  * @return $url  URL after processing
  *
  */
function check_https($url) {
  // Save the first five chars of the URL to a new variable '$trim_url'
  $trim_url = substr($url,0,5);

  // Check if plain HTTP is used without override command and exit if not
  if ($trim_url != 'https' && $trim_url != '!http') {
    exit('<font color="red" face="Helvetica"><hr>
    <b>The use of plain HTTP and other protocols is blocked for security reasons.</b>
    <br>Please use HTTPS instead.
    <font color="black"><hr><br>
    You can override this safety precaution and send your admin credentials
    <u><b>unencrypted</b></u> if you really need to by inserting \'!\' before \'http\'
    <br>e.g.: !http://cloud.example.com</font>');
  }

  // Remove '!' if HTTPS check override is selected by use of '!http'
  if ($trim_url == '!http') {
    $url = ltrim($trim_url,'!');
  }
  return $url;
}

/**
  * Select elements from array "$data" and decode UTF8 or not
  * depending on parameters
  *
  * @param $data    Single user record data array
  * @param $type    If not 'utf8', UTF8 will be decoded for browser display
  * OPTIONAL        DEFAULT: null
  *
  * @return $selected_data  Result of $data filtering
  */
function select_data($data, $key, $type = null) {
  global $users;
  if ($data['ocs']['meta']['statuscode'] == 997) {
    $selected_data[] = $users[$key];
    for ($i = 1; $i < count(EXPORT_CHOICES); $i++) {
      $selected_data[] = 'N/A';
    }
  }
  // Prepare data for csv file export if $type = 'utf8'
  else {
    // Iterate through chosen data sets
    foreach(EXPORT_CHOICES as $key => $item) {
      // Filter/format different data sets
      switch ($item) {
        case 'id':
        case 'displayname':
          $selected_data[] = $type != 'utf8'
            // Apply utf8_decode on ID and displayname
            ? utf8_decode($data['ocs']['data'][$item])
            : $data['ocs']['data'][$item];
          break;
        // Convert email data set to lowercase
        case 'email':
          $selected_data[] = strtolower($data['ocs']['data'][$item]);
          break;
        case 'lastLogin':
          $last_login = $data['ocs']['data'][$item];
          // If user has never logged in set $last_login to '-'
          $selected_data[] = $last_login == 0 ? '-' :
            // Format unix timestamp to YYYY-MM-DD after trimming last 3 chars
            date("Y-m-d",substr($last_login,0,10));
          break;
        // Make the display of 'enabled' bool pretty in the browser
        case 'enabled':
        $selected_data[] = $type == 'utf8'
          ? $data['ocs']['data'][$item]
          : ($data['ocs']['data'][$item] == true
            ? '<span style="color: green">&#10004;</span>'
            : '<span style="color: red">&#10008;</span>');
          break;
        case 'total':
        case 'used':
        case 'free':
          $selected_data[] = $type != 'utf8'
            ? format_size($data['ocs']['data']['quota'][$item])
            : $data['ocs']['data']['quota'][$item];
          break;
        // Convert arrays 'subadmin' and 'groups' to comma separated values and wrap them in parentheses if not null
        case 'subadmin':
        case 'groups':
          $selected_data[] = $type != 'utf8'
            ? utf8_decode(build_csv_line($data['ocs']['data'][$item], true))
            : build_csv_line($data['ocs']['data'][$item]);
          break;
        // If none of the above apply
        default:
          $selected_data[] = $data['ocs']['data'][$item];
      }
    }
  }
  return $selected_data;
}

/**
  * Print status message
  *
  * Status message contains user count, target instance, export timestamp and
  * runtime in seconds
  * Change the standard used to display the timestamp to your needs
  *
  */
function print_status_message() {
  global $selected_user_data;

  // Calculate total script runtime
  $timestamp_script_end = microtime(true);
  $time_total = number_format(round(
                $timestamp_script_end - TIMESTAMP_SCRIPT_START, 1),1);

  // Output status message
  echo '<font face="Helvetica">
    Exported ' . count($selected_user_data) . ' records from ' . TARGET_URL .
    ' on ' . date(DATE_RFC1123) . ' in ' . $time_total . ' seconds.<hr>';
}

/**
  * Show control buttons on results page
  *
  * Provides buttons for CSV file download and mass mail via 'mailto:' string
  *
  */
function show_control_buttons() {
  // Build and store email list formatted as 'mailto:'
  $mailto_list = build_mailto_list();
  // Show download CSV file button, POST hidden form items to download.php on click
  echo
  '<form method="post" action="download.php"><font face="Helvetica">
    <input type="hidden" name="file" value="' . CSV_FILENAME . '">
    <input type="hidden" name="mime" value="application/csv">
    <input type="hidden" name="temp" value="' . TEMP_FOLDER . '">
    <input style="background-color: green; color: white; height: 35px"
      type="submit" name="submit" value="Download CSV file">';

  // Show mass mail button (only if email addresses were provided)
  if ($mailto_list != false) {
    echo
    ' <input style="background-color: blue; color: white; height: 35px"
      type="button" onclick="window.location.href = \'' . $mailto_list .
      '\'" value="Write email to all users"/>';
  }
  echo '</form><hr>';
}

/**
  * Source of the following function 'random_str':
  * https://stackoverflow.com/questions/4356289/php-random-string-generator/31107425#31107425
  *
  * Generate a random string, using a cryptographically secure
  * pseudorandom number generator (random_int)
  *
  * This function uses type hints now (PHP 7+ only), but it was originally
  * written for PHP 5 as well.
  *
  * For PHP 7, random_int is a PHP core function
  * For PHP 5.x, depends on https://github.com/paragonie/random_compat
  *
  * @param int $length      How many characters do we want?
  * @param string $keyspace A string of all possible characters
  *                         to select from
  * @return string
  *
  */
function random_str(
  int $length = 64,
  string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
  ): string {
    if ($length < 1) {
      throw new \RangeException("Length must be a positive integer");
    }
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
      $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}

/**
  * Build CSV file
  *
  * Creates a file containing provided array data as comma separated values
  *
  * @param $list            Data array containing e.g. collected user data
  * OPTIONAL                DEFAULT: global $selected_user_data_utf8
  * @param $headers         CSV line containing the column headers
  * OPTIONAL                DEFAULT: List from EXPORT_CHOICES constant
  *
  * @return $csv_filename   Filename of the newly created file
  *
  */
function build_csv_file($list = 'default', $headers = 'default') {
  // Invoke global variables
  global $selected_user_data_utf8;

  // Delete contents of temporary folder, if file was not deleted after last run
  delete_folder_content(TEMP_FOLDER);

  // Create headers from EXPORT_CHOICES key list if not supplied
  if ($headers == 'default') {
    $headers = build_csv_export_key_list();
  }
  // Import $list from global $selected_user_data_utf8
  if ($list == 'default') {
    $list = $selected_user_data_utf8;
  }
  // Set random filename
  $csv_filename = random_str(32) . '.csv';
  // Check if temporary folder already exists, else make directory
  if (!file_exists(TEMP_FOLDER)) {
    mkdir(TEMP_FOLDER, 0755, true);
  }
  // Create/open file with write access and return file handle
  $csv_file = fopen(TEMP_FOLDER . '/' . $csv_filename,"w");
  // Set file permissions (rw-r-----)
  chmod(TEMP_FOLDER . '/' . $csv_filename, 0640);
  // Write selected headers as first line to file
  fwrite($csv_file, $headers . "\n");
  // Iterate through provided data array and append each line to the file
  foreach ($list as $line) {
    fputcsv($csv_file, $line);
  }
  // Close active file handle
  fclose($csv_file);
  return $csv_filename;
}

/**
  * Delete content of specified folder
  *
  * Do nothing if no foldername or the script base folder is provided
  *
  * @param $folder  Foldername to delete content in
  *
  */
function delete_folder_content($folder) {
  if ($folder == null || $folder == ".") {
    return;
  }
  // Get filelist from target folder
  $files = glob($folder . '/*');
  // Iterate through filelist and delete all except hidden files (e.g. .htaccess)
  foreach($files as $file) {
    if(is_file($file)) {
      unlink($file);
    }
  }
}

/**
  * Build and format userlist
  *
  * Creates and returns a formatted HTML table containing the userlist
  *
  * @return $table_user_data_style concatenated with $table_user_data_headers and $table_user_data
  *
  */
function build_table_user_data() {
  // Invoke global variables
  global $selected_user_data;
  // Define table CSS style
  $table_user_data_style =
  '<style>
    table {border-collapse: collapse;font-family: "Helvetica";}
    table,td,th {border: 1px solid #ddd;}
    th {text-align: left;
      background-color: #4C6489;
      color:white;
      padding: 8px 4px 4px;}
    td {padding: 4px 4px 0px;}
    tr:nth-child(even) {background-color: #f2f2f2;}
  </style>';

  // Define HTML table and set header cell content
  $table_user_data_headers = '<table><tr>';
  foreach(EXPORT_CHOICES as $item) {
    $table_user_data_headers .= '<th>' . $item . '</th>';
  }
  '</tr>';

  // Search for and return position of quota keys in EXPORT_CHOICES constant
  $keypos_right_align[] = array_search('total',EXPORT_CHOICES);
  $keypos_right_align[] = array_search('used',EXPORT_CHOICES);
  $keypos_right_align[] = array_search('free',EXPORT_CHOICES);

  // Search for and return position of 'enabled' in EXPORT_CHOICES constant
  $keypos_center_align = array_search('enabled',EXPORT_CHOICES);

  // Iterate through collected user data by row and column, build HTML table
  for ($row = 0; $row < sizeof($selected_user_data); $row++) {
    $table_user_data .= '<tr>';
    for ($col = 0; $col < sizeof($selected_user_data[$row]); $col++) {
      $color_text = 'color: unset';
      if ($selected_user_data[$row][$col] == 'N/A') {
        $color_text = 'color: grey;';
      }
      if (in_array($col, array_filter($keypos_right_align))) {
        $table_user_data .= '<td style="text-align:right;white-space:nowrap;' . $color_text . '">'
          . $selected_user_data[$row][$col] . '</td>';
      } elseif ($col === $keypos_center_align) {
        $table_user_data .= '<td style="text-align:center;' . $color_text . '">'
          . $selected_user_data[$row][$col] . '</td>';
      } else {
        $table_user_data .= '<td style="' . $color_text . '">'.$selected_user_data[$row][$col].'</td>';
      }
    }
    $table_user_data .= '</tr>';
  }
  $table_user_data .= '</table>';
  return $table_user_data_style . $table_user_data_headers . $table_user_data;
}

/**
  * Build 'mailto:' string
  *
  * Creates a string in 'mailto:' notation containing all user emails
  *
  * @param  $type         Send emails as 'to', 'cc' or 'bcc'
  * OPTIONAL              DEFAULT: 'bcc'
  *
  * @return $mailto_list  Exported emails as mailto: string for mass mailing
  *
  */
function build_mailto_list($type = 'bcc') {
  // Invoke global variables
  global $selected_user_data;
  // If a custom message mode was chosen, set $type to MESSAGE_MODE constant
  if (MESSAGE_MODE == 'cc' || MESSAGE_MODE == 'to') {
    $type = MESSAGE_MODE;
  }

  // Search for and return position of key 'email' in EXPORT_CHOICES constant
  $keypos = array_search('email',EXPORT_CHOICES);

  // Begin if 'email' key is present
  if ($keypos != null) {
    // Initiate construction of mailto string, setting 'to:', 'cc:' or 'bcc:'
    $mailto_list = 'mailto:?' . $type . '=';
    // Iterate through collected user data and add email addresses
    for ($row = 0; $row < sizeof($selected_user_data); $row++) {
      if ($selected_user_data[$row][$keypos] == 'N/A') {
        continue;
      }
      if ($row == 0) {
        $mailto_list .= $selected_user_data[$row][$keypos];
      } else {
        $mailto_list .= ',' . $selected_user_data[$row][$keypos];
      }
    }
    // Set email subject
    $mailto_list .= '&subject=All%20user%20mail';
    return $mailto_list;
  } else {
    // Return false if mailto list has not been constructed due to missing email data
    return false;
  }
}

/**
  * CSV string creation
  *
  * Build CSV formatted string containing the user data
  *
  * @return $csv_user_data csv  Formatted string containing the user data
  *
  */
function build_csv_user_data($delimiter = ',') {
  // Invoke global variables
  global $selected_user_data;
  // Add headers to $csv_user_data variable
  $csv_user_data .= build_csv_export_key_list($delimiter) . '<br>';

  // Iterate through collected user data by row and column, build CSV output
  for ($row = 0; $row < sizeof($selected_user_data); $row++) {
    for ($col = 0; $col < sizeof($selected_user_data[$row]); $col++) {
      // To prevent possible import issues, quote data cells of type string containing spaces
      if (is_string($selected_user_data[$row][$col])) {
        if ($selected_user_data[$row][$col] !=
          trim($selected_user_data[$row][$col])) {
            $csv_user_data .= '"' . $selected_user_data[$row][$col] . '"';
        }
        else {
          $csv_user_data .= $selected_user_data[$row][$col];
        }
      }
      else {
        $csv_user_data .= $selected_user_data[$row][$col];
      }
      // Put column separators between cells but not at the end of a record
      if ($col != sizeof($selected_user_data[$row])) {
        $csv_user_data .= $delimiter;
      }
    }
    // Indicate the start of a new record
    $csv_user_data .= '<br>';
  }
  return $csv_user_data;
}

/**
  * Source of the following function 'format_size':
  * https://stackoverflow.com/questions/15188033/human-readable-file-size
  *
  * Convert byte size descriptors shown as integers to a human readable format
  *
  * slightly adapted
  *
  */
function format_size($size) {
  if ($size == 0) {
    return "-";
  }

  $s = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
  $e = floor(log($size, 1024));

  return number_format(round($size/pow(1024, $e), 2),2).' '.$s[$e];
}

// Merge the following two functions into one if reasonable - TODO

/**
  * Build a comma separated line from a given array
  *
  * @param $array       Array to
  * @param $space       Puts a space behind each delimiter if true
  * OPTIONAL            DEFAULT: false
  * @param $delimiter   Which char to put between cells
  * OPTIONAL            DEFAULT: ','
  *
  * @return $csv_line   CSV formatted string
  *
  */
function build_csv_line($array, $space = false, $delimiter = ',') {
  $i = 0;
  foreach($array as $item) {
    if ($i == 0) {
      $csv_line .= $item;
    }
    elseif ($space == false) {
      $csv_line .= $delimiter . $item;
    } else {
      $csv_line .= $delimiter . ' ' . $item;
    }
    $i++;
  }
  return $csv_line;
}

/**
  * Build a comma separated line from EXPORT_CHOICES constant
  *
  * @param  $delimiter  Which char to put between cells
  *
  * @return $csv_line   CSV formatted string
  *
  */
function build_csv_export_key_list($delimiter = ',') {
  $i = 0;
  foreach (EXPORT_CHOICES as $item) {
    if ($i == 0) {
      $csv_line = $item;
    } else {
      $csv_line .= $delimiter . $item;
    }
    $i++;
  }
  return $csv_line;
}

// EOF
