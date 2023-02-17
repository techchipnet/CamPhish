<?php

$date = date('dMYHis');
$imageData=$_POST['cat'];
$output_dir="out/";

if (!empty($_POST['cat'])) {
error_log("Received" . "\r\n", 3, "Log.log");

}

$filteredData=substr($imageData, strpos($imageData, ",")+1);
$unencodedData=base64_decode($filteredData);

if (!file_exists($output_dir)) {
    mkdir($output_dir, 0777, true);
}

$fp = fopen( $output_dir.'cam'.$date.'.png', 'wb' );
fwrite( $fp, $unencodedData);
fclose( $fp );

exit();
?>

