<?php
include('dbconnect.php');
if (!isset($argv[1]) ) {
        echo "Usage: $argv[0] photo-group \n";
        exit(2);
 }
else removeGroup ($argv[1]);

function removeGroup ($group) {
        $query="SELECT thumbnail FROM tblpic WHERE picgroup='$group'";
        $result = mysql_query($query) or die(mysql_error());
        while($row = mysql_fetch_array($result)){
                $filename=$row['thumbnail'];
                echo "Deleting $filename\n";
		$cmd="rm \"/web/html/thumb/thumbnails/$filename\"";
//		echo $cmd;
                shell_exec ($cmd);
        }
	$query="DELETE FROM tblpic WHERE  picgroup='$group'";
	$result = mysql_query($query) or die(mysql_error());
	mysql_close();
}
?>
