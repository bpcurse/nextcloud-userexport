<!--  This simple php script exports user data from a nextcloud instance through
      user metadata OCS API calls
      -->

<style>
  table {border-collapse: collapse;}
  table,td,th {border: 1px solid #ddd;}
  th {text-align: left;
    background-color: #4C6489;
    color:white;
    padding: 8px;
    padding-left: 5px;}
  td {padding: 5px;}
  tr:nth-child(even) {background-color: #f2f2f2;}
</style>

<form action="#" method="post"><font face="Helvetica">
  <input type="text" name="url" size="25"
    placeholder="https://cloud.example.com">
	<input type="text" name="user" placeholder="Admin user name">
	<input type="password" name="password" placeholder="Admin user password">
  <br><br>
  Display results as:
  <input type="radio" name="export_type" value="table" checked> Table
  <input type="radio" name="export_type" value="csv"> CSV
  <br><br>
  <input type="submit" name="submit" value="submit"></font>
</form>

<?php

// Adjust the timezone to your needs
date_default_timezone_set("Europe/Berlin");

// Check if the form has been submitted
if(isset($_POST['submit'])) {

  // Set variables to POST values
  $nextcloud_url = $_POST['url'];
  $admin_username = $_POST['user'];
  $admin_password = $_POST['password'];
  $export_type = $_POST['export_type'];

  // Check if the form has been filled in completely
 	if (isset($nextcloud_url) && isset($admin_username) && isset($admin_password))
  {
    // Save the first five chars of the url to a new variable '$trim_url'
    $trim_url = substr($nextcloud_url,0,5);

    // Check if plain http is used without override command and exit if not
    if ($trim_url != 'https' && $trim_url != '!http') {
      exit('<font color="red" face="Helvetica"><hr>
      <b>The use of plain http is blocked for security reasons.</b>
      <br>Please use https instead.
      <font color="black"><br><hr>
      You can override this safety precaution and send your admin credentials unencrypted by using \'!\' before \'http\'
      <br>e.g.: !http://cloud.example.com</font>');
    }

    // Remove '!'' if https check override is selected by use of '!http'
    if ($trim_url == '!http') {
      $nextcloud_url = ltrim($nextcloud_url,'!');
    }

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

    /**
      * Print status message containing user count, target instance and export timestamp
      * (change the standard used to display the timestamp to your needs)
      */
		echo "<br><hr><font face=\"Helvetica\">Exported "
      . count($collected_user_data) . " records from ".$nextcloud_url." on "
      . date(DATE_RFC1123)."<hr><br>";

    // Sort the array containing the collected user data
    sort($collected_user_data);

    // Display a table or comma separated values depending on radio button selection in the form
    if ($export_type == 'table') {
      echo build_table_user_data($collected_user_data);
    } elseif ($export_type == 'csv') {
      echo build_csv_user_data($collected_user_data);
    }
	}
}

/**
  * Build and display an html table containing the selected user data
  */
function build_table_user_data($collected_user_data) {

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
  return $table_user_data;
}

/**
  * Build the variable $csv_user_data with the selected user data in csv format
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
