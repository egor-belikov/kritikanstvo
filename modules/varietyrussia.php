<?php
include ("../../strfunc.php");
include ("../../../../connect.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = "http://www.varietyrussia.com/review/";
	$page=file_get_contents($link);
  	// $page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	$content_html = SelectNode($page, "div", "class=\"result-list\"");

	$items = SelectNodes($content_html, "div", "class=\"result-list-item");
	foreach ($items as $item)
	{
		$url_div = SelectNode($item, "h3");
		$new_url = 'http://www.varietyrussia.com'.SelectiSubs($url_div, "href=\"", "\"");

		$new_text = trim(htmlspecialchars_decode(strip_tags(SelectNode($item, "h3"))));
		
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