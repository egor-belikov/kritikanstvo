<?php
include ("../../strfunc.php");
include ("../../../../connect.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = "http://kino.myvi.ru/review";
	$page=file_get_contents($link);
  	// $page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	$content_html = SelectNode($page, "div", "class=\"mv-portlet-content\"");

	$items = SelectNodes($content_html, "div", "class=\"archive-description-container");
	foreach ($items as $item)
	{
		$url_div = SelectNode(SelectNode($item, "div", "class=\"review-container\""),"p");
		$new_url = 'http://kino.myvi.ru'.SelectiSubs($url_div, "href=\"", "\"");

		$new_text = trim(htmlspecialchars_decode(str_replace(array('&laquo;','&raquo;'),'',strip_tags(SelectNode(SelectNode($item, "div", "class=\"poster-container\""),"a",'class="film-title"')))));
		
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