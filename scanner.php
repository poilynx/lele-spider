<?php
require_once("simple_html_dom.php");
require_once("utils.php");
require_once("config.php");
/*

电影专栏        /dyzl/..[42]
电视剧专栏      /dsjzl/sgnb/sgnb25.mp4
动画专栏        /dhzl/hmjzzfczx[42].mp4
高清专栏        /gqzl/xltfn[42].mp4
经典视频        /jdsp/yidongmigong[42].mp4
film soap cartoon hd classic
*/

function rpath2lpath($path)
{
	global $config;
	if(!preg_match('/^(?:ftp:\\/\\/.+?\\/)(.+?)\\/(.+?)(?:\\[42\\])?(?:\\.\b\w+$)/',$path,$match)){
		return FALSE;
	}
	$type = $match[1];
	$filename = trim(str_replace('/','-',$match[2]));
	//var_dump($match);
	$idx=0;
	//echo "type:$type.\n";
	//echo "categories\n";
	//var_dump($config["categories"]);
	for($i=1;$i<count($config["categories"]);$i++) {
		//echo $i."\n";
		//echo $config["categories"][$i][0]."\n";
		if($config["categories"][$i][0]==$type) {
			$idx=$i;
			break;
		}
	}
	//var_dump($config["categories"][$idx]);
	$filename = $config["categories"][$idx][1]."-".$filename;
	return $filename;

}
/*
function test()
{
	echo "test\n";
	//parse_file_path('ftp://172.24.4.40/jdsp/yidongmigong.mp4');
	var_dump(parse_file_path('ftp://192.168.102.40/dsjzl/sgnb/sgnb25.mp4'));
}*/

function html_parse($html)
{
	$char_from=array( '&ensp;','&emsp;','&nbsp;','&lt;','&gt;','&amp;','&quot;','&copy;','&reg;','&times;','&divide;','&hellip;','&ldquo;','&rdquo;','&lsquo;','&rsquo;');
	$char_to=array(" "," "," ","<",">","&","\"","©","®","×","÷","...","“","”","‘","’");
	
	for($i=0;$i<count($char_from);$i++) {//转意char_from表中定义的转意字符
		$html = str_replace($char_from[$i],$char_to[$i],$html);
	}
	
	$html = str_replace('/&\\b\\w+;/'," ",$html);//去掉多余的&xx;转意字符
	$html = str_replace('/^\\s+|\\s+$/',"",$html);//去掉前后多余的空格
	return $html;
}


function get_movie_id_list($page)
{
	$idlist = array();
	$html = file_get_html("http://192.168.102.41/42movie/user/userIndex_UserIndex.do?pageNumber=$page");
	if($html==false)
		return false;
	$es = $html->find("div[class=lastest_list] ul[class=list_ul] li");
	foreach($es as $movie){
		$e = $movie->find("div[class=file_img title_hover] a",0);
		if(preg_match('/\d+$/',$e->href,$matches) && !empty($matches))
			#call_user_func_array($callback,array($matches[0]));
			$idlist[] = $matches[0];
	}
	return $idlist;
}
//var_dump(get_movie_id_list_by_type(1));

function get_movie_id_list_by_type($type,$page)
{
	$idlist = array();
	$html = file_get_html("http://192.168.102.41/42movie/user/userIndex_UserList.do?category_id=$type&pageNumber=$page");
	if($html==false)
		return false;
	$es = $html->find("div[class=lastest_list] ul[class=list_ul] li");
	foreach($es as $movie){
		$e = $movie->find("div[class=file_img title_hover] a",0);
		if(preg_match('/\d+$/',$e->href,$matches) && !empty($matches))
			#call_user_func_array($callback,array($matches[0]));
			$idlist[] = $matches[0];
	}
	return $idlist;
}
function get_movie_info($id)
{
	$match=array();
	$html = file_get_html("http://192.168.102.41/42movie/user/userIndex_UserDetail.do?movie_id=$id");
	$es = $html->find("div[class=post_info] h5");
	preg_match("/^【.*】/",$es[0]->innertext,$match);
	$sort= trim(preg_replace('/^【|】$|\s+/','',$match[0]));

	//if($sort!="电影专栏")
	//	return FALSE;//无法处理
	$es = $html->find("div[class=post_info_img] a img");
	$name = $es[0]->alt;
	$img = "http://192.168.102.41/".$es[0]->src;
	preg_match('/\d+\.\b\w+$/',$es[0]->src,$match);
	$img_fname = $match[0];
	$es = $html->find("ul[class=info_word] li a");
	$type = trim($es[0]->innertext);
	$category=0;
	switch((trim($type))){
		case '电影专栏';
			$category = 1;
			break;
		case '电视剧专栏';
			$category = 2;
			break;
		case '动画专栏';
			$category = 3;
			break;
		case '高清专栏';
			$category = 4;
			break;
		case '经典视频';
			$category = 5;
	}
	//echo $category."\n";
	$es = $html->find("ul[class=info_word spec_size] li");
	preg_match('/\d{4}-\d\d-\d\d \d\d:\d\d:\d\d/',$es[0]->innertext."\n",$match);
	$time = $match[0];
	preg_match_all('/\d+/',$es[3]->innertext,$match);
	$downloads = $match[0];
	$es = $html->find("a[class=hero_name]");
	$auther = $es[0]->innertext;
	
	$es = $html->find("ul[class=post_intro]");
	$comment = html_parse($es[0]->plaintext);
	$es = $html->find("p[class=down] a");
	
	$items=array();
	foreach($es as $el){
		preg_match('/\d+$/',$el->href,$match);
		$item_id = $match[0];
		$item_name = str_replace('[本地下载]','',$el->innertext);
		$item_url = 'http://192.168.102.41'.$el->href;
		
		
		//echo "\n".$item_url."\n";
		//echo "m1\n";
		$redic_url = get302url($item_url);
		//echo "m2\n";
		//echo "\n";
		//var_dump($redic_url);
		//echo "ftp:\t".$redic_url."\n";
		if(!$redic_url)
			return FALSE;
		//preg_match('/\\/(dyzl|dsj)\\/.+?\.mp4$/',$redic_url,$match);
		//$item_file = $match[0];
		//echo $item_file."\n";
		//$item_file = preg_replace('/\\[42\\]\\..+$/','',$item_file);
		//$item_file = preg_replace('/^\\/dyzl\\//','',$item_file);
		//$item_file = preg_replace('/^\\/dsjzl\\/',''
		//$item_file = str_replace('.mp4','',$item_file);
		//echo $item_file."\n";
		//$item_file = basename($redic_url);
		//$item_file = preg_replace('/\\..+$/','',$item_file);
		$item_file = rpath2lpath($redic_url);
		$items[]=array('id'=>"$item_id",'name'=>"$item_name",'url'=>"$item_url",'filename'=>"$item_file");
		
	}
	$result=array();
	$result["id"] = $id;
	$result["category"] = $category;
	$result["name"] = $name;
	$result["type"] = $type;
	$result["sort"] = $sort;
	$result["time"] = $time;
	$result["img"] = $img;
	$result["img_file"] = $img_fname;
	$result["auther"] = $auther;
	$result["downloads"] = $downloads;
	$result["comment"] = $comment;
	$result["items"] = $items;
	return $result;

}

/*

for($i=1;$i<30;$i++) {
$list = get_movie_id_list($i);
if(!$list)
	exit(0);
foreach($list as $id) {
	 var_dump(get_movie_info($id));

}
}
*/










?>
