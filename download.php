<?php

// GET parameters
$filename = $_POST['file'];
$mime_type = $_POST['mime'];
$filename_download = $_POST['name'];
$folder = $_POST['temp'];

// Set filename or create one depending on GET parameters
if($filename_download == null) {
  $filename_download = "nextcloud-userlist_" . date("Y-m-d_Hi") . ".csv";
}

download_file($filename, $mime_type, $filename_download, $folder);

/**************************************************************************/

/**
  * Initiate file download
  *
  * The selected file (by filename) will be downloaded and deleted afterwards
  * It can be downloaded using an alternative filename, if supplied
  *
  * @param  $filename           Filename on the server
  * @param  $mime_type          MIME type to be sent in the header
  * OPTIONAL                    DEFAULT: 'application/csv'
  * @param  $filename_download  Filename for download
  * OPTIONAL                    DEFAULT: 'download'
  * @param  $folder             Folder to prepend in front of the filename
  * OPTIONAL                    DEFAULT: '.'
  *
  */
function download_file($filename, $mime_type = 'application/csv',
  $filename_download = 'download', $folder = '.') {
  // make sure file is deleted even if user cancels download
  ignore_user_abort(true);

  header('Content-Type: ' . $mime_type);
  header("Content-Transfer-Encoding: Binary");
  header("Content-disposition: attachment; filename=\"" . $filename_download . "\"");

  readfile($folder . '/' . $filename);
  // delete file
  unlink($folder . '/' . $filename);
}

// EOF
