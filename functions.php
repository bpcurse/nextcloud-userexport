<?php

/**
  * Set cURL options
  *
  * @param $ch          cURL handle
  * @param $target      'users', 'groups', 'groupfolders' or 'capabilities'
  * @param $user_id     User ID of the target user
  * OPTIONAL            DEFAULT: null
  *
  */
function set_curl_options($ch, $target, $id = null) {
  switch ($target) {
    case 'users':
      $path = '/ocs/v1.php/cloud/users';
      break;
    case 'groups':
      $path = '/ocs/v1.php/cloud/groups';
      break;
    case 'groupfolders':
      $path = '/index.php/apps/groupfolders/folders';
      break;
    case 'capabilities':
      $path = '/ocs/v1.php/cloud/capabilities';
      break;
  }

  $id = $id === null ? null : '/' . rawurlencode($id);

  curl_setopt($ch, CURLOPT_URL, $_SESSION['target_url'] . $path . $id);
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
    'quota' => L10N_QUOTA, 'used' => L10N_QUOTA_USED,
    'percentage_used' => L10N_PERCENTAGE_USED, 'free' => L10N_QUOTA_FREE,
    'groups' => L10N_GROUPS, 'subadmin' => L10N_SUBADMIN,
    'language' => L10N_LANGUAGE, 'locale' => L10N_LOCALE];
}

/**
  * Check secure outgoing connection
  *
  * Depending on the first five chars of the supplied URL:
  * - In case 'https' -> return unchanged URL
  * - In case '!http' -> remove '!' and return trimmed URL
  * - In case 'http:' -> exit with insecure connection warning
  * - In case 'none of the above' -> prepend input with https:// and return it
  *
  * @param  $input_url    URL to be processed
  *
  * @return $output_url   URL after processing
  *
  */
