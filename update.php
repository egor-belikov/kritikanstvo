<?php
include ('../../../connect.php');

@$query = $_GET['query'];
@$id = $_GET['id'];
@$url = $_GET['url'];
@$text = $_GET['text'];

if($query == 'save' & $id !== null & $url !== null & $text !== null & $id !== 'null' & $url !== 'null' & $text !== 'null') {
	@mysql_query("SET NAMES utf8", con());
	mysql_select_db('kritikanstvo');
	$sql = "UPDATE kritikanstvo.publications_revise
			SET
			  url = '".mysql_real_escape_string($url)."', `text` = '".mysql_real_escape_string($text)."', last_timestamp=".mktime()."
			WHERE
			  id = ".$id;
	if(mysql_query($sql))
	{
		echo "OK";
	}
} else
if($query == 'reset' & $id !== null & $id !== 'null') {
	@mysql_query("SET NAMES utf8", con());
	mysql_select_db('kritikanstvo');
	$sql = "UPDATE kritikanstvo.publications_revise
			SET
			  url = null, text='', last_timestamp=0
			WHERE
			  id = ".$id;
	if(mysql_query($sql))
	{
		echo "OK";
	}
}

?>