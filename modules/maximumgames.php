﻿<?php
include ("../../strfunc.php");
include ("../../../../connect.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = 'http://www.maximumgames.ru/articles/reviews/';
	$page=file_get_contents($link);
  	$page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	$content_html = SelectNode(SelectNode(SelectNode($page, 'div', 'class="content-block"'),'div','class="left-col"'),'div','dle-content');
	// echo $content_html;
	$items = SelectNodes($content_html, 'div', 'class="news-item"');
	foreach ($items as $item)
	{
		$url_div = SelectNode(SelectNode($item, 'div', 'class="news-item-content"'),'h2');
		// $url_div = $item;
		$new_url = SelectiSubs($url_div, 'href="', '"');	
		if ($new_url!='')
		{
			$new_url=''.$new_url;
			$new_text = trim(htmlspecialchars_decode(str_replace(array('&laquo;','&raquo;', '&nbsp;', "
	", '&#8212;', '&mdash;', '&quot;'),array('', '', ' ', '', '—', '—', '"'),strip_tags($url_div))));
			
			// if (mb_stristr($new_text,'превью') || mb_stristr($new_text,'Впечатления'))
			//	$new_text='';
				
			if($new_url !== $url)
			{
				$a[$n]['url'] = $new_url;
				$a[$n]['text'] = $new_text;
				$n++;
			} else
			{
				break;
			}
		}
	}
	@$js=json_encode($a);
	echo $js;

	@mysql_query("SET NAMES utf8", con());
	mysql_select_db('kritikanstvo');
	$sql = "UPDATE kritikanstvo.publications_revise
		SET
		  `timestamp` = ".time()."
		WHERE
		  id = ".$id;
	mysql_query($sql);
}

?>