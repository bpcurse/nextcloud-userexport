<?php

/**
  * Set cURL options
  *
  * @param $ch          cURL handle
  * @param $type        'users' or 'groups' API
  * @param $user_id     User ID of the target user
  * OPTIONAL            DEFAULT: null
  *
  */
function set_curl_options($ch, $type, $id = null) {
  $id = $id === null ? null : '/' . rawurlencode($id);
  curl_setopt($ch, CURLOPT_URL, $_SESSION['target_url']
    . '/ocs/v1.php/cloud/' . $type . $id);
  curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
  curl_setopt($ch, CURLOPT_USERPWD, $_SESSION['user_name'] . ':'
    . $_SESSION['user_pass']);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'OCS-APIRequest: true',
    'Accept: application/json'
  ]);
}

/**
* Populate session array 'data_options' with all data options that can be selected
*
* @return $_SESSION['data_options']
*/
function set_data_options() {
  $_SESSION['data_options'] = [
    'id' => L10N_USER_ID, 'displayname' => L10N_DISPLAYNAME,
    'email' => L10N_EMAIL, 'lastLogin' => L10N_LAST_LOGIN,
    'backend' => L10N_BACKEND, 'enabled' => L10N_ENABLED,
    'quota' => L10N_QUOTA_LIMIT, 'used' => L10N_QUOTA_USED,
    'free' => L10N_QUOTA_FREE, 'groups' => L10N_GROUPS,
    'subadmin' => L10N_SUBADMIN, 'language' => L10N_LANGUAGE,
    'locale' => L10N_LOCALE];
}

/**
  * Check secure outgoing connection
  *
  * Depending on the first five chars of the supplied URL:
  * - In case 'https' -> return unchanged URL
  * - In case '!http' -> remove '!' and return trimmed URL
  * - In case 'http:' or anything else -> exit with insecure connection warning
  *
  * @param  $input_url    URL to be processed
  *
  * @return $output_url   URL after processing
  *
  */
