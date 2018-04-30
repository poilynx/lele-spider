<?php

require_once("DataAccess.php");

$MIS_WAITING	=0;
$MIS_CATCHING	=1;
$MIS_TRANSCODING=2;
$MIS_ERROR	=3;
$MIS_READY	=4;

function update_movie($dao,$movie)
{
	$dao->beginTransaction(false);
	$dao->prepare('select count(*) from movie where rid=:rid;');

#echo "id:\t".$movie->id ."\n";
	$dao->bindValue(":rid",$movie["id"]);
	if(!$dao->execute())
	{
		$dao->rollback();
		return false;
	}
	$res = $dao->resultSet();
	if($res[0]["count(*)"]==0)
	{
		$sql=<<<EOT
insert into movie (
rid,name,type,sort,category,comment,auther,img,dlw,dlm,dla,time
) values (
:rid,:name,:type,:sort,:category,:comment,:auther,:img,:dlw,:dlm,:dla,:time
);
EOT;
		$dao->prepare($sql);
		$dao->bindValue(':rid',$movie["id"]);
		$dao->bindValue(":name",$movie["name"]);
		$dao->bindValue(":type",$movie["type"]);
		$dao->bindValue(":sort",$movie["sort"]);
		$dao->bindValue(":category",$movie["category"]);
		//$dao->bindValue(":comment",$movie["comment"]);
		$dao->bindValue(":comment","TEST");
		$dao->bindValue(":auther",$movie["auther"]);
		$dao->bindValue(":img",$movie["img_file"]);
		$dao->bindValue(":dlw",$movie["downloads"][0]);
		$dao->bindValue(":dlm",$movie["downloads"][1]);
		$dao->bindValue(":dla",$movie["downloads"][2]);
		$dao->bindValue(":time",($movie["time"]));
#$dao->bindValue(":tctime",time());
		if(!$dao->execute())
		{
			$dao->rollback();
			return false;
		}
		$sql=<<<EOT
insert into movie_item (
movie_id,rid,name
) values (
:movie_id,:rid,:name
);
EOT;
		$insert_id=$dao->insert_id();
		foreach($movie["items"] as $item) {
			$dao->prepare($sql);
			$dao->bindValue(":movie_id",$insert_id);
			$dao->bindValue(":rid",$item["id"]);
			$dao->bindValue(":name",$item["name"]);
			//$dao->bindValue(":size",3333);//测试
			//$dao->bindValue(":path",$item["filename"]);//测试
			if(!$dao->execute()) {
				$dao->rollback();
				return false;
			}
		}
	}else{
		$sql='update movie set dlw=:dlw,dlm=:dlm,dla=:dla where rid=:rid;';
		$dao->prepare($sql);
		$dao->bindValue(":dlw",$movie["downloads"][0]);
		$dao->bindValue(":dlm",$movie["downloads"][1]);
		$dao->bindValue(":dla",$movie["downloads"][2]);
		$dao->bindValue(":rid",$movie["id"]);
		if(!$dao->execute())
		{
			$dao->rollback();
			return false;
		}
	}
	$dao->commit();
	return true;
}
function set_movie_item($dao,$rid,$path,$size,$rawsize) {
	$path = trim($path);
	if($path==""){
		echo "Path is empty.\n";
		exit(1);
	}
	$dao->beginTransaction(TRUE);
	$dao->prepare('update movie_item set path=:path,size=:size,rawsize=:rawsize where rid=:rid');
	$dao->bindValue(':path',$path);
	$dao->bindValue(':size',$size);
	$dao->bindValue(':rawsize',$rawsize);
	$dao->bindValue(':rid',$rid);
	if(!$dao->execute()) {
		return NULL;
	}

	return TRUE;
}
function is_movie_item_ready($dao,$rid)
{
	$dao->beginTransaction(TRUE);
        $dao->prepare('select path from movie_item where rid=:rid');
        $dao->bindValue(":rid",$rid);
	if(!$dao->execute())
        {
                return NULL;
        }
        $res = $dao->resultSet();
        if(empty($res) || $res[0]["path"]==NULL)
        {
                return FALSE;
        }
	return TRUE;
}

function set_movie_item_status($dao,$rid,$status)
{
	$dao->prepare('update movie_item set status=:status where rid=:rid');
	$dao->bindValue(':rid',$rid);
	$dao->bindValue(':status',$status);
	if($dao->execute())
		return TRUE;
	else
		return FALSE;
}
?>
