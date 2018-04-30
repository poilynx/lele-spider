<?php
//error_reporting(0);
function get302url($url)
{
	$cmd = "./http.php header '$url'";
	exec($cmd,$output,$retval);
	//echo "val\t".$retval."\n";
	//var_dump( $output[0]);
	if($output === FALSE)
		return FALSE;
	if($retval==0) {
		return $output[0];

	}else
		return FALSE;
}

function download($url,$filename)
{
	$cmd = "wget -q \"$url\" -O \"$filename\"";
	$output = system($cmd,$retval);
	//echo "val\t".$retval."\n";
	//var_dump( $output[0]);
	if($output === FALSE)
		return FALSE;
	if($retval==0) {
		return TRUE;

	}else{
		if(file_exists($filename))
			unlink($filename);
		return FALSE;
	}
}
#echo get302url("http://192.168.102.41/42movie/download/DownloadAction.do?movie_items_id=42678");
//echo download("http://192.168.102.41/42movie/download/DownloadAction.do?movie_items_id=42678","tmpfile");
?>
