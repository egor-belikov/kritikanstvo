<?php
include ("../../../../strfunc.php");
include ("../../../../core.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = 'http://meownauts.com/tag/recenziya/';
	$page=file_get_contents($link);
  	// $page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	$content_html = SelectNode($page, 'ul', 'class="blu-post blu-post-grid row clearfix"');

	$items = SelectNodes($content_html, 'div', 'class="post-item-wrap"');
	foreach ($items as $item)
	{
		//echo $item;
		$url_div = SelectNode($item, 'h4');
		// $url_div = $item;
		$new_url = SelectiSubs($url_div, 'href="', '"');	
		if ($new_url!='')
		{
			$new_url='http://meownauts.com'.$new_url;
			$new_text = trim(htmlspecialchars_decode(str_replace(array('&laquo;','&raquo;', '&nbsp;', "
", '&#8212;', '&mdash;', '&quot;', '&#8217;', '&#39;',
			''),array('', '', ' ', '', '—', '—', '"', "'", "'",
			''),strip_tags($url_div))));	
			$category=trim(between(SelectNode(SelectNode($item,'ul','class="list-unstyled dots"'),'li'),'">','</a>'));	
			$category=str_replace(array('Игры','Кино'),array('',''),$category);
			if(($category=='') and ($new_url !== $url))
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