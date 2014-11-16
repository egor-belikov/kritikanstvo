<?php
include ("../../strfunc.php");
include ("../../../../connect.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = 'http://www.zoneofgames.ru/forum/index.php?showforum=29';
	$page=file_get_contents($link);
  	$page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	$content_html = SelectNode(SelectNode($page,'div','borderwrap'), 'table', 'ipbtable');
	
	// echo $content_html;
	
	$items = SelectNodes($content_html, 'span', 'tid-span-');
	foreach ($items as $item)
	{
		// $url_div = SelectNode($item, 'div', 'class="fullText"');
		$url_div = $item;
		$new_url = mb_strrchr(SelectiSubs($url_div, 'href="', '"'),'showtopic=');	
		if ($new_url!='')
		{
			$new_url='http://www.zoneofgames.ru/forum/index.php?'.$new_url;
			$new_text = trim(htmlspecialchars_decode(str_replace(array('&laquo;','&raquo;', '&nbsp;', "
	", '&#8212;', '&mdash;', '&quot;','[Рецензия]'),array('', '', ' ', '', '—', '—', '"',''),strip_tags($url_div))));
			
			if (mb_stristr($new_text,'[Превью]') || mb_stristr($new_text,'[Интервью]') || mb_stristr($new_text,'[Авторская колонка]') || mb_stristr($new_text,'[В фокусе]'))
				$new_text='';
				
			if($new_url !== $url)
			{
				if ($new_text!='' && $new_url!='')
				{
					$a[$n]['url'] = $new_url;
					$a[$n]['text'] = $new_text;
					$n++;
				}
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