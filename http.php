#! /usr/bin/php
<?php
	error_reporting(0);
	if($argc>=3 && !strcmp($argv[1],"header"))
	{
		$header =get_headers($argv[2]);
		if(!$header)
			exit(1);
		foreach($header as $item)
		{
			//echo $item."\n";
			if(preg_match('/^Location:.*$/',$item,$match)){
				$url=trim(preg_replace('/^Location:/','',$match[0]));
				echo $url;
				exit(0);
			}
		}
		exit(3);
	}else if($argc>=4 && !strcmp($argv[1],"get")){
		$fr = fopen($argv[2],"r");
		if(!$fr)
			exit(4);
		$fw = fopen($argv[3],"w");
		if(!$fw)
		{
			fclose($fr);
			exit(5);
		}
		$size = 0;
		while(!feof($fr)){
			$buf=fread($fr,2048);
			$size += strlen($buf);
			fwrite($fw,$buf);
		}
		fclose($fr);
		fclose($fw);
		
	}else
		exit(2);
?>
