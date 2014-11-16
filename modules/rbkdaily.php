<?php
include ("../../strfunc.php");
include ("../../../../connect.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = "http://www.rbcdaily.ru/tags/562949982021648";
	$page=file_get_contents($link);
  	// $page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	$content_html = SelectNode($page, "div", "id=\"tags_articles\"");

	$items = SelectNodes($content_html, "div", "class=\"b-tags-item-list");
	foreach ($items as $item)
	{
		$url_div = SelectNode($item, "h3", "class=\"info-summary__header\"");
		$new_url = 'http://www.rbcdaily.ru'.SelectiSubs($url_div, "class=\"info-summary__header-link\" href=\"", "\"");

		$new_text = trim(htmlspecialchars_decode(str_replace(array('&laquo;','&raquo;', '&nbsp;', "
"),'',strip_tags(SelectNode(SelectNode($item, "h3", "class=\"info-summary__header\""),"a",'class="info-summary__header-link"').': '.SelectNode(SelectNode($item, "div", "class=\"info-summary__body-inner\""),"p",'class="info-summary__text"')))));
		
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