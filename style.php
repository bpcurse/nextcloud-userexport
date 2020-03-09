<?php
    header("Content-type: text/css; charset: UTF-8");

    /**
      * Set default colors
      *
      */
    $body_background_color = 'white';

    $navigation_background_color = '#4c6489';
    $navigation_text_color = 'white';

    $navigation_hover_background_color = '#8097b9';

    $navigation_current_background_color = '#d56e2d';
    $navigation_current_text_color = 'white';

    $button_connect_background_color = '#4c6489';
    $button_connect_text_color = 'white';

    $button_display_background_color = '#4c6489';
    $button_display_text_color = 'white';

    $button_download_background_color = 'green';
    $button_download_text_color = 'white';

    $button_email_background_color = '#4c6489';
    $button_email_text_color = 'white';

    $table_header_background_color = '#4C6489';
    $table_header_text_color = 'white';

    // Overwrite with config options, if set
    include_once 'config.php';
?>

* {
  font-family: Helvetica, Arial, sans-serif;
}

body {
  background-color: <?php echo $body_background_color ?>;
}

#navigation ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
  overflow: hidden;
  background-color: <?php echo $navigation_background_color ?>;
}

#navigation li {
  float: left;
  border-right: 1px solid #8097b9;
}

#navigation li:last-child {
  border-right: none;
}

#navigation li a {
  display: block;
  color: <?php echo $navigation_text_color ?>;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none;
}

#navigation #currentpage a {
  background: <?php echo $navigation_current_background_color ?>;
  color: <?php echo $navigation_current_text_color ?>;
}

#navigation li a:hover {
  background-color: <?php echo $navigation_hover_background_color ?>;
}

#button-connect {
  background-color: <?php echo $button_connect_background_color ?>;
  color: <?php echo $button_connect_text_color ?>;
  height: 45px;
  width: 300px;
}

#button-display {
  background-color: <?php echo $button_display_background_color ?>;
  color: <?php echo $button_display_text_color ?>;
  height: 45px;
  width: 300px;
}

#button-download {
  background-color: <?php echo $button_download_background_color ?>;
  color: <?php echo $button_download_text_color ?>;
  height: 45px;
  width: 300px;
}

#button-email {
  background-color: <?php echo $button_email_background_color ?>;
  color: <?php echo $button_email_text_color ?>;
  height: 45px;
  width: 300px;
}

table {
  border-collapse: collapse;
}

.no_show_link {
  text-decoration: none;
  color: black;
}

.align_r {
  text-align: right;
}

.align_c {
  text-align: center;
}

.list table, .list td, .list th {
  border: 1px solid #ddd;
}

.list th {
  text-align: left;
  background-color: <?php echo $table_header_background_color ?>;
  color: <?php echo $table_header_text_color ?>;
  padding: 8px 4px 4px;
  cursor: pointer;
}

.list td {
  padding: 4px 4px 0px;
}

.list tr:nth-child(even) {
  background-color: #f2f2f2;
}

.status th, .status td {
  padding: 4px 4px 0px;
}

.status td:nth-child(2) {
  text-align: right;
}