function check_https($input_url) {
  // Save the first five chars of the URL to a new variable '$trim_url'
  $trimmed_url = substr($input_url,0,5);

  // Check if plain HTTP is used without override command and exit if not

  if ($trimmed_url != 'https' && $trimmed_url != '!http')
    exit('<font color="red" face="Helvetica"><hr>
        <b>' . L10N_HTTP_IS_BLOCKED . '</b>
        <br>' . L10N_HTTPS_RECOMMENDATION . '
      <font color="black"><hr>
        <br>' . L10N_HTTPS_OVERRIDE_HINT . '
        <br>' . L10N_EG . '!http://cloud.example.com
       </font>');

  // Remove '!' if HTTPS check override is selected by use of '!http'
  $output_url = $trimmed_url == '!http'
    ? ltrim($input_url,'!')
    : $input_url;

  return $output_url;
}

/**
  * Remove httpx:// from given URL and return the trimmed version
  *
  * @param  $url      URL to be trimmed
  *
  * @return $trim_url URL without http:// or https://
  *
  */
function removehttpx($input_url) {
  $trimmed_url = preg_replace('(^https?://)', '', $input_url);
  return $trimmed_url;
}

/**
  * Error handling for cURL requests
  *
  * Checks cURL error codes and statuscodes in the API response
  *
  * @param  $ch     cURL handle to be checked
  * @param  $data   variable containing the API data fetched by cURL exec
  *
  */
function check_curl_response($ch, $data) {

  // check if cURL returns an error != 0
  if (curl_errno($ch)) {
    // Iterate through common cURL error codes, exit and return custom error messages or default message
    switch (curl_errno($ch)) {
      case 6:
        exit('
          <font color="red">
            <hr>
            <b>' . L10N_ERROR . 'cURL ('. L10N_STATUSCODE . curl_errno($ch) . ')</b>
            <br>' . curl_error($ch) .
          '<font color="black">
            <hr>' . L10N_ERROR_CURL_CONNECTION . '
          </font>');
      case 51:
        exit('
          <font color="red">
            <hr>
            <b>' . L10N_ERROR . 'cURL ('. L10N_STATUSCODE . curl_errno($ch) . ')</b>
            <br>' . curl_error($ch) .
          '<font color="black">
            <hr>
            ' . L10N_ERROR_URL . '
          </font>');
      default:
        exit('
          <font color="red">
            <hr>
            <b>' . L10N_ERROR . 'cURL ('. L10N_STATUSCODE . curl_errno($ch) . ')</b>
            <br>' . curl_error($ch) . '
            <hr>
          </font>');
    }
  }

  if ($data === null) {
    exit('
      <font color="red">
        <hr>
        <b>' . L10N_ERROR . L10N_ERROR_EMPTY_API_RESPONSE . '</b>
        <hr>
      </font>');
  }
  // Read statuscode from API response
  $status = $data['ocs']['meta']['statuscode'];

  // Iterate through possible statuscode responses
  switch ($status) {
    case 100:
    case 200:
      // 100 or 200 mean OK -> break switch case and continue normal script execution
      break;
    case 404:
      exit('
        <font color="red">
          <hr>
          <b>' . L10N_ERROR . L10N_USER_DOES_NOT_EXIST
            .' ('. L10N_STATUSCODE . $status . ')</b>
          <hr>
        </font>');
      break;
    case 997:
      exit('
        <font color="red">
          <hr><b>' . L10N_ERROR . L10N_AUTHENTICATION . ' ('. L10N_STATUSCODE . $status . ')</b>
          <br>' . L10N_CHECK_USER_PASS . '
        <font color="black">
          <hr>' . L10N_HINT_ADMIN_OR_GROUP_ADMIN . '
        </font>');
    default:
      exit('
        <font color="red">
          <hr>
          <b>' . L10N_ERROR . L10N_UNKNOWN . ' ('. L10N_STATUSCODE . $status . ')</b>
          <hr>
        </font>');
  }
}

/**
  * Fetch the list containing all user IDs from the server
  *
  * @return $users
  *
  */
function fetch_userlist() {
  // Initialize cURL handle to fetch user ID list and set options
  $ch = curl_init();
  set_curl_options($ch, 'users');

  // Fetch raw userlist and store user_ids in $users
  $users_raw = json_decode(curl_exec($ch), true);

  // Check for errors in cURL request
  check_curl_response($ch, $users_raw);

  // Drop cURL handle
  curl_close($ch);

  // Check if the userlist has been received and save user IDs to $users
  if (isset($users_raw['ocs']['data']['users'])) {
    $users = $users_raw['ocs']['data']['users'];
    // Set the session variable 'authenticated' to true to access other pages
    $_SESSION['authenticated'] = true;
  }
  return $users;
}

/**
  * Fetch the list containing all group IDs from the server
  *
  * @return $groups
  *
  */
function fetch_grouplist() {
  // Initialize cURL handle to fetch user id list and set options
  $ch = curl_init();
  set_curl_options($ch, 'groups');

  // Fetch raw userlist and store user_ids in $users
  $groups_raw = json_decode(curl_exec($ch), true);

  // Check for errors in cURL request
  check_curl_response($ch, $groups_raw);

  // Drop cURL handle
  curl_close($ch);

  // Check if the userlist has been received and save user IDs to $users
  if (isset($groups_raw['ocs']['data']['groups'])) {
    $groups = $groups_raw['ocs']['data']['groups'];

    // Initialize cURL multi handle for parallel requests
    $mh = curl_multi_init();
  }
  return $groups;
}

/**
  * Initialize individual cURL handles, set options and append them to multi handle list
  *
  * @return $raw_user_data
  *
  */
function fetch_raw_user_data() {
  // Initialize cURL multi handle for parallel requests
  $mh = curl_multi_init();

  // Iterate through userlist
  foreach ($_SESSION['userlist'] as $key => $user_id) {
    // Initialize cURL handle
    $curl_requests[$key] = curl_init();
    // Set cURL options for this handle
    set_curl_options($curl_requests[$key], 'users', $user_id);
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
    $raw_user_data[] =
      json_decode(
        curl_multi_getcontent($curl_requests[$key])
      ,true);

    // Remove processed cURL handle
    curl_multi_remove_handle($mh, $curl_requests[$key]);
  }
  // Drop cURL multi handle
  curl_multi_close($mh);

  // Calculate total script runtime
  $timestamp_script_end = microtime(true);
  $_SESSION['time_total'] = number_format(round(
                $timestamp_script_end - TIMESTAMP_SCRIPT_START, 1),1);
  return $raw_user_data;
}

/**
* Iterate through userlist and call select_data_single_user each time
*
* @param  $export_choices   Array containing a list of data columns to be taken into account
*         OPTIONAL          DEFAULT: null
* @param  $format           decode UTF8?
*         OPTIONAL          DEFAULT: null (decode UTF8)
*
* @return $selected_user_data
*         ARRAY
*/
function select_data_all_users($export_choices = null, $format = null) {
  $export_choices = $export_choices ?? $_SESSION['data_choices'];
  foreach ($_SESSION['userlist'] as $key => $user_id)
    // Call select_data function to filter/format request data
    $selected_user_data[] = select_data_single_user(
      $_SESSION['raw_user_data'][$key], $user_id, $export_choices, $format);

  return $selected_user_data;
}

/**
  * Select elements from array "$data" and decode UTF8 or not
  * depending on parameters
  *
  * @param  $data           Single user record data array
  * @param  $user_id        ID of the user to be processed
  * @param  $export_choices Array containing a list of data columns to be taken into account
  * @param  $format         If not 'utf8', UTF8 will be decoded for browser display
  *         OPTIONAL        DEFAULT: null
  *
  * @return $selected_data  Result of $data filtering
  *         ARRAY
  */
function select_data_single_user(
  $data, $user_id, $export_choices, $format = null) {
  // If data is not returned due to missing permissions (group admins) set 'N/A' instead
  if ($data['ocs']['meta']['statuscode'] == 997) {
    $selected_data[] = $user_id;
    for ($i = 1; $i < count($export_choices); $i++)
      $selected_data[] = 'N/A';
  }

  // Prepare data for CSV file export if $format = 'utf8'
  else {
    // Iterate through chosen data sets
    foreach($export_choices as $key => $item) {
      $item_data = $data['ocs']['data'][$item];
      // Filter/format different data sets
      switch ($item) {
        case 'id':
        case 'displayname':
          $selected_data[] = $format != 'utf8'
            // Apply utf8_decode on ID and displayname
            ? utf8_decode($item_data) : $item_data;
          break;
        // Convert email data set to lowercase
        case 'email':
          $selected_data[] = $item_data == null ? '-' : strtolower($item_data);
          break;
        case 'lastLogin':
          // If user has never logged in set $last_login to '-'
          $selected_data[] = $item_data == 0
            ? ($format == 'utf8' ? '-'
                : '<span style="color: red;">&#10008;</span>')
            // Format unix timestamp to YYYY-MM-DD after trimming last 3 chars
            : date("Y-m-d", substr($item_data, 0, 10));
          break;
        // Make the display of 'enabled' bool pretty in the browser
        case 'enabled':
        $selected_data[] = $format == 'utf8' ? $item_data
          : ($item_data == true
            ? '<span style="color: green">&#10004;</span>'
            : '<span style="color: red">&#10008;</span>');
          break;
        case 'quota':
        case 'used':
        case 'free':
          $selected_data[] = $format != 'utf8'
            ? format_size($data['ocs']['data']['quota'][$item])
            : $data['ocs']['data']['quota'][$item];
          break;
        // Convert arrays 'subadmin' and 'groups' to comma separated values and wrap them in parentheses if not null
        case 'subadmin':
        case 'groups':
          $selected_data[] = empty($item_data) ? '-'
            : ($format != 'utf8'
              ? utf8_decode(build_csv_line($item_data, ', '))
              : build_csv_line($item_data));
          break;
        case 'locale':
          // If user has not set a locale use '-'
          $selected_data[] = $item_data == '' ? '-' : $item_data;
          break;
        // If none of the above apply
        default:
          $selected_data[] = $item_data;
      }
    }
  }
  return $selected_data;
}

/**
* Find all users belonging to a given group an return an array containing userID and displayname
*
* @param  $group    The name of the group to search for
* @param  $format   If not 'utf8', UTF8 will be decoded for browser display
*         OPTIONAL  DEFAULT: null
*
* @return $group_members
*
*/
function select_group_members($group, $format = null) {
  // Iterate through userlist
  foreach ($_SESSION['userlist'] as $key => $user_id) {
    // Call select_data function to filter/format request data
    $data = $_SESSION['raw_user_data'][$key];
    if (in_array($group, $data['ocs']['data']['groups']))
      $group_members[] = $format == 'utf8'
        ? [$user_id, $data['ocs']['data']['displayname']]
        : array_map('utf8_decode', [$user_id, $data['ocs']['data']['displayname']]);
  }
  return $group_members;
}

/**
  * Print status message on successful server connection
  *
  * Status message contains user count, target instance, timestamp and runtime in seconds
  *
  * Change the standard in 'date(DATE_ATOM)' used to display the timestamp to your needs
  *
  */
function print_status_success() {
  // Output status message after receiving user and group data
  echo '
    <hr>' . L10N_CONNECTED_TO_SERVER . removehttpx($_SESSION['target_url'])
    . ' <span style="color: green">&#10004;</span>'
    . '<br>' . L10N_DOWNLOADED . ' ' . count($_SESSION['raw_user_data']) . ' '
    . L10N_USERS_AND . ' ' . count($_SESSION['grouplist']) . ' '
    . L10N_GROUPS_IN . ' ' . $_SESSION['time_total'] . ' ' . L10N_SECONDS_ON
    . ' ' . date(DATE_ATOM) .
    '<hr><span style="color: darkgreen;">'
    . L10N_ACCESS_TO_ALL_MENU_OPTIONS . '</span>';
}

/**
  * Status printed on each page, showing the active server and user/group count
  *
  */
function print_status_overview() {
  echo '<hr>' . $_SESSION['target_url']
    . '<br>' . L10N_TOTAL . ' ' . $_SESSION['usercount'] . ' ' . L10N_USERS . ' | '
    . $_SESSION['groupcount'] . ' ' . L10N_GROUPS . '
  echo '<hr>' . removehttpx($_SESSION['target_url'])
    . '<br>' . L10N_TOTAL . $_SESSION['usercount'] . L10N_USERS . ' | '
    . $_SESSION['groupcount'] . ' ' . L10N_GROUPS . '
    <hr>';
}

/**
  * Show control button to send emails
  *
  * Provides a button for basic mass mailing via 'mailto:' string
  *
  * @param  $user_data    User data array in raw format ([key][ocs][data][email])
  *         OPTIONAL      DEFAULT: null (full user list will be used)
  * @param  $button_text  Text to display on the button
  *         OPTIONAL      DEFAULT: 'send email to all users'
  * @param  $message_mode How to send emails (to, cc, bcc)
  *         OPTIONAL      DEFAULT: 'bcc'
  */
function show_button_mailto($message_mode = 'bcc', $user_data = null,
  $button_text = L10N_SEND_EMAIL_TO_ALL_USERS) {
  // Build and store email list formatted as 'mailto:'
  $mailto_list = build_mailto_list($user_data, $message_mode);

  // Show mass mail button (only if email addresses were provided)
  if ($mailto_list != false)
    echo '
      <form>
        <input id="button-blue" type="button"
          onclick="window.location.href = \'' . $mailto_list .
          '\'" value="' . $button_text . '"/>
      </form>';
}

/**
  * Build CSV file
  *
  * Creates a file containing provided array data as comma separated values
  *
  * @param $list            Data array containing user data
  * OPTIONAL                DEFAULT: global $selected_user_data_utf8
  * @param $headers         CSV line containing the column headers
  * OPTIONAL                DEFAULT: List from $export_choices variable
  *
  * @return $csv_filename   Filename of the newly created file
  *
  */
function build_csv_file($list, $headers = 'default') {

  if (!TEMP_FOLDER)
    define(TEMP_FOLDER, "export_temp");

  // Delete contents of temporary folder, if file was not deleted after last run
  delete_folder_content(TEMP_FOLDER);

  // Create headers from session variable 'data_choices' if not supplied
  if ($headers == 'default')
    $headers = build_csv_line();

  // Set random filename
  $csv_filename = random_str(32) . '.csv';

  // Check if temporary folder already exists, else make directory
  if (!file_exists(TEMP_FOLDER))
    mkdir(TEMP_FOLDER, 0755, true);

  // Create/open file with write access and return file handle
  $csv_file = fopen(TEMP_FOLDER . '/' . $csv_filename,"w");

  // Set file permissions (rw-r-----)
  chmod(TEMP_FOLDER . '/' . $csv_filename, 0640);

  // Write selected headers as first line to file
  fwrite($csv_file, $headers . "\n");

  // Iterate through provided data array and append each line to the file
  foreach ($list as $line)
    fputcsv($csv_file, $line);

  // Close active file handle
  fclose($csv_file);
  return $csv_filename;
}

/**
  * Initiate file download
  *
  * The selected file (by filename) will be downloaded and deleted afterwards
  * It can be downloaded using an alternative filename, if supplied
  *
  * @param  $filename           Filename on the server
  * @param  $mime_type          MIME type to be sent in the header
  * OPTIONAL                    DEFAULT: 'text/csv'
  * @param  $filename_download  Filename for download
  * OPTIONAL                    DEFAULT: 'download'
  * @param  $folder             Folder to prepend in front of the server filename
  * OPTIONAL                    DEFAULT: '.'
  *
  */
function download_file($filename, $mime_type = 'text/csv',
  $filename_download = 'download', $folder = '.') {
  // make sure file is deleted even if user cancels download
  ignore_user_abort(true);

  header('Content-Type: ' . $mime_type);
  header("Content-Transfer-Encoding: Binary");
  header("Content-disposition: attachment; filename=\"" . $filename_download . "\"");

  readfile($folder . '/' . $filename);
  // delete file
  unlink($folder . '/' . $filename);
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
  if ($folder == null || $folder == ".")
    return;

  // Get filelist from target folder
  $files = glob($folder . '/*');
  // Iterate through filelist and delete all except hidden files (e.g. .htaccess)
  foreach($files as $file)
    if(is_file($file))
      unlink($file);
}

/**
  * Build and format userlist
  *
  * Creates and returns a formatted HTML table containing the userlist
  *
  * @param  $user_data  Array containing selected and formatted user data
  *
  * @return $table_user_data_headers concatenated with $table_user_data
  *
  */
function build_table_user_data($user_data) {
  $data_choices = $_SESSION['data_choices'];

  // Define HTML table and set header cell content
  $table_user_data_headers = '<table id="list"><tr>';

  foreach($data_choices as $choice) {
    if (in_array($choice, ['quota','used','free']))
      $sort = null;
    else
      $sort = ' onclick="sortTable()"';

    foreach($_SESSION['data_options'] as $option => $title) {
      if ($choice == $option)
        $choice = $title;
    }

    $table_user_data_headers .= '<th' . $sort . '>' . $choice . '</th>';
  }
  $table_user_data_headers .= '</tr>';

  // Search for and return position of quota keys in $data_choices
  $keypos_right_align[] = array_search('quota', $data_choices);
  $keypos_right_align[] = array_search('used', $data_choices);
  $keypos_right_align[] = array_search('free', $data_choices);

  // Search for and return position of 'enabled' and 'lastLogin' in $data_choices
  $keypos_center_align[] = array_search('enabled', $data_choices);
  $keypos_center_align[] = array_search('lastLogin', $data_choices);

  // Iterate through collected user data by row and column, build HTML table
  for ($row = 0; $row < sizeof($user_data); $row++) {
    $table_user_data .= '<tr>';
    for ($col = 0; $col < sizeof($user_data[$row]); $col++) {
      $color_text = 'color: unset';
      $selected_data = $user_data[$row][$col];

      if ($selected_data == 'N/A')
        $color_text = 'color: grey;';

      if ($selected_data == "&infin;")
        $align = 'text-align: center; font-size: large;';
      elseif (in_array($col, array_filter($keypos_right_align)))
        $align = 'text-align: right; white-space: nowrap;';
      elseif (in_array($col, array_filter($keypos_center_align)))
        $align = 'text-align: center;';
      else
        $align = null;

      $table_user_data .= '<td style="' . $align . $color_text . '">'
        . $selected_data . '</td>';
    }
    $table_user_data .= '</tr>';
  }
  $table_user_data .= '</table>';
  return $table_user_data_headers . $table_user_data;
}

/**
* Build group table showing all associated userIDs and displaynames
*
* @return $table_group_data_headers concatenated with $table_group_data
*
*/
function build_table_group_data() {
  $grouplist = $_SESSION['grouplist'];

  // Define HTML table and set header cell content
  $table_group_data_headers = '<table id="list"><tr>';
  $table_group_data_headers .=
     '<th onclick="sortTable()">' . L10N_GROUP . '</th>
      <th onclick="sortTable()">' . L10N_USER_ID . '</th>
      <th onclick="sortTable()">' . L10N_DISPLAYNAME . '</th>
      </tr>';

  // Iterate through collected user data by row and column, build HTML table
  for ($row = 0; $row < sizeof($grouplist); $row++) {
    $members = select_group_members($grouplist[$row]);

    // Check if group has no users associated, else list them as CSV
    $user_ids = $members === null
      ? '-'
      : build_csv_line(array_column($members, 0),', ');
    $user_displaynames = $members === null
      ? '-'
      : build_csv_line(array_column($members, 1),', ');

    $table_group_data .= '<tr><td>' . utf8_decode($grouplist[$row])
      . '</td><td>' . $user_ids . '</td><td>' . $user_displaynames . '</td></tr>';
  }
  $table_group_data .= '</table>';
  return $table_group_data_headers . $table_group_data;
}

/**
  * Build 'mailto:' string
  *
  * Creates a string in 'mailto:' notation containing all user emails
  *
  * @param  $mode         Send emails as 'to', 'cc' or 'bcc'
  *         OPTIONAL      DEFAULT: 'bcc'
  *
  * @return $mailto_list  Exported emails as mailto: string for mass mailing
  *
  */
function build_mailto_list($user_data = null, $message_mode = 'bcc') {
  $user_data = $user_data ?? $_SESSION['raw_user_data'];

  // Begin if 'email' key is present
  if ($user_data[0]['ocs']['data']['email']) {
    // Initiate construction of mailto string, setting 'to:', 'cc:' or 'bcc:'
    $mailto_list = 'mailto:?' . $message_mode . '=';
    // Iterate through collected user data and add email addresses
    for ($row = 0; $row < sizeof($user_data); $row++) {
      $user_email = $user_data[$row]['ocs']['data']['email'];
      if ($user_email == 'N/A')
        continue;
      if ($row == 0)
        $mailto_list .= $user_email;
      else
        $mailto_list .= ',' . $user_email;
    }
    // Set email subject
    $mailto_list .= '&subject=' . L10N_SUBJECT_ALL_USER_MAIL;
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
  * @param  $data       Build a comma separated string from supplied array
  * @param  $delimiter  Delimiter to be used between data columns
  *
  * @return $csv_user_data  CSV formatted string containing the processed data
  *
  */
function build_csv_user_data($data, $delimiter = ',') {
  // Add headers to $csv_user_data variable
  $csv_user_data .= build_csv_line(null, $delimiter) . '<br>';

  // Iterate through collected user data by row and column, build CSV output
  for ($row = 0; $row < sizeof($data); $row++) {
    for ($col = 0; $col < sizeof($data[$row]); $col++) {
      // To prevent possible import issues, quote data cells of type string containing spaces
      if (is_string($data[$row][$col])) {
        if ($data[$row][$col] != trim($data[$row][$col])) {
            $csv_user_data .= '"' . $data[$row][$col] . '"';
        } else { $csv_user_data .= $data[$row][$col]; }
      } else { $csv_user_data .= $data[$row][$col]; }
      // Put column separators between cells but not at the end of a record
      if ($col < sizeof($data[$row])-1)
        $csv_user_data .= $delimiter;
    }
    // Indicate the start of a new record
    $csv_user_data .= '<br>';
  }
  return $csv_user_data;
}

/**
  * Build array or CSV formatted string containing the group and user data
  *
  * @param  $array      Return an array or CSV
  *         OPTIONAL    DEFAULT = 'null'
  * @param  $format     Whether to return utf8 formatted data ('utf8') or not
  *         OPTIONAL    DEFAULT = 'null'
  *
  * @return $group_data Array or CSV formatted string containing the group associated user data
  *
  */
function build_group_data($array = null, $format = null) {
  $grouplist = $_SESSION['grouplist'];

  // Add headers to $group_data variable
  if (!$array)
    $group_data .= L10N_CSV_GROUP_HEADERS . '<br>';

  // Iterate through collected group data by row and column, build CSV output
  for ($row = 0; $row < sizeof($grouplist); $row++) {
    $members = select_group_members($grouplist[$row], $format);

    // Check if group has no users associated, else list them as CSV
    $user_ids = $members === null
      ? '-'
      : build_csv_line(array_column($members, 0),', ');
    $user_displaynames = $members === null
      ? '-'
      : build_csv_line(array_column($members, 1),', ');

    if ($array == 'array')
      $group_data[$row+1] = [$grouplist[$row], $user_ids, $user_displaynames];
    else
      $group_data .= utf8_decode($grouplist[$row]) . ',"' . $user_ids . '","'
        . $user_displaynames . '"<br>';
  }
  return $group_data;
}

/**
  * Build a comma separated line from a given array
  *
  * @param  $array       Array to build from
  *         OPTIONAL     DEFAULT: null (session variable 'data_choices')
  * @param  $delimiter   Which char to put between cells
  *         OPTIONAL     DEFAULT: ','
  *
  * @return $csv_line   CSV formatted string
  *
  */
function build_csv_line($array = null, $delimiter = ',') {
  $array = $array ?? $_SESSION['data_choices'];
  foreach($array as $key => $item) {
    if ($key === 0)
      $csv_line = $item;
    else
      $csv_line .= $delimiter . $item;
  }
  return $csv_line;
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
  if ($size == 0)
    return "0 MB";
  elseif ($size == -3)
    return '&infin;';

  $s = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
  $e = floor(log($size, 1024));

  return number_format(round($size/pow(1024, $e), 1),1).' '.$s[$e];
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
    if ($length < 1)
      throw new \RangeException("Length must be a positive integer");
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i)
      $pieces []= $keyspace[random_int(0, $max)];
    return implode('', $pieces);
}

// EOF