function check_https($input_url) {

  // Save the first five chars of the URL to a new variable '$trim_url'
  $trimmed_url = substr($input_url,0,5);

  switch($trimmed_url) {

    // Leave URL untouched if https:// protocol is already set
    case 'https':
      $output_url = $input_url;
      break;

    // Check if plain HTTP is used without override command and exit if not
    case 'http:':
      exit('<font color="red" face="Helvetica"><hr>
          <b>' . L10N_HTTP_IS_BLOCKED . '</b>
          <br>' . L10N_HTTPS_RECOMMENDATION . '
        <font color="black"><hr>
          <br>' . L10N_HTTPS_OVERRIDE_HINT . '
          <br>' . L10N_EG . '!http://cloud.example.com
        </font>');
      break;

    // Remove '!' if HTTPS check override is selected by use of '!http'
    case '!http':
      $output_url = ltrim($input_url,'!');
      break;

    default:
      $output_url = "https://".$input_url;
      break;
  }
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
            <b>'.L10N_ERROR.'cURL ('.L10N_STATUSCODE.curl_errno($ch).')</b>
            <br>'.curl_error($ch).
          '<font color="black">
            <hr>'.L10N_ERROR_CURL_CONNECTION.'
          </font>');
      case 51:
        exit('
          <font color="red">
            <hr>
            <b>'.L10N_ERROR.'cURL ('.L10N_STATUSCODE.curl_errno($ch).')</b>
            <br>'.curl_error($ch).
          '<font color="black">
            <hr>
            '.L10N_ERROR_URL.'
          </font>');
      default:
        exit('
          <font color="red">
            <hr>
            <b>'.L10N_ERROR.'cURL ('.L10N_STATUSCODE.curl_errno($ch).')</b>
            <br>'.curl_error($ch).'
            <hr>
          </font>');
    }
  }

  if ($data === null) {
    exit('
      <font color="red">
        <hr>
        <b>'.L10N_ERROR . L10N_ERROR_EMPTY_API_RESPONSE.'</b>
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
          <b>'.L10N_ERROR . L10N_USER_DOES_NOT_EXIST
            .' ('.L10N_STATUSCODE.$status.')</b>
          <hr>
        </font>');
      break;
    case 997:
      exit('
        <font color="red">
          <hr><b>'.L10N_ERROR . L10N_AUTHENTICATION.' ('.L10N_STATUSCODE.$status.')</b>
          <br>'.L10N_CHECK_USER_PASS.'
        <font color="black">
          <hr>'.L10N_HINT_ADMIN_OR_GROUP_ADMIN.'
        </font>');
    default:
      exit('
        <font color="red">
          <hr>
          <b>'.L10N_ERROR . L10N_UNKNOWN.' ('.L10N_STATUSCODE.$status.')</b>
          <hr>
        </font>');
  }
}

/**
  * Fetch the list containing all user IDs from the server
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
  $_SESSION['userlist'] = $users;
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
  $groups = $groups_raw['ocs']['data']['groups'] ?? null;

  $_SESSION['grouplist'] = $groups;
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
  foreach($_SESSION['userlist'] as $key => $user_id) {
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
      json_decode(curl_multi_getcontent($curl_requests[$key]),true);

    // Remove processed cURL handle
    curl_multi_remove_handle($mh, $curl_requests[$key]);
  }
  // Drop cURL multi handle
  curl_multi_close($mh);

  // Calculate total script runtime
  $timestamp_script_end = microtime(true);
  $_SESSION['time_total'] = number_format(round(
                $timestamp_script_end - TIMESTAMP_SCRIPT_START, 1),1);
  $_SESSION['timestamp_data'] = date(DATE_ATOM);
  return $raw_user_data;
}

/**
  * Download groupfolder data if enabled on the server
  *
  */
function fetch_raw_groupfolders_data() {
  // Initialize cURL handle to fetch user id list and set options
  $ch = curl_init();
  set_curl_options($ch, 'groupfolders');

  // Fetch raw userlist and store user_ids in $users
  $_SESSION['raw_groupfolders_data'] = json_decode(curl_exec($ch), true);

  // Set session variable telling if groupfolders is active on connected server
  $_SESSION['groupfolders_active'] = isset($_SESSION['raw_groupfolders_data']);

  // Drop cURL handle
  curl_close($ch);
}

/**
  * Download groupfolder data
  *
  */
function fetch_server_capabilities() {
  // Initialize cURL handle to fetch user id list and set options
  $ch = curl_init();
  set_curl_options($ch, 'capabilities');

  // Fetch raw userlist and store user_ids in $users
  $_SESSION['raw_server_capabilities'] = json_decode(curl_exec($ch), true);

  // Check for errors in cURL request
  check_curl_response($ch, $_SESSION['raw_server_capabilities']);

  // Drop cURL handle
  curl_close($ch);
}

/**
* Iterate through userlist and call select_data_single_user each time
*
* @param  $data_choices   Array containing a list of data columns to be taken into account
*         OPTIONAL          DEFAULT: null
* @param  $format           decode UTF8?
*         OPTIONAL          DEFAULT: null (decode UTF8)
*
* @return $selected_user_data
*         ARRAY
*/
function select_data_all_users($data_choices = null, $userlist = null, $format = null,
  $csv_delimiter = ', ') {

  $data_choices = $data_choices ?? $_SESSION['data_choices'];
  $userlist = $userlist ?? $_SESSION['userlist'];

  foreach($userlist as $key => $user_id) {
    // Call select_data function to filter/format request data
    $selected_user_data[] = select_data_single_user(
      $_SESSION['raw_user_data'][$key], $user_id, $data_choices, $format,
        $csv_delimiter);
  }

  return $selected_user_data;
}

/**
  * Select elements from array "$data" and decode UTF8 or not
  * depending on parameters
  *
  * @param  $data           Single user record data array
  * @param  $user_id        ID of the user to be processed
  * @param  $data_choices Array containing a list of data columns to be taken into account
  * @param  $format         If not 'utf8', UTF8 will be decoded for browser display
  *         OPTIONAL        DEFAULT: null
  *
  * @return $selected_data  Result of $data filtering
  *         ARRAY
  */
function select_data_single_user(
  $data, $user_id, $data_choices, $format = null, $csv_delimiter = ', ') {

  // If data is not returned due to missing permissions (group admins) set 'N/A' instead
  if($data['ocs']['meta']['statuscode'] == 997) {
    $selected_data[] = $user_id;
    for($i = 1; $i < count($data_choices); $i++)
      $selected_data[] = 'N/A';
  }

  // Prepare data for CSV file export if $format = 'utf8'
  else {
    // Iterate through chosen data sets
    foreach($data_choices as $key => $item) {
      $quota = $data['ocs']['data']['quota']['quota'];
      $used = $data['ocs']['data']['quota']['used'];

      $item_data = $item == 'percentage_used'
        ? (in_array($quota, [-3, 0, 'none'])
          ? 'N/A'
          : round(($used / $quota * 100), 1))
        : $data['ocs']['data'][$item];

      // Filter/format different data sets
      switch($item) {

        // Convert email data set to lowercase
        case 'email':
          $selected_data[] = $item_data == null ? '-' : strtolower($item_data);
          break;
        case 'lastLogin':
          // If user has never logged in set $last_login to '-'
          $selected_data[] = $item_data == 0
            ? ($format == 'utf8'
                ? '-'
                : '<span style="color: red;">&#10008;</span>')
            // Format unix timestamp to YYYY-MM-DD after trimming last 3 chars
            : date("Y-m-d", substr($item_data, 0, 10));
          break;
        // Make the display of 'enabled' bool pretty in the browser
        case 'enabled':
          $selected_data[] = $format == 'utf8'
            ? $item_data
            : ($item_data == true
              ? '<span style="color: green">&#10004;</span>'
              : '<span style="color: red">&#10008;</span>');
          break;
        case 'quota':
        case 'used':
        case 'free':
          $item_data = $data['ocs']['data']['quota'][$item];
          $selected_data[] = in_array($item_data, [-3, 'none'], true)
            ? '∞'
            : ($format != 'utf8'
              ? format_size($item_data)
              : $item_data);
          break;
        // Convert arrays 'subadmin' and 'groups' to comma separated values and wrap them in parentheses if not null
        case 'subadmin':
        case 'groups':
          $selected_data[] = empty($item_data) ? '-'
            : ($format != 'utf8'
              ? build_csv_line($item_data, false, $csv_delimiter)
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
* TODO
*/
function select_data_all_users_filter($filter_by, $condition1, $condition2 = null) {

  foreach($_SESSION['userlist'] as $key => $user_id) {

    if($filter_by == 'quota' || $filter_by == 'used' || $filter_by == 'free')
      $item_data = $_SESSION['raw_user_data'][$key]['ocs']['data']['quota'][$filter_by];
    else
      $item_data = $_SESSION['raw_user_data'][$key]['ocs']['data'][$filter_by];

    switch($filter_by) {

      case 'quota':
      case 'used':
      case 'free':
        if($item_data > $condition1 * 1000000000)
          $selected_user_ids[] = $user_id;
        break;

      case 'lastLogin':
        $lastLogin = substr($item_data, 0, 10);

        if(!$condition1)
          $condition1 = "1970-01-01";

        if(!$condition2)
          $condition2 = date('Y-m-d');

        if($lastLogin >= strtotime($condition1) && $lastLogin <= strtotime($condition2.' +1 day'))
          $selected_user_ids[] = $user_id;
        break;

      case 'groups':
      case 'subadmin':
        if(in_array($condition1, $item_data))
          $selected_user_ids[] = $user_id;

      default:
        if($item_data == $condition1)
          $selected_user_ids[] = $user_id;
    }
  }

  if(!$selected_user_ids)
    $selected_user_ids = [''];

  return $selected_user_ids;
  }

function filter_users() {

  if($_SESSION['filters_set']) {

    $uids_g = in_array('filter_group_choice', $_SESSION['filters_set'])
      ? select_data_all_users_filter('groups', $_SESSION['filter_group'])
      : $_SESSION['userlist'];

    $uids_l = in_array('filter_lastLogin_choice', $_SESSION['filters_set'])
      ? select_data_all_users_filter('lastLogin', $_SESSION['filter_ll_since'],
          $_SESSION['filter_ll_before'])
      : $_SESSION['userlist'];

    $uids_q = in_array('filter_quota_choice', $_SESSION['filters_set'])
      ? select_data_all_users_filter('used', $_SESSION['filter_quota'])
      : $_SESSION['userlist'];

    $user_ids = array_intersect($_SESSION['userlist'], $uids_g, $uids_l, $uids_q);

  }

  if(!$user_ids)
    exit('No users found matching filter settings');

  return $user_ids;

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
  foreach($_SESSION['userlist'] as $key => $user_id) {
    // Call select_data function to filter/format request data
    $data = $_SESSION['raw_user_data'][$key];
    if(in_array($group, $data['ocs']['data']['groups']))
      $group_members[] = [$user_id, $data['ocs']['data']['displayname']];
  }
  return $group_members;
}

/**
  * Calculate how much disk space is assigned, used and available (user and groupfolder quota)
  *
  */
function calculate_quota() {
  $_SESSION['quota_total_assigned'] = 0;
  $_SESSION['quota_total_free'] = 0;
  $_SESSION['quota_total_used'] = 0;

  foreach($_SESSION['raw_user_data'] as $user_data) {
    $_SESSION['quota_total_used'] += $user_data['ocs']['data']['quota']['used'];
    $_SESSION['quota_total_free'] +=
      $user_data['ocs']['data']['quota']['free'];

    $quota_assigned = $user_data['ocs']['data']['quota']['quota'];
    $_SESSION['quota_total_assigned'] += $quota_assigned > 0
      ? $quota_assigned
      : 0;
    $_SESSION['quota_total_assigned_infin'] = ($quota_assigned == -3);
  }
  if($_SESSION['groupfolders_active'])
    foreach($_SESSION['raw_groupfolders_data']['ocs']['data'] as $groupfolder) {
      $_SESSION['quota_groupfolders_used'] += $groupfolder['size'];
      $_SESSION['quota_groupfolders_assigned'] += $groupfolder['quota'];
    }
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
    <hr>'.L10N_CONNECTED_TO_SERVER.removehttpx($_SESSION['target_url'])
    .' <span style="color: green">&#10004;</span>'
    .'<br>'.L10N_DOWNLOADED.' '.count($_SESSION['raw_user_data']).' '
    .L10N_USERS_AND.' '.count($_SESSION['grouplist']).' '
    .L10N_GROUPS_IN.' '.$_SESSION['time_total'].' '.L10N_SECONDS
    .'<br>Timestamp: '.$_SESSION['timestamp_data'].
    '<hr><span style="color: darkgreen;">'
    .L10N_ACCESS_TO_ALL_MENU_OPTIONS.'</span>';
}

/**
  * Status printed on each page, showing the active server and user/group count
  *
  */
function print_status_overview($scope = 'quick') {
  $infinite = $_SESSION['quota_total_assigned_infin']
    ? ' (+ &infin;)'
    : '';
  if($scope == 'quick') {
    echo '<hr>
    <a class="no_show_link" href="'.$_SESSION['target_url']
    .'" target="_blank">'.removehttpx($_SESSION['target_url']).'</a>
    <hr>';

  } else {
    fetch_server_capabilities();
    $_SESSION['server_version_string'] =
      $_SESSION['raw_server_capabilities']['ocs']['data']['version']['string'];
    echo '<hr>
      <a class="no_show_link" href="'.$_SESSION['target_url']
      .'" target="_blank">'.removehttpx($_SESSION['target_url']).'</a>
      <br><br>'.L10N_NEXTCLOUD.' '.$_SESSION['server_version_string']
      .'<hr>
    <table class="status">
      <tr><td>'.L10N_USERS.'</td><td style="text-align: right;">'
        .$_SESSION['user_count'].'</td></tr>
      <tr><td>'.L10N_GROUPS.'</td><td>'
        .$_SESSION['group_count'].'</td></tr>';
    if($_SESSION['groupfolders_active'])
      echo '<tr><td>'.L10N_GROUPFOLDERS.'</td><td>'
        .$_SESSION['groupfolders_count'].'</td></tr>';
    echo '
    </table>
    <hr>
    <table class="status">
      <tr>
        <td colspan=2><b>'.L10N_USERS.'</b>
        </td></tr>
        <tr><td>'.L10N_QUOTA_USED.'</td><td>'
          .format_size($_SESSION['quota_total_used']).'
        </td></tr>
        <tr><td>'.L10N_QUOTA.'</td><td>'
          .format_size($_SESSION['quota_total_assigned'])
          .'</td><td>'.$infinite.'
        </td></tr>
        <tr><td>'.L10N_QUOTA_FREE.'</td><td>'
          .format_size($_SESSION['quota_total_free']).
        '</td></tr>';
      if($_SESSION['groupfolders_active'])
        echo '<tr style="height: 10px"><td></td></tr>
          <tr><td colspan=2><b>'.L10N_GROUPFOLDERS.'</b></td></tr>
        <tr><td>'.L10N_QUOTA_USED.'</td><td>'
          .format_size($_SESSION['quota_groupfolders_used']).'</td></tr>
        <tr><td>'.L10N_QUOTA.'</td><td>'
          .format_size($_SESSION['quota_groupfolders_assigned']).'</td></tr>';
      echo '</table><hr>'
        .L10N_DATA_RETRIEVED.' '.$_SESSION['timestamp_data'];
  }
}

/**
  * Build CSV file
  *
  * Creates a file containing provided array data as comma separated values
  *
  * @param $list            Data array containing user data
  * OPTIONAL                DEFAULT: global $selected_user_data_utf8
  * @param $headers         CSV line containing the column headers, set null if none
  * OPTIONAL                DEFAULT: List from $data_choices variable
  *
  * @return $csv_filename   Filename of the newly created file
  *
  */
function build_csv_file($list, $headers = 'default') {

  if(!TEMP_FOLDER)
    define(TEMP_FOLDER, "export_temp");

  // Delete contents of temporary folder, if file was not deleted after last run
  delete_folder_content(TEMP_FOLDER);

  // Create headers from session variable 'data_choices' if not supplied
  if($headers == 'default')
    $headers = build_csv_line();

  // Set random filename
  $csv_filename = random_str(32).'.csv';

  // Check if temporary folder already exists, else make directory
  if(!file_exists(TEMP_FOLDER))
    mkdir(TEMP_FOLDER, 0755, true);

  // Create/open file with write access and return file handle
  $csv_file = fopen(TEMP_FOLDER.'/'.$csv_filename, "w");

  // Set file permissions (rw-r-----)
  chmod(TEMP_FOLDER.'/'.$csv_filename, 0640);

  // Write selected headers as first line to file
  if($headers != 'no_headers')
    fwrite($csv_file, $headers."\n");

  // Iterate through provided data array and append each line to the file
  foreach($list as $line)
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

  header('Content-Type: '.$mime_type);
  header("Content-Transfer-Encoding: Binary");
  header("Content-disposition: attachment; filename=\"".$filename_download."\"");

  readfile($folder.'/'.$filename);
  // delete file
  unlink($folder.'/'.$filename);
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
  if($folder == null || $folder == ".")
    return;

  // Get filelist from target folder
  $files = glob($folder.'/*');
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

  if($_SESSION['filters_set']) {
    echo "<br><span style='color: red;'>Display filtered by ";

    foreach($_SESSION['filters_set'] as $filter)
      switch($filter) {
        case 'filter_group_choice':
          echo "group {$_SESSION['filter_group']}";
          $preceding = true;
          break;
        case 'filter_lastLogin_choice':
          if($preceding)
            echo " <b>and</b> ";
          echo "last login between {$_SESSION['filter_ll_since']} and {$_SESSION['filter_ll_before']} (incl. last day)";
          $preceding = true;
          break;
        case 'filter_quota_choice':
          if($preceding)
            echo " <b>and</b> ";
          echo "quota usage over {$_SESSION['filter_quota']} GB";
          break;
      }
    echo "<br><br></span>";
  }

  // Define HTML table and set header cell content
  $table_user_data_headers = '<table class="list"><tr>';

  foreach($data_choices as $key => $choice) {
    $sort = (in_array($choice, ['quota', 'used', 'free']))
      ? null
      : ' onclick="sortTable()"';

    $align = (in_array($choice,
      ['quota', 'used', 'free', 'lastLogin', 'percentage_used']))
      ? 'text-align: center;'
      : null;

    foreach($_SESSION['data_options'] as $option => $title) {
      if($choice == $option)
        $choice = $title;
    }

    $table_user_data_headers .= "<th$sort style='$align'>$choice</th>";
  }
  $table_user_data_headers .= '</tr>';

  // Search for and return position of quota keys in $data_choices
  $keypos_right_align[] = array_search('quota', $data_choices);
  $keypos_right_align[] = array_search('used', $data_choices);
  $keypos_right_align[] = array_search('free', $data_choices);
  $keypos_right_align[] = array_search('percentage_used', $data_choices);

  // Search for and return position of 'enabled' and 'lastLogin' in $data_choices
  $keypos_center_align[] = array_search('enabled', $data_choices);
  $keypos_center_align[] = array_search('lastLogin', $data_choices);

  // Iterate through collected user data by row and column, build HTML table
  for($row = 0; $row < sizeof($user_data); $row++) {
    $table_user_data .= '<tr>';
    for($col = 0; $col < sizeof($user_data[$row]); $col++) {
      $selected_data = $user_data[$row][$col];

      $color_text = ($selected_data === 'N/A')
        ? ' color: grey;'
        : ' color: unset;';

      $align = (in_array($col, $keypos_right_align, true))
        ? 'text-align: right; white-space: nowrap;'
        : (in_array($col, $keypos_center_align, true)
          ? 'text-align: center;'
          : null);

      $table_user_data .= "<td style='$align$color_text'>$selected_data</td>";
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
  $table_group_data_headers = '<table class="list"><tr>';
  $table_group_data_headers .=
     '<th onclick="sortTable()">'.L10N_GROUP.'</th>
      <th onclick="sortTable()" style="text-align: center;">'.L10N_USERS
        .'</th>
      <th onclick="sortTable()">'.L10N_USER_ID.'</th>
      <th onclick="sortTable()">'.L10N_DISPLAYNAME.'</th>
      </tr>';

  // Iterate through collected user data by row and column, build HTML table
  for($row = 0; $row < sizeof($grouplist); $row++) {
    $members = select_group_members($grouplist[$row]);

    // Check if group has no users associated, else list them as CSV
    if($members === null) {
      $user_ids = '-';
      $members_count = 0;
    } else {
      $user_ids = build_csv_line(array_column($members, 0), false, ', ');
      $members_count = count($members);
    }

    $user_displaynames = $members === null
      ? '-'
      : build_csv_line(array_column($members, 1), false, ', ');

    $table_group_data .= "<tr><td>".$grouplist[$row]."</td>
      <td style='text-align: right;'>$members_count</td>
      <td>$user_ids</td><td>$user_displaynames</td></tr>";
  }
  $table_group_data .= '</table>';
  return $table_group_data_headers . $table_group_data;
}

/**
* Build groupfolder table
*
* @return $table_groupfolder_data_headers concatenated with $table_groupfolder_data
*
*/
function build_table_groupfolder_data() {
  // Define HTML table and set header cell content
  $align_right = 'style="text-align: right;"';
  $align_center = 'style="text-align: center;"';

  $table_groupfolder_data_headers = '<table class="list"><tr>';
  $table_groupfolder_data_headers .=
     '<th onclick="sortTable()">'.L10N_ID.'</th>
      <th onclick="sortTable()">'.L10N_NAME.'</th>
      <th onclick="sortTable()">'.L10N_GROUPS.'</th>
      <th class="align_r" onclick="sortTable()">'.L10N_QUOTA_USED.'</th>
      <th class="align_c" onclick="sortTable()">'.L10N_PERCENTAGE_USED.'</th>
      <th class="align_r" onclick="sortTable()">'.L10N_QUOTA.'</th>
      <th class="align_c" onclick="sortTable()">'.L10N_ACL.'</th>
      <th onclick="sortTable()">'.L10N_ADMIN.'</th>
      </tr>';

  // Iterate through collected user data by row and column, build HTML table
  foreach($_SESSION['raw_groupfolders_data']['ocs']['data'] as $groupfolder) {
    $groups = build_csv_line($groupfolder['groups'], true, ', ');

    $manager = null;
    foreach($groupfolder['manage'] as $item)
      $manager = $item['id'];

    $acl = ($groupfolder['acl'])
      ? '<span style="color: green">&#10004;</span>'
      : null;

    $percent_used = round($groupfolder['size'] / $groupfolder['quota'] * 100,2);

    $table_groupfolder_data .= "<tr><td>".utf8_decode($groupfolder['id'])."</td>
      <td>".$groupfolder['mount_point']."</td>
      <td>$groups</td>
      <td class='align_r'>".format_size($groupfolder['size'])."</td>
      <td class='align_r'>".round($percent_used, 1)."</td>
      <td class='align_r'>".format_size($groupfolder['quota'])."</td>
      <td class='align_c'>$acl</td>
      <td>$manager</td></tr>";
  }
  $table_groupfolder_data .= "</table>";

  return $table_groupfolder_data_headers . $table_groupfolder_data;
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
function build_mailto_list($message_mode = 'bcc', $user_ids = null) {

  $user_data = $_SESSION['raw_user_data'];

  // Initiate construction of mailto string, setting 'to:', 'cc:' or 'bcc:'
  $mailto_list = "mailto:?$message_mode=";

  if(!$user_ids) {

    // Iterate through user data and add email addresses
    foreach($user_data as $key => $item) {
      $user_email = $item['ocs']['data']['email'];
      if ($user_email == 'N/A')
        continue;
      if ($key == 0)
        $mailto_list .= $user_email;
      else
        $mailto_list .= ',' . $user_email;
    }

  }
  else {
    foreach($user_data as $key => $item) {
      if(in_array($item['ocs']['data']['id'], $user_ids)) {
        $user_email = $item['ocs']['data']['email'];
        if ($user_email == 'N/A')
          continue;
        if ($key == 0)
          $mailto_list .= $user_email;
        else
          $mailto_list .= ',' . $user_email;
      }
    }
  }

  return $mailto_list;

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
  $csv_user_data .= build_csv_line(null, false, $delimiter)."<br>";

  // Iterate through collected user data by row and column, build CSV output
  for($row = 0; $row < sizeof($data); $row++) {
    for($col = 0; $col < sizeof($data[$row]); $col++) {
      // To prevent possible import issues, quote data cells of type string containing spaces
      if(is_string($data[$row][$col])) {
        if($data[$row][$col] != trim($data[$row][$col])) {
          $csv_user_data .= '"'.$data[$row][$col].'"';
        } else { $csv_user_data .= $data[$row][$col]; }
      } else { $csv_user_data .= $data[$row][$col]; }
      // Put column separators between cells but not at the end of a record
      if ($col < sizeof($data[$row])-1)
        $csv_user_data .= $delimiter;
    }
    // Indicate the start of a new record
    $csv_user_data .= "<br>";
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
function build_group_data($array = null, $format = null, $delimiter = ', ') {
  $grouplist = $_SESSION['grouplist'];

  // Add headers to $group_data variable
  if(!$array)
    $group_data .= L10N_CSV_GROUP_HEADERS."<br>";

  // Iterate through collected group data by row and column, build CSV output
  for($row = 0; $row < sizeof($grouplist); $row++) {
    $members = select_group_members($grouplist[$row], $format);

    // Check if group has no users associated, else list them as CSV
    $user_ids = $members === null
      ? '-'
      : build_csv_line(array_column($members, 0), false, $delimiter);

    $user_displaynames = $members === null
      ? '-'
      : build_csv_line(array_column($members, 1), false, $delimiter);

    if($array == 'array')
      $group_data[$row+1] = [$grouplist[$row], $user_ids, $user_displaynames];
    else
      $group_data .= $grouplist[$row].$delimiter.'"'.$user_ids.'"'
        .$delimiter.'"'.$user_displaynames.'"<br>';
  }
  return $group_data;
}

/**
  * Build array or CSV formatted string containing the group and user data
  *
  * @param  $array      Return an array or CSV
  *         OPTIONAL    DEFAULT = 'null'
  *
  * @return $groupfolder_return_data Array or CSV formatted string containing groupfolder data
  *
  */
function build_groupfolder_data($array = null) {
  if(!$array)
    // Add headers to $group_data variable
    $groupfolder_return_data .= L10N_ID.','.L10N_NAME.','.L10N_GROUPS.','
      .L10N_QUOTA_USED.','.L10N_PERCENTAGE_USED.','.L10N_QUOTA.','.L10N_ACL.','
      .L10N_ADMIN.'<br>';

  // Iterate through collected groupfolder data, build CSV output or array
  foreach($_SESSION['raw_groupfolders_data']['ocs']['data'] as $groupfolder) {
    $groups = build_csv_line($groupfolder['groups'], true, ', ');

    $manager = null;
    foreach($groupfolder['manage'] as $item)
      $manager = $item['id'];

    if(!$array)
      $acl = ($groupfolder['acl'])
        ? '<span style="color: green">&#10004;</span>'
        : null;
    else
      $acl = $groupfolder['acl'];

    $percent_used = round(($groupfolder['size'] / $groupfolder['quota'] * 100),1);

    $groupfolder_data = [$groupfolder['id'],$groupfolder['mount_point'],
      $groups,format_size($groupfolder['size']),$percent_used,
      format_size($groupfolder['quota']),$acl,$manager];

    if(!$array)
      $groupfolder_return_data .= build_csv_line($groupfolder_data)."<br>";
    else
      $groupfolder_return_data[] = $groupfolder_data;
  }
  return $groupfolder_return_data;
}

/**
  * Build a comma separated line from a given array
  *
  * @param  $array        Array to build from
  *         OPTIONAL      DEFAULT: null (session variable 'data_choices')
  * @param  $return_key   Return array key instead of item
  *         OPTIONAL      DEFAULT: false
  * @param  $delimiter    Which char to put between cells
  *         OPTIONAL      DEFAULT: ','
  *
  * @return $csv_line   CSV formatted string
  *
  */
function build_csv_line($array = null, $return_key = false, $delimiter = ',') {
  $array = $array ?? $_SESSION['data_choices'];

  $i = 0;
  foreach($array as $key => $item) {
    if ($return_key)
      $csv_line .= ($i === 0)
        ? $key
        : $delimiter.$key;
    else
      $csv_line .= ($i === 0)
        ? $item
        : $delimiter.$item;
    $i++;
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
  if ($size === 0)
    return "0 MB";
  if ($size === null)
    return '-';

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
