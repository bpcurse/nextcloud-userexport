<!--  This simple php script exports user data from a nextcloud instance through
      user metadata OCS API calls
-->

<style>
  table {border-collapse: collapse;}
  table,td,th {border: 1px solid #ddd;}
	th {text-align: left;background-color: #4C6489; color:white; padding: 8px; padding-left: 5px;}
  td {padding: 5px;}
  tr:nth-child(even) {background-color: #f2f2f2;}
</style>

<form action="#" method="post">
  <input type="text" name="url" size="25" placeholder="https://cloud.example.com">
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
  $nextcloudUrl = $_POST['url'];
 	$adminUsername = $_POST['user'];
 	$adminPassword = $_POST['password'];

  // Check if the form has been filled in completely
 	if(isset($nextcloudUrl) && isset($adminUsername) && isset($adminPassword)) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $nextcloudUrl . '/ocs/v1.php/cloud/users');
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_USERPWD, $adminUsername . ':' . $adminPassword);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'OCS-APIRequest: true',
    	'Accept: application/json'
		]);

		$data = json_decode(curl_exec($ch), true);

		if (isset($data['ocs']['data']['users'])) {
			$users = $data['ocs']['data']['users'];
    	foreach($users as $userId) {
				curl_setopt($ch, CURLOPT_URL, $nextcloudUrl . '/ocs/v1.php/cloud/users/' . $userId);
        $singleUserData = json_decode(curl_exec($ch), true);

        // Push the elements "userId", "displayname", "email" and "lastLogin" to the array variable "$collectedUserData"
        $collectedUserData[] = array(utf8_decode($userId), utf8_decode($singleUserData['ocs']['data']['displayname']), strtolower($singleUserData['ocs']['data']['email']), date("Y-m-d", substr($singleUserData['ocs']['data']['lastLogin'],0,10)));
			}
		}

    // Print status message containing which cloud instance has been queried and when (change the standard used to display the timestamp to your needs)
		echo "<font face=\"Helvetica\">Exported from ".$nextcloudUrl." on ".date(DATE_RFC1123)."<br><br>";

    // Sort the array containing the collected user data
    sort($collectedUserData);

    // Define an html table and set header cell content
    echo "<font face=\"Helvetica\"><table id=\"userexport\";><tr><th>Username</th><th>Displayname</th><th>Email</th><th>Last login</th><tr>";

    // iterate through the collected user data by row and column and build the table
    for ($row = 0; $row < sizeof($collectedUserData); $row++) {
			echo "<tr>";
	  	for ($col = 0; $col < 4; $col++) {
	    	echo "<td>".$collectedUserData[$row][$col]."</td>";
	  	}
	  	echo "</tr>";
		}
		echo "</table>";
	}
}

// EOF
