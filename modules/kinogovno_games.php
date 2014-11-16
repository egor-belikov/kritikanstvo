<?php
include ("../../../../strfunc.php");
include ("../../../../core.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = 'http://www.kino-govno.com/reviews/games/';
	$page=file_get_contents($link);
  	// $page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	$content_html = SelectNode($page, 'div', 'class="reviews_list');

	$items = SelectNodes($content_html, 'div', 'class="list_item"');
	foreach ($items as $item)
	{
		$url_div = SelectNode($item, 'span', 'itemprop="original"');
		// $url_div = $item;
		$new_url = SelectiSubs($url_div, 'href="', '"');	
		if ($new_url!='')
		{
			$new_url='http://www.kino-govno.com'.$new_url;
			$new_text = trim(htmlspecialchars_decode(str_replace(array('&laquo;','&raquo;', '&nbsp;', "
", '&#8212;', '&mdash;', '&quot;', '&#8217;',
			''),array('', '', ' ', '', '—', '—', '"', "'",
			''),strip_tags(SelectNode($url_div,'a')))));
			
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