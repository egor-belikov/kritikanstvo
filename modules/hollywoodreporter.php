<?php
include ("../../strfunc.php");
include ("../../../../connect.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = "http://www.thr.ru/articles/cinema/reviews/";
	$page=file_get_contents($link);
  	$page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	$content_html = SelectNode($page, "ul", "class=\"articles\"");

	$items = SelectNodes($content_html, "li");
	foreach ($items as $item)
	{
		$url_div = SelectNode(SelectNode($item, "dl"), "dt");
		$new_url = SelectiSubs($url_div, "href=\"", "\"");
		if ($new_url!='')
		{
			$new_url='http://www.thr.ru'.$new_url;
			$new_text = trim(htmlspecialchars_decode(str_replace(array('&laquo;','&raquo;', '&nbsp;', "
"),'',strip_tags($url_div.': '.SelectNode(SelectNode($item, "dl"), "dd", 'class="_text"')))));
			
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