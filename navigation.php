<?php include 'config.php' ?>

<div id="navigation">
  <ul>
    <li<?php if ($active_page == "index")
      echo ' id="currentpage"'; ?>><a href="index.php">Server</a></li>
    <li<?php if ($active_page == "users")
      echo ' id="currentpage"'; ?>><a href="users.php">Users</a></li>
    <li<?php if ($active_page == "groups")
      echo ' id="currentpage"'; ?>><a href="groups.php">Groups</a></li>
    <li<?php if ($active_page == "email")
      echo ' id="currentpage"'; ?>><a href="email.php">Email</a></li>
    <li style="float:right;"><a style="font-size: 14px;"
      href="https://github.com/bpcurse/nextcloud-userexport">
      Nextcloud Userexport v1.0.0</a>
  </ul>
</div>
