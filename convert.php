<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>My photo album</title>
<style>
table { border-width: 7px;
border-style: outset; }
td { border-width: medium;
border-style: outset; }
p { border-width: thick;
border-style: solid; }
</style>
</head>
<body>
<?php

$photo;
if (!isset($_GET['photo'] )) {
	exit(2);
 }
else $photo=$_GET['photo'];
if (file_exists($photo)) {
  convert ("/web/html/thumb/".dirname ($photo)."/", basename($photo), "Resized_".basename($photo));
  loadPic ("resized/", "Resized_".basename($photo));
}
else
  echo "File not found: $photo";

function convert ($images_folder,$from_name,$to_name) {
        $thumbs_folder = '/web/html/thumb/resized/';
        echo $images_folder.$from_name."\n";
        shell_exec ("djpeg '$images_folder/$from_name' | pnmscale -xsize 1200 | cjpeg -optimize -progressive > $thumbs_folder/$to_name");
}

function loadPic ($path,$pic) {
	$filename = $path.$pic;
	$file_extension = strtolower(substr(strrchr($filename, "."), 1));
	$fp = @fopen($filename, "rb"); 
	if ($filename == "") {
		echo "<html><title>Download</title><body>No filename given.</body></html>";
		exit();
	} elseif (!file_exists($filename)) {
		echo "<html><title>Download</title><body>File not found.</body></html>";
		exit();
	}
	$contenttype = "image/jpg";
    	header('Content-Description: File Transfer');
   	header('Content-Type: application/octet-stream');
    	header('Content-Disposition: attachment; filename='.basename($filename));
    	header('Content-Transfer-Encoding: binary');
    	header('Expires: 0');
    	header('Cache-Control: must-revalidate');
    	header('Pragma: public');
    	header('Content-Length: ' . filesize($filename));
    	ob_clean();
    	flush();
    	readfile($filename);
	fpassthru($fp);
	exit();
}
?>
