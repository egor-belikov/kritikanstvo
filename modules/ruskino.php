<?php
include ("../../../../strfunc.php");
include ("../../../../core.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = 'http://ruskino.ru/review/';
	$page=file_get_contents($link);
  	$page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	$content_html = SelectNode(SelectNode($page, 'table', 'mainCont'),'table','class="Comments"');
	$items = SelectNodes($content_html, 'div', 'class="itemNameTitle2"');
	foreach ($items as $item)
	{	
		// $url_div = SelectNode($item, 'div', 'class="fullText"');
		$url_div = $item;
		$new_url = SelectiSubs($url_div, 'href="', '"');	
		
		if ($new_url!='')
		{
			$new_url=''.$new_url;
			$new_text = trim(htmlspecialchars_decode(str_replace(array('&laquo;','&raquo;', '&nbsp;', "
", '&#8212;', '&mdash;', '&quot;',
			''),array('', '', ' ', '', '—', '—', '"',
			''),strip_tags(between(SelectNode($url_div,'h3'),'',' \ ')))));
			
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