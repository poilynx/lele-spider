<?php
function transcode($src,$dest,$width,$height,$fps){
	//
	#sleep(2);
	/*
	$fp = fopen($dest,"w");
	if(!$fp)
		return FALSE;
	else {
		fwrite($fp,strval($width)."\n");
		fwrite($fp,strval($fps)."\n");
		return TRUE;
	}
	*/
	//$cmd = "mencoder \"$src\" -o \"$dest\" -ovc lavc -really-quiet -oac lavc -lavcopts vcodec=mpeg1video -vf scale=$width:$height -of mpeg -aid 0 -fps $fps ";
	if(file_exists($dest))
		unlink($dest);
	$cmd = "avconv -loglevel panic -i \"$src\" -r $fps -s {$width}x{$height} -c:v libx264 \"$dest\"";
	//$cmd = "";
	//echo "\n".$cmd."\n";
	system($cmd,$retval);
	return $retval===0?TRUE:FALSE;
}
//transcode("neusoft.f4v","neusoft.mp4",800,480,15);
?>
