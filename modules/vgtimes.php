<?php
include ("../../strfunc.php");
include ("../../../../connect.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = "http://www.vgtimes.ru/tags/%EE%E1%E7%EE%F0/";
	$page=file_get_contents($link);
  	$page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	$content_html = $page;

	$items = SelectNodes($content_html, "div", 'class="item-name"');
	foreach ($items as $item)
	{
		$url_div = str_replace(array('<span>','</span>'),array('',''),SelectNode($item, "a"));
		$new_url = SelectiSubs($url_div, 'href="', '"');	
		if ($new_url!='')
		{
			$new_url='http://www.vgtimes.ru'.$new_url;
			$new_text = trim(htmlspecialchars_decode(str_replace(array('&laquo;','&raquo;', '&nbsp;', "
	", '&#8212;', '&mdash;', '&quot;', 'Обзор '),array('', '', ' ', '', '—', '—', '"',''),strip_tags(SelectNode($url_div,'a')))));
			
			if (mb_strstr($new_text,'Превью') || mb_strstr($new_text,'Решение проблем'))
				$new_text='';
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