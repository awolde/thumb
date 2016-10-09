<?php
error_reporting(E_ALL ^ E_NOTICE);
include('dbconnect.php');
if (!isset($argv[1]) || !isset($argv[2])) {
	echo "Usage: $argv[0] photo-group path-to-your-photos\n";
	exit(2);
 }
$source=$argv[2];
$group=$argv[1];
//echo "grp = $group \n";
define (webdir, "/web/html/thumb");
getFilesFromDir ($source,$group);


function getFilesFromDir($dir,$group) {
  //$files = array();
  if ($handle = opendir($dir)) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && !preg_match('/^._/',$file)) {
            if(is_dir($dir.'/'.$file)) {
                $dir2 = $dir.'/'.$file;
                getFilesFromDir($dir2,$group);
            }
            else {
		if (checkExt($file)) {
	            //  echo $dir.'/'.$file.'<br>';
			$filler=rand();
			$thumb="thumb_".$filler.$file;
			$exif = exif_read_data ("$dir/$file");
			echo $exif===false ? "No header data found! " : "";
			//$exif = read_exif_data ("$dir/$file");
			if ($exif) { 
			 //$dateTaken=$exif['DateTime'];
			  $comment=$exif['Comments'];
		        }
		  date_default_timezone_set('America/Chicago');
		  $dateTaken=date("Y-m-d H:i:s", filemtime("$dir/$file"));
		//if the file is not already in the databes, create the thumbnail and add it to the db
		  if (insertPic ($dir.'/',$file,$thumb,$group,$dateTaken,$comment)) {
			convert($dir.'/',$file,$thumb);
		  }
		}
            }
        }
      }
      closedir($handle);
    }
  } 

function removeGroup ($group) {
	$query="SELECT thumbnail FROM tblpic WHERE picgroup='$group'";
	$result = mysql_query($query) or die(mysql_error());
	while($row = mysql_fetch_array($result)){
                $filename=$row['thumbnail'];
		echo "Deleting $filename\n";
		shell_exec ("rm /web/html/thumb/$filename");
        }
}
	
	

function insertPic ($dir,$filename,$thumb,$group,$dateTaken,$comment) {
	//$group="Family";//$argv[1]
	//echo $group;
        $filename = addslashes($filename);
	$dir=addslashes($dir);
	$link = str_replace("/web/html/photos","photos",$dir.$filename);
        //CHECK IF THE FOTO EXISTS IN THE DATABASE
        $query="SELECT filename, link FROM tblpic WHERE filename='$filename' AND link='$link'";
        $result = mysql_query($query) or die(mysql_error());
        $num_rows = mysql_num_rows($result);
        if ($num_rows) {
                echo $filename." - ".$link." exists!\n";
		$query="SELECT filename, comment FROM tblpic WHERE filename='$filename' AND comment='$comment'";
		$result = mysql_query($query) or die(mysql_error());
		$num_rows2 = mysql_num_rows($result);
		if (!$num_rows2) {
			echo "Updating the comments on file $filename...........\n";
			$query="UPDATE tblpic SET comment='$comment' WHERE filename='$filename' AND link='$link'";
			$result = mysql_query($query) or die(mysql_error());
		}
		return false;
    //            global $existing;
      //          $existing++;
        }
        else {
                $query="INSERT INTO tblpic (filename, link, picgroup, thumbnail, dateTaken, comment) VALUES ('$filename',  '$link', '$group', '$thumb', '$dateTaken', '$comment')";
		//echo "\n $query \n";
                mysql_query($query) or die(mysql_error());
		return true;
        }
}

function checkExt ($filename) {
	$ext = end(explode('.', $filename));
	if (!strcasecmp($ext,"JPG") || !strcasecmp($ext,"PNG")) return true;
	else return false;
}

function convert ($images_folder,$from_name,$to_name) {
        $thumbs_folder = '/web/html/thumb/thumbnails/';
	echo $images_folder.$from_name."\n";
	$out=shell_exec ("djpeg '$images_folder/$from_name' | pnmscale -xsize 300 | cjpeg -optimize -progressive > $thumbs_folder/$to_name");
	echo $out;
}

