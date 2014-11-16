<?php
include ('../../../connect.php');

@mysql_query("SET NAMES utf8", con());
mysql_select_db('kritikanstvo');

$sql = "SELECT t1.id
			 , t1.url
			 , t1.`text`
			 , t1.`timestamp`
			 , t2.`name`
			 , t2.`codename`
			 , t1.`publication_id`
			 , t1.`page`
			 , t1.`module`
			 , t1.`last_timestamp`
			 , t1.`parser_errors`
		FROM
		  kritikanstvo.publications_revise t1
		JOIN
		  kritikanstvo.publications t2
		ON t1.publication_id = t2.id
		ORDER BY id DESC";
$result = mysql_query($sql);
$line = mysql_fetch_all($result);
for($i=0; $i<count($line); $i++)
{
	$a[$i]['id'] = $line[$i][0];
	$a[$i]['url'] = $line[$i][1];
	$a[$i]['text'] = $line[$i][2];
	$a[$i]['timestamp'] = $line[$i][3];
	$a[$i]['name'] = $line[$i][4];
	$a[$i]['codename'] = $line[$i][5];
	$a[$i]['publication_id'] = $line[$i][6];
	$a[$i]['page'] = $line[$i][7];
	$a[$i]['module'] = $line[$i][8];
	$a[$i]['last_timestamp'] = $line[$i][9];
	$a[$i]['parser_errors'] = $line[$i][10];
}
@$js=json_encode($a);
echo $js;

function mysql_fetch_all($res)
{
	while($row=mysql_fetch_array($res))
	{
		$return[] = $row;
   	}
   	return $return;
}
?>