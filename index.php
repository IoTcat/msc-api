<?php
header('Access-Control-Allow-Origin:*');


/* anti ddos */
/*if(!isset($_COOKIE['_token__']) || $_COOKIE['_token__'] != md5(date('Y-m-d-H'))) {
    setcookie("_token__",md5(date('Y-m-d-H')),time()+1*3600);
    header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 301);
}*/


require 'vendor/autoload.php';
include './functions.php';

header("Content-Type: application/json;charset=utf-8");

use Metowolf\Meting;

$API = new Meting('netease');

$API->cookie('MUSIC_U='.substr(md5(time()), 0, 5).'; buildver=1506310741; resolution=1920x1080; mobilename=MI5; osver=7.0.1; channel=coolapk; os=android; appver=4.2');


$type = $_REQUEST['type'];
$id = $_REQUEST['id'];
$random = $_REQUEST['random'];
$limit = $_REQUEST['limit'];


if($type == "url"){
	if(!isset($id)){
		echo json_encode(array("code"=>500, "err"=>"You need to provide an id!!"));
		die();
	}
	$res = get_object_vars(json_decode($API->format(true)->url($id, 320)));
	if(in_array("url", $res)){
		echo json_encode(array("code"=>404, "err"=>"No Found!!"));
		die();
	}
	$res['url'] = str_replace("http", "https", $res['url']);
	log_api();
	header("Location: ".$res['url']);
	die();
}


if($type == "cover"){
	if(!isset($id)){
		echo json_encode(array("code"=>500, "err"=>"You need to provide an id!!"));
		die();
	}
	$res = get_object_vars(json_decode($API->format(true)->pic($id)));
	if(in_array("url", $res)){
		echo json_encode(array("code"=>404, "err"=>"No Found!!"));
		die();
	}
	log_api();
	header("Location: ".$res["url"]);
	die();
}



if($type == "lrc"){
	if(!isset($id)){
		echo json_encode(array("code"=>500, "err"=>"You need to provide an id!!"));
		die();
	}
	$res = get_object_vars(json_decode($API->format(true)->lyric($id)));
	if(in_array("lyric", $res)){
		echo json_encode(array("code"=>404, "err"=>"No Found!!"));
		die();
	}
	log_api();
	header("Content-Type: text/plain;charset=utf-8");
	echo $res["lyric"];
	die();
}





if($type == "single"){
	if(!isset($id)){
		echo json_encode(array("code"=>500, "err"=>"You need to provide an id!!"));
		die();
	}
	$content = get_object_vars(getSongInfo($id, $API)[0]);
	//var_dump($content);
	$o = array("id"=>$content["id"], "name"=>$content["name"], "artist"=>$content["artist"][0], "album"=>$content["album"], "url"=>"https://api.yimian.xyz/msc/?type=url&id=".$content["url_id"], "cover"=>"https://api.yimian.xyz/msc/?type=cover&id=".$content["pic_id"], "lrc"=>"https://api.yimian.xyz/msc/?type=lrc&id=".$content["lyric_id"]);
	if(!$o){
		echo json_encode(array("code"=>404, "err"=>"Cannot find any songs!!"));
		die();
	}
	echo json_encode($o);
	log_api();
	die();
}


if($type == "playlist"){
	/* 7.18 events */
	//$id="2889727316";
	if(!isset($id)){
		echo json_encode(array("code"=>500, "err"=>"You need to provide an id!!"));
		die();
	}
	$content = array();
	$o = array();

	foreach (getPlaylistInfo($id, $API) as $key => $value) {
		$content = get_object_vars($value);
		array_push($o, array("id"=>$content["id"], "name"=>$content["name"], "artist"=>$content["artist"][0], "album"=>$content["album"], "url"=>"https://api.yimian.xyz/msc/?type=url&id=".$content["url_id"], "cover"=>"https://api.yimian.xyz/msc/?type=cover&id=".$content["pic_id"], "lrc"=>"https://api.yimian.xyz/msc/?type=lrc&id=".$content["lyric_id"]));
	}
	if(!$o){
		echo json_encode(array("code"=>404, "err"=>"Cannot find any songs!!"));
		die();
	}
	if($random) shuffle($o);
	if($limit) $o = array_slice($o, 0, $limit);
	echo json_encode($o);
	log_api();
	die();
}


echo json_encode(array("code"=>500, "err"=>"Cannot find such type!!"));



function getSongInfo($id, $API){
	return json_decode($API->format(true)->song($id));
}

function getPlaylistInfo($id, $API){
	return json_decode($API->format(true)->playlist($id));
}



function log_api(){
	yimian__log("log_api", array("api" => "msc", "timestamp" => date('Y-m-d H:i:s', time()), "ip" => ip2long(getIp()), "_from" => get_from(), "content" => $_SERVER["QUERY_STRING"]));
	return;
}
