﻿<?php
include ("../../strfunc.php");
include ("../../../../connect.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = "http://www.igromania.ru/articles/?section=37";
	$page=file_get_contents($link);
  	$page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	$content_html = SelectNode($page, "div", "class=\"block12_content\"");

	$items = SelectNodes($content_html, "div", "class=\"articleitem\"");
	foreach ($items as $item)
	{
		$url_div = SelectNode($item, "span", "class=\"block3_newslist_capture\"");
		$new_url = SelectiSubs(SelectiSubs($url_div, "Рецензии</a>", "class=\"articlesbaseresultheader\""),'href="','"');
		if ($new_url!='')
		{
			$new_url='http://www.igromania.ru'.$new_url;
			$new_text = trim(htmlspecialchars_decode(str_replace(array('&laquo;','&raquo;', '&nbsp;', "
	", '&#8212;', '&mdash;', '&quot;'),array('', '', ' ', '', '—', '—', '"'),strip_tags(SelectNode($url_div,'a','class="articlesbaseresultheader"')))));
			
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