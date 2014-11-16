<?php
include ("../../strfunc.php");
include ("../../../../connect.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = "http://www.filmz.ru/pub/7/";
	$page=file_get_contents($link);
  	$page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	$content_html = SelectNode($page, "div", "class=\"content");

	$items = SelectNodes($content_html, "div", "class=\"article_block");
	foreach ($items as $item)
	{
		$url_div = SelectNode($item, "div", "class=\"text\"");
		$new_url = 'http://filmz.ru'.SelectiSubs($url_div, "href=\"", "\"");

		$new_text = str_replace(array('Рецензия на фильм ','Рецензия на '),array('',''),trim(htmlspecialchars_decode(str_replace(array('&laquo;','&raquo;'),'',strip_tags(SelectNode(SelectNode($item, "h2"),"a"))))));
		
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