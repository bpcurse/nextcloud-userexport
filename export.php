<?php

// Set temporary folder to store csv files in
define('TEMP_FOLDER', 'export_temp');

// Create and populate CSV file with selected user data and set filename constant
define('CSV_FILENAME', build_csv_file());

// Show some status information (processing time, number of exported accounts, ...)
print_status_message();
// Show buttons for downloading csv file and mass email function
show_control_buttons();
