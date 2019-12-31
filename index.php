<html>
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
      if (isset($_GET['type']) && ($_GET['type'] == 'table' || $_GET['type'] == 'csv'
        || $_GET['type'] == 'csv_dl')) {
          $export_type = $_GET['type'];
      }

    ?>
  </head>

  <body>
    <form method='post' action='userexport.php'><font face='Helvetica'>
      <input type='text' name='url' size='30'
        placeholder='https://cloud.example.com'
        value='<?php echo $nextcloud_url; ?>'>
      <input type='text' name='user' size='15' placeholder='username'
        value='<?php echo $admin_username; ?>'>
      <input type='password' name='pass' size='15' placeholder='password'
        value='<?php echo $admin_password; ?>'>
      <br><br>
      Display results:
      <input type='radio' name='export_type' value='table'
        <?php if ($export_type == 'table' || $export_type == null)
        {echo 'checked=\"checked\"';} ?>> Table
      <input type='radio' name='export_type' value='csv'
        <?php if ($export_type == 'csv') {echo 'checked=\"checked\"';} ?>> CSV
      <br><br>or download:
      <input type='radio' name='export_type' value='csv_dl'
        <?php if ($export_type == 'csv_dl') {echo 'checked=\"checked\"';} ?>> CSV
      <br><br>
      <input type='submit' name='submit' value='submit'></font>
    </form>
  </body>
</html>
