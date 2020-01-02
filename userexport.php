<?php

/**
  * v0.2.0 2020-01-03
  */

// Save the script's start timestamp
$timestamp_script_start = microtime(true);

// Set variables to POST values
$target_url = $_POST['url'];
$user_name = $_POST['user'];
$user_pass = $_POST['pass'];
$export_type = $_POST['export_type'];
$message_mode = $_POST['msg_mode'];

// Check if plain HTTP is used without override command and exit if not
$target_url = check_https($target_url);

// Initialize cURL connection to fetch user id list and set options
$ch = curl_init();
curl_set_options($ch, $user_name, $user_pass);

// Fetch raw userlist and store user_ids in $users
$users_raw = json_decode(curl_exec($ch), true);
curl_close($ch);

// Check if the list has been received and save only user_ids to $users
if (isset($users_raw['ocs']['data']['users'])) {
  $users = $users_raw['ocs']['data']['users'];

  // Initialize cURL multi handle for parallel requests
  $mh = curl_multi_init();

  // Iterate through users init cURL handles and add them to cURL handle list
  foreach ($users as $key => $user_id) {
    $curl_requests[$key] = curl_init();
    curl_set_options($curl_requests[$key], $user_name, $user_pass, $user_id);
    curl_multi_add_handle($mh, $curl_requests[$key]);
  }

  // Fetch user data via cURL using multiple parallel connections
  do {
    $status = curl_multi_exec($mh, $active);
    if ($active) {
      curl_multi_select($mh);
    }
  } while ($active && $status == CURLM_OK);

  // Save content to $collected_user_data
  foreach ($curl_requests as $key => $request) {
    $single_user_data = json_decode(
      curl_multi_getcontent($curl_requests[$key]),
      true);
    $collected_user_data[] = select_data($single_user_data, $export_type);
    $collected_user_data_utf8[] = select_data($single_user_data, 'utf8');
    curl_multi_remove_handle($mh, $curl_requests[$key]);
  }
  curl_multi_close($mh);

  $temp_folder = 'export_temp';
  delete_folder_content($temp_folder);
  $csv_filename = build_csv_file($collected_user_data_utf8, $temp_folder);

  // Display HTML table or comma separated values
  if ($export_type == 'table') {
    print_status_message($target_url);
    show_control_buttons($csv_filename, $temp_folder);
    echo build_table_user_data();
  } elseif ($export_type == 'csv') {
      print_status_message($target_url);
      show_control_buttons($csv_filename, $temp_folder);
      echo build_csv_user_data();
  }
}

/**
  * Set cURL options
  *
  * @param $ch        cURL handle
  * @param $url       Nextcloud target instance URL
  * @param $user_name Username
  * @param $user_pass Password
  * @param $user_id   User ID of the target user
  */
function curl_set_options($ch, $user_name, $user_pass, $user_id = null) {
  global $target_url;
  if($user_id !== null) {
    $user_id = '/' . $user_id;
  }
  curl_setopt($ch, CURLOPT_URL, $target_url . '/ocs/v1.php/cloud/users' . $user_id);
  curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
  curl_setopt($ch, CURLOPT_USERPWD, $user_name . ':' . $user_pass);
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
  * @param $url  URL to be processed
  *
  * @return $url URL after processing
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

  // Remove '!'' if HTTPS check override is selected by use of '!http'
  if ($trim_url == '!http') {
    $url = ltrim($trim_url,'!');
  }
  return $url;
}

/**
  * Select elements "user_id", "displayname", "email" and "lastLogin"
  * from array "$data" and decode UTF8 or not depending on parameters
  *
  * @param $data  Single user record data array
  * @param $type  If not 'utf8', UTF8 will be decoded for browser display
  *
  * @return $selected_data  Result of $data filtering
  */
function select_data($data, $type) {
  if ($type == 'utf8') {
    return $selected_data = array(
      $data['ocs']['data']['id'],
      $data['ocs']['data']['displayname'],
      strtolower($data['ocs']['data']['email']),
      date("Y-m-d",
        substr($data['ocs']['data']['lastLogin'],0,10)
      )
    );

// In case of HTML table or CSV list to be displayed in the browser decode UTF8
  } else {
      return $selected_data = array(
        utf8_decode($data['ocs']['data']['id']),
        utf8_decode($data['ocs']['data']['displayname']),
        strtolower($data['ocs']['data']['email']),
        date("Y-m-d",
          substr($data['ocs']['data']['lastLogin'],0,10)
        )
      );
    }
}


/**
  * Print status message
  *
  * Status message contains user count, target instance, export timestamp and
  * runtime in seconds
  * Change the standard used to display the timestamp to your needs
  *
  * @param $to_count  Array variable to be counted ($collected_user_data)
  * @param $url       URL of the target nextcloud instance
  */
function print_status_message() {
  global $timestamp_script_start, $collected_user_data, $target_url;

  // Calculate total script runtime
  $timestamp_script_end = microtime(true);
  $time_total = round($timestamp_script_end - $timestamp_script_start, 1);

  // Output status message
  echo '<font face="Helvetica">
    Exported ' . count($collected_user_data) . ' records from ' . $target_url .
    ' on ' . date(DATE_RFC1123) . ' in ' . $time_total . ' seconds.<hr>';
}

