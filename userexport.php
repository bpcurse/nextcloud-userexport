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

<form action="#" method="post">
  <input type="text" name="url" size="25"
    placeholder="https://cloud.example.com">
	<input type="text" name="user" placeholder="Admin user name">
	<input type="password" name="password" placeholder="Admin user password">
  <input type="submit" name="submit" value="submit">
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

  // Check if the form has been filled in completely
 	if(isset($nextcloud_url) && isset($admin_username) && isset($admin_password))
    {

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

      // iterate through users and save the supplied data to $single_user_data
      foreach($users as $user_id) {
				curl_setopt($ch, CURLOPT_URL, $nextcloud_url .
          '/ocs/v1.php/cloud/users/' . $user_id);

        // Fetch data for specific user via curl
        $single_user_data = json_decode(curl_exec($ch), true);

        /**
          * Push elements "userId", "displayname", "email" and "lastLogin"
          * to array "$collected_user_data"
          */
        $collected_user_data[] = array(utf8_decode($user_id),
          utf8_decode($single_user_data['ocs']['data']['displayname']),
          strtolower($single_user_data['ocs']['data']['email']),
          date("Y-m-d",
            substr($single_user_data['ocs']['data']['lastLogin'],0,10)));
			}
		}

    /**
      * Print status message containing
      * which nextcloud instance has been queried and when it was completed
      * (change the standard used to display the timestamp to your needs)
      */
		echo "<font face=\"Helvetica\">Exported from ".$nextcloud_url." on "
      .date(DATE_RFC1123)."<br><br>";

    // Sort the array containing the collected user data
    sort($collected_user_data);

    // Define an html table and set header cell content
    echo "<font face=\"Helvetica\"><table id=\"userexport\";><tr>
      <th>Username</th>
      <th>Displayname</th>
      <th>Email</th>
      <th>Last login</th><tr>";

    // iterate through collected user data by row and column, build html table
    for ($row = 0; $row < sizeof($collected_user_data); $row++) {
			echo "<tr>";
	  	for ($col = 0; $col < 4; $col++) {
	    	echo "<td>".$collected_user_data[$row][$col]."</td>";
	  	}
	  	echo "</tr>";
		}
		echo "</table>";
	}
}

// EOF
