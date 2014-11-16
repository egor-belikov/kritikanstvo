<?php
include ("../../../../strfunc.php");
include ("../../../../core.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = 'http://www.3dnews.ru/games/';
	$page=file_get_contents($link);
  	// $page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	//$content_html = SelectNode(between($page,'<span class="left strong">Игры</span>','<div class="content-block-header">'), 'ul', 'class="article-list"');

	$content_html = SelectNode($page, 'div', 'id="section-content"');
	
	
	$items = SelectNodes($content_html, 'div', 'class="content-block-data');
	foreach ($items as $item)
	{
		$url_div = SelectNode(SelectNode($item, 'div', 'class="header"'),'a');
		//$url_div = RemoveNode($item,'span');
		$new_url = SelectiSubs($url_div, 'href="', '"');
		if ($new_url!='')
		{
			$new_url='http://www.3dnews.ru'.$new_url;
			$new_text = trim(htmlspecialchars_decode(str_replace(array('&laquo;','&raquo;', '&nbsp;', "
	", '&#8212;', '&mdash;', '&quot;'),array('', '', ' ', '', '—', '—', '"'),strip_tags($url_div))));
			
			// if (!mb_stristr($new_text,'Рецензи') && !mb_stristr($new_text,'рецензи'))
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