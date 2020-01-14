<html lang="en">
  <head>
    <title>Nextcloud user export</title>
    <?php

      // Get parameters if any
      if (isset($_GET['url'])) {
        $nextcloud_url = $_GET['url'];
      }
      if (isset($_GET['user'])) {
        $admin_username = $_GET['user'];
      }
      if (isset($_GET['pass'])) {
        $admin_password = $_GET['pass'];
      }
      if (isset($_GET['type'])) {
        $export_type = $_GET['type'];
      }
      if (isset($_GET['msg_mode'])) {
        $message_mode = $_GET['msg_mode'];
      }
      if (isset($_GET['select'])) {
        $data_choices = explode(",", $_GET["select"]);
      }
      if ($data_choices === null) {
        $data_choices = ['id', 'displayname', 'email', 'lastLogin'];
      }
      $data_options = ['id' => 'User ID', 'displayname' => 'Displayname',
                      'email' => 'Email', 'lastLogin' => 'Last Login',
                      'backend' => 'Backend', 'enabled' => 'Enabled',
                      'total' => 'Quota total', 'used' => 'Quota used',
                      'free' => 'Quota free', 'groups' => 'Groups',
                      'subadmin' => 'Subadmin', 'language' => 'Language',
                      'locale' => 'Locale'];

    ?>
  </head>

  <body>
    <form method='post' action='userexport.php' id='auth_form'><font face='Helvetica'>
      <u>URL and login data:</u><br><br>
      <table>
      <tr><td><label for='url'>Target URL</label></td>
      <td colspan="3"><input id='url' type='text' name='url' size='36'
        placeholder='https://cloud.example.com'
        value='<?php echo $nextcloud_url; ?>'>
      </td></tr>
      <tr><td><label for='user'>Username</label></td>
      <td><input id='user' type='text' name='user' size='10' placeholder='username'
        value='<?php echo $admin_username; ?>'></td>
      <td><label for='pass'>Password</label></td>
      <td><input id='pass' type='password' name='pass' size='10' placeholder='password'
        value='<?php echo $admin_password; ?>'></td></tr>
      </table>
      <br><br>
      <u>Include the following user data:</u><br><br>
      <?php

      echo "<table><tr>";
      foreach ($data_options as $option => $title) {
        if (in_array($option, $data_choices)) {
          $checked = "checked='checked'";
        } else {
          $checked = null;
        }
        switch ($option) {
          case 'email':
          case 'enabled':
          case 'free':
          case 'subadmin':
          case 'locale':
            echo "<td><input type='checkbox' name='" . $option
              . "' value='true' " . $checked . "'>" . $title
              . "</td></tr>";
            break;
          case 'lastLogin':
          case 'total':
          case 'groups':
          case 'language':
            echo "<tr><td><input type='checkbox' name='" . $option
              . "' value='true' " . $checked . ">" . $title . "</td>";
            break;
          default:
            echo "<td><input type='checkbox' name='" . $option
              . "' value='true' " . $checked . ">" . $title . "</td>";
        }
      }
      echo "</table>";

      ?>
      <br><br>
      <u>Display results as:</u><br><br>
      <input type='radio' name='export_type' value='table'
        <?php if ($export_type == 'table' || $export_type == null)
        {echo 'checked=\"checked\"';} ?>> Table
      <input type='radio' name='export_type' value='csv'
        <?php if ($export_type == 'csv') {echo 'checked=\"checked\"';} ?>> CSV
      <input type="hidden" name="msg_mode" value="<?php echo $message_mode ?>">
      <br><br><span style="font-size: small">
        A CSV formatted file can be downloaded on the next page</span>
      <br><br>
      <input type='submit' name='submit' value='submit'></font>
    </form>
  </body>
</html>
