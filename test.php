<?php
	$mod=$_GET['mod'];
	$id=$_GET['id'];
	if ($mod!='')
		header ('Location: modules/'.$mod.'.php?id='.$id.'&url=1');
?>