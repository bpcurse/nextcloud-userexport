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

    ?>
  </head>

  <body>
    <form method='post' action='userexport.php' id='auth_form'><font face='Helvetica'>
      <label for='url'>Target URL</label>
      <input id='url' type='text' name='url' size='30'
        placeholder='https://cloud.example.com'
        value='<?php echo $nextcloud_url; ?>'>
      <label for='user'>Username</label>
      <input id='user' type='text' name='user' size='10' placeholder='username'
        value='<?php echo $admin_username; ?>'>
      <label for='pass'>Password</label>
      <input id='pass' type='password' name='pass' size='10' placeholder='password'
        value='<?php echo $admin_password; ?>'>
      <br><br>
      Include data:
      <input type='checkbox' name='id' value='true' checked='checked'>User ID
      <input type='checkbox' name='displayname' value='true' checked='checked'>Displayname
      <input type='checkbox' name='email' value='true' checked='checked'>Email
      <input type='checkbox' name='lastLogin' value='true' checked='checked'>Last login
      <input type='checkbox' name='total' value='true'>Quota total
      <input type='checkbox' name='used' value='true'>Quota used
      <input type='checkbox' name='free' value='true'>Quota remaining
      <input type='checkbox' name='groups' value='true'>Groups
      <input type='checkbox' name='subadmin' value='true'>Subadmin
      <input type='checkbox' name='backend' value='true'>User backend
      <input type='checkbox' name='enabled' value='true'>Enabled
      <input type='checkbox' name='language' value='true'>Language
      <input type='checkbox' name='locale' value='true'>Locale
      <br><br>
      Display results as:
      <input type='radio' name='export_type' value='table'
        <?php if ($export_type == 'table' || $export_type == null)
        {echo 'checked=\"checked\"';} ?>> Table
      <input type='radio' name='export_type' value='csv'
        <?php if ($export_type == 'csv') {echo 'checked=\"checked\"';} ?>> CSV
      <input type="hidden" name="msg_mode" value="<?php echo $message_mode ?>">
      <br><br>
      <input type='submit' name='submit' value='submit'></font>
    </form>
  </body>
</html>
