<?php
require_once("scanner.php");
require_once("transcode.php");
require_once("config.php");
require_once("db.php");
require_once("utils.php");
//global $config;
/*
function download($url,$filename)
{
	//sleep(2);
	
	$fw = fopen($filename,"w");
	fclose($fw);
	
	return TRUE;
}
*/

$connstr = array(
        'host' => 'localhost',
        'user' => 'root',
        'password' => 'meteorshower.',
        'port' => 3306,
        'database' => 'neulife',
        'charset'=> 'utf8');
$dao = & new DataAccess();
$dao->connect($connstr);
$dao->query("set names utf8");

for($i=1;$i<=1000;$i++)
{
	$list = get_movie_id_list($i);
	foreach($list as $id) {
		printf("Movie found! [%d],Filtering. . .",$id);
		if($id!=3684){//过滤掉指定电影项
			echo "ignored\n\n";
			continue;
		}
		echo "OK\n";

		printf("Scanning movie detail html page . . .");
		if(!$movie = get_movie_info($id))//读取详情
		{
			echo "failed\n\n";
			continue;
		}

		if($movie["category"]==0){//放弃该分类
			echo "ignored\n\n";
			continue;
		}
		
		echo "OK\n";

		echo "Updating movie data table. . .";
		if(!update_movie($dao,$movie))
		{
			echo "failed\n\n";
			continue;
		}else
		echo "OK\n";

		echo "Downloading picture. . .";
		$filename_picture = $config["pictures_dir"].$movie["img_file"];
		if(!file_exists($filename_picture)) {
			if(!download($movie["img"],$filename_picture))
			{
				echo "failed\n";
				continue;
			}
			echo "OK\n";
		}else
			echo "ignored\n";

		foreach($movie["items"] as $item) {
			$filename_downloaded = $config["catche_dir"].$item["id"].".video";
			$filename_downloading = $filename_downloaded.".tmp";
			$filename_coded = $config["videos_dir"]
				.$item["filename"].$config["suffix"];

			//echo "paths:\n";
			//echo $filename_downloaded."\n";
			//echo $filename_downloading."\n";
			//echo $filename_coded."\n";
			if(is_movie_item_ready($dao,$item["id"])) {
				echo "Video file[{$item["id"]}] update operation ignored.\n";
				continue;
			}
			printf("Catching video file [%s]. . .",$item["id"]);
			if(!file_exists($filename_downloaded)) {
				set_movie_item_status($dao,$item["id"],$MIS_CATCHING);
				if(!download($item["url"],$filename_downloading)) {
					set_movie_item_status($dao,$item["id"],$MIS_ERROR);
					echo "failed\n";
					continue;
				}
				rename($filename_downloading,$filename_downloaded);
			}
			$rawsize = filesize($filename_downloaded);
			echo "OK\n";

			printf("Transcoding video [%s]. . .",$item["id"]);
			$filename = $item["filename"];
			set_movie_item_status($dao,$item["id"],$MIS_TRANSCODING);
			if(!transcode($filename_downloaded,$filename_coded,800,480,15)){
				//转码失败，但不删除原视频
				set_movie_item_status($dao,$item["id"],$MIS_ERROR);
				echo "failed\n";
				continue;
			}
			echo "OK\n";

			unlink($filename_downloaded);
		
			$size = filesize($filename_coded);
			echo "-Updating movie item record. . .";
			if(!set_movie_item($dao,$item["id"],$filename,$size,$rawsize)) {
				//set_movie_item_status($dao,$item["id"],$MIS_ERROR);
				echo "failed\n";
			}
			set_movie_item_status($dao,$item["id"],$MIS_READY);
			echo "OK\n";

			
		}
	}

}

?>
