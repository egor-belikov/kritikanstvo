<?php

function q ($query)
{

 if (strpos($query,'/*N*/')===FALSE && strpos($query,'INFORMATION_SCHEMA')===FALSE && strpos($query,'cnt.js')===FALSE)
 {
	$ret=mysql_db_query ("kritikanstvo",$query);

 	return $ret;
 }
}

function fq ($query)
{
	return mysql_fetch_array(q($query));
} 

function faq ($query,$key)
{
	$ar=mysql_fetch_array(q($query));
	return $ar[$key];
} 


function con ()
{
	$link=mysql_connect('localhost', 'kritikanstvoadm', 'Kr!kMyNow');
	return $link;
}



?>