/**
  * Show control buttons on results page
  *
  * At the moment there's only a solitary download button (CSV file)
  *
  * @param $csv_filename  Filename of the created user records CSV file
  */
function show_control_buttons($csv_filename, $temp_folder) {
  echo
  '<form method="post" action="download.php"><font face="Helvetica">
    <input type="hidden" name="file" value="' . $csv_filename . '">
    <input type="hidden" name="mime" value="application/csv">
    <input type="hidden" name="temp" value="' . $temp_folder . '">
    <input style="background-color: green; color: white; height: 35px"
      type="submit" name="submit" value="Download CSV file">
    <input style="background-color: blue; color: white; height: 35px"
      type="button" onclick="window.location.href = \'' . build_mailto_list() .
      '\';" value="Write email to all users"/>
  </form><hr>';
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
  * Creates a file containing the user data as comma separated values
  *
  * @param $userlist       Collected user data array provided by curl ($collected_user_data)
  *
  * @return $csv_filename  Filename of the newly created file
  */
function build_csv_file($list) {
  global $temp_folder;
  $csv_filename = random_str(32) . '.csv';
  if (!file_exists($temp_folder)) {
    mkdir($temp_folder, 0755, true);
  }
  $csv_file = fopen($temp_folder . '/' . $csv_filename,"w");
  chmod($temp_folder . '/' . $csv_filename, 0640);
  foreach ($list as $line) {
    fputcsv($csv_file, $line);
  }
  fclose($csv_file);
  return $csv_filename;
}

/**
  * Delete content of specified folder
  *
  * Do nothing if no foldername or the script folder is provided
  *
  * @param $folder  Foldername to delete content in
  */
function delete_folder_content($folder) {
  if ($folder == null || $folder == ".") {
    return;
  }
  $files = glob($folder . '/*');
  foreach($files as $file) {
    if(is_file($file)) {
      unlink($file);
    }
  }
}

/**
  * Build and format user list
  *
  * Creates an HTML formatted table containing the user list
  *
  * @param  $collected_user_data  Collected user data array provided by curl
  *
  * @return $table_user_data_style concatenated with $table_user_data
  *         css style and html table
  */
function build_table_user_data() {
  global $collected_user_data;
  // Define CSS style for table
  $table_user_data_style =
  '<style>
    table {border-collapse: collapse;}
    table,td,th {border: 1px solid #ddd;}
    th {text-align: left;
      background-color: #4C6489;
      color:white;
      padding: 8px;
      padding-left: 5px;}
    td {padding: 5px;}
    tr:nth-child(even) {background-color: #f2f2f2;}
  </style>';

  // Define HTML table and set header cell content
  $table_user_data =
  '<table><font face="Helvetica"><tr>
    <th>Username</th>
    <th>Displayname</th>
    <th>Email</th>
    <th>Last login</th></tr>';

  // Iterate through collected user data by row and column, build HTML table
  for ($row = 0; $row < sizeof($collected_user_data); $row++) {
    $table_user_data .= '<tr>';
    for ($col = 0; $col < 4; $col++) {
      $table_user_data .= '<td>'.$collected_user_data[$row][$col].'</td>';
    }
    $table_user_data .= '</tr>';
  }
  $table_user_data .= '</table>';
  return $table_user_data_style . $table_user_data;
}

/**
  * Build and format user list
  *
  * Creates an HTML formatted table containing the user list
  *
  * @param  $type         Send emails as 'to', 'cc' or 'bcc', defaults to 'bcc'
  *
  * @return $mailto_list  Exported emails as mailto: string for mass mailing
  */
function build_mailto_list($type = 'bcc') {
  global $collected_user_data, $message_mode;
  if ($message_mode == 'cc' || $message_mode == 'to') {
    $type = $message_mode;
  }
  $mailto_list = 'mailto:?' . $type . '=';

  // Iterate through collected user data by row, build mailto list
  for ($row = 0; $row < sizeof($collected_user_data); $row++) {
    if ($row == 0) {
      $mailto_list .= $collected_user_data[$row][2];
    } else {
      $mailto_list .= ',' . $collected_user_data[$row][2];
    }
  }
  $mailto_list .= '&subject=All%20user%20mail';
  return $mailto_list;
}

/**
  * CSV string creation
  *
  * Build CSV formatted string containing the user data
  *
  * @param $collected_user_data Collected user data array provided by curl
  *
  * @return $csv_user_data csv  Formatted string containing the user data
  */
function build_csv_user_data() {
  global $collected_user_data;
  // Add headers to $csv_user_data variable
  $csv_user_data = 'User ID,Displayname,Email,Last login<br>';

  // Iterate through collected user data by row and column, build CSV output
  for ($row = 0; $row < sizeof($collected_user_data); $row++) {
    for ($col = 0; $col < 4; $col++) {
      // To prevent possible import issues, quote displayname data cells
      if ($col == 1) {
        $csv_user_data .= '"' . $collected_user_data[$row][$col] . '"';
      }
      else {
        $csv_user_data .= $collected_user_data[$row][$col];
      }
      // Put column separators between cells but not at the end of a record
      if ($col != 3) {
        $csv_user_data .= ',';
      }
    }
    // Indicate the start of a new record
    $csv_user_data .= '<br>';
  }
  return $csv_user_data;
}

// EOF
