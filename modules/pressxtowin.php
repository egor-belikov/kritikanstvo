﻿<?php
include ("../../../../strfunc.php");
include ("../../../../core.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = 'http://pressxtowin.com/categories/Reviews';
	$page=file_get_contents($link);
  	// $page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	$content_html = SelectNode($page, 'div', 'posts-wrapper');

	$items = SelectNodes($content_html, 'div', 'class="post-item');
	foreach ($items as $item)
	{
		$url_div = SelectNode(SelectNode($item, 'div', 'class="main"'),'h3');
		// $url_div = $item;
		$new_url = str_replace('#cover','',SelectiSubs($url_div, 'href="', '"'));	
		if ($new_url!='')
		{
			$new_url='http://pressxtowin.com'.$new_url;
			$new_text = trim(htmlspecialchars_decode(str_replace(array('&laquo;','&raquo;', '&nbsp;', "
", '&#8212;', '&mdash;', '&quot;', '&#8217;',
			'Рецензия на'),array('', '', ' ', '', '—', '—', '"', "'",
			''),strip_tags($url_div))));
			
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