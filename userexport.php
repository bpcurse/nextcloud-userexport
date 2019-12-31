<?php

  // Adjust the timezone to your needs
  date_default_timezone_set("Europe/Berlin");

  // Set variables to POST values
  $nextcloud_url = $_POST['url'];
  $admin_username = $_POST['user'];
  $admin_password = $_POST['pass'];
  $export_type = $_POST['export_type'];

  // Check if plain http is used without override command and exit if not
  $nextcloud_url = check_https($nextcloud_url);

  /**
    * Initialize and set some curl options
    */
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $nextcloud_url . '/ocs/v1.php/cloud/users');
  curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
  curl_setopt($ch, CURLOPT_USERPWD, $admin_username . ':' . $admin_password);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'OCS-APIRequest: true',
    'Accept: application/json'
  ]);

  $data = json_decode(curl_exec($ch), true);

  if (isset($data['ocs']['data']['users'])) {
    $users = $data['ocs']['data']['users'];

    // Iterate through users and save the supplied data to $single_user_data
    foreach ($users as $user_id) {
			curl_setopt($ch, CURLOPT_URL, $nextcloud_url .
        '/ocs/v1.php/cloud/users/' . $user_id);

      // Fetch data for specific user via curl
      $single_user_data = json_decode(curl_exec($ch), true);

      /**
        * Push elements "user_id", "displayname", "email" and "lastLogin"
        * to array "$collected_user_data"
        */
      if ($export_type == 'csv_dl') {
        $collected_user_data[] = array(
          $user_id,
          $single_user_data['ocs']['data']['displayname'],
          strtolower($single_user_data['ocs']['data']['email']),
          date("Y-m-d",
            substr($single_user_data['ocs']['data']['lastLogin'],0,10)
          )
        );
      // In case of html table or csv display in the browser decode utf8
      } else {
        $collected_user_data[] = array(
          utf8_decode($user_id),
          utf8_decode($single_user_data['ocs']['data']['displayname']),
          strtolower($single_user_data['ocs']['data']['email']),
          date("Y-m-d",
            substr($single_user_data['ocs']['data']['lastLogin'],0,10)
          )
        );
      }
    }
	}

  // Sort the array containing the collected user data
  sort($collected_user_data);

  // Display table, comma separated values or download csv file
  if ($export_type == 'table') {
    print_status_message($collected_user_data, $nextcloud_url);
    echo build_table_user_data($collected_user_data);
  } elseif ($export_type == 'csv') {
    print_status_message($collected_user_data, $nextcloud_url);
    echo build_csv_user_data($collected_user_data);
  } elseif ($export_type == 'csv_dl') {
    download_file(build_csv_file($collected_user_data), 'application/csv');
  }

  /**
    * Check if the first five chars of the supplied url match 'https' or '!http'
    *
    * In case 'https' -> return unchanged url
    * In case '!http' -> remove '!' and return trimmed url
    * In case 'http' or anything else -> exit with insecure connection warning
    *
    * @param $url Url to be processed
    * @return string $url Url after processing
    */
  function check_https($url) {
    // Save the first five chars of the url to a new variable '$trim_url'
    $trim_url = substr($url,0,5);

    // Check if plain http is used without override command and exit if not
    if ($trim_url != 'https' && $trim_url != '!http') {
      exit('<font color="red" face="Helvetica"><hr>
      <b>The use of plain http is blocked for security reasons.</b>
      <br>Please use https instead.
      <font color="black"><hr><br>
      You can override this safety precaution and send your admin credentials
      <u><b>unencrypted</b></u> if you really need to by inserting \'!\' before \'http\'
      <br>e.g.: !http://cloud.example.com</font>');
    }

    // Remove '!'' if https check override is selected by use of '!http'
    if ($trim_url == '!http') {
      $url = ltrim($trim_url,'!');
    }
    return $url;
  }

  /**
    * Initiate file download
    *
    * The supplied file (by filename) will be downloaded and deleted afterwards
    *
    * @param $file target filename to download
    * @param $mime_type MIME type to be sent in the header
    */
  function download_file($file, $mime_type) {
    // make sure file is deleted even if user cancels download
    ignore_user_abort(true);

    header('Content-Type: ' . $mime_type . '');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $file . "\"");

    readfile($file);

    // delete file
    unlink($file);
  }

  /**
    * Print status message
    *
    * Status message contains user count, target instance and export timestamp
    * (change the standard used to display the timestamp to your needs)
    *
    * @param $to_count Array variable to be counted ($collected_user_data)
    * @param $url Url of the target nextcloud instance
    */
  function print_status_message($to_count, $url) {
    echo "<hr><font face=\"Helvetica\">Exported "
      . count($to_count) . " records from ".$url." on "
      . date(DATE_RFC1123)."<hr><br>";
  }

  /**
    * Build csv file
    *
    * Creates a file containing the user data as comma separated values
    *
    * @param $userlist The collected user data array provided by curl ($collected_user_data)
    * @return $csv_filename Filename of the newly created file
    */
  function build_csv_file($userlist) {
    $csv_filename = ("userlist_" . date('Y-m-d') . ".csv");
    $csv_file = fopen($csv_filename,"w");
    foreach ($userlist as $line) {
      fputcsv($csv_file, $line);
    }
    fclose($csv_file);
    return $csv_filename;
  }

  /**
    * Build and format user list
    *
    * Creates an html formatted table containing the user list
    *
    * @param $collected_user_data The collected user data array provided by curl
    * @return $table_user_data_style concatenated with $table_user_data
    *   css style and html table
    */
  function build_table_user_data($collected_user_data) {

    // Define css style for table
    $table_user_data_style = '<style>
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

    // Define an html table with id userexport and set header cell content
    $table_user_data = "<font face=\"Helvetica\"><table id=\"userexport\";><tr>
      <th>Username</th>
      <th>Displayname</th>
      <th>Email</th>
      <th>Last login</th><tr>";

    // Iterate through collected user data by row and column, build html table
    for ($row = 0; $row < sizeof($collected_user_data); $row++) {
      $table_user_data .= "<tr>";
      for ($col = 0; $col < 4; $col++) {
        $table_user_data .= "<td>".$collected_user_data[$row][$col]."</td>";
      }
      $table_user_data .= "</tr>";
    }
    $table_user_data .= "</table>";
    return $table_user_data_style . $table_user_data;
  }

  /**
    * csv string creation
    *
    * Build csv formatted string containing the user data
    *
    * @param $collected_user_data The collected user data array provided by curl
    * @return $csv_user_data csv formatted string containing the user data
    */
  function build_csv_user_data($collected_user_data) {

    // Add headers to $csv_user_data variable
    $csv_user_data = 'User ID,Displayname,Email,Last login<br>';

    // Iterate through collected user data by row and column, build csv output
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
