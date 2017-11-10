<?php
	echo '<link rel="stylesheet" type="text/css" href="'.dirname(__FILE__)."/css/xmlimport.css".'">';
	include dirname(__FILE__)."/conn.php";
	//include dirname(__FILE__)."/process.php";
	$sources=getXMLlist();
	//parse_category('Elso kategoria / alkategoria / Al alkategoria');
	//print(parse_category('Elso kategoria / alkategoria').'\n');
	//var_dump(explode(' / ', 'Irodaszerek, irodatechnika / Címkenyomtatók, szalagok / Készülék'));
?>

<table class="table-striped">
	<thead>
		<tr>
			<th>Forrás</th>
			<th>URL</th>
			<th>Adatbázis frissítve</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php 
			$i=0;
			foreach($sources as $source) { 
			echo '<form method="post" action="http://'.$_SERVER['SERVER_NAME'].'/wp-content/plugins/xmlimport/process.php">';
				//$sxml = simplexml_load_file($source['url']);
				echo '<tr>';
				echo '	<td><input type="text" readonly id="name" name="name" value="'.$source['name'].'""></td>';
				echo '	<td><input type="text" readonly id="url" name="url" value="'.$source['url'].'"></td>';
				//echo '	<td><input type="text" readonly id="last_update_date" name="last_update_date" value="'.$sxml->header->last_update_date.'"></td>';
				echo '	<td><input type="text" readonly id="last_import" name="last_import" value="'.$source['last_import'].'"></td>';
				//if ($sxml->header->last_update_date <> $source['last_import']) {
					echo '<td>';
					echo '		<input type="submit" value="Frissít" name="submit">';
					echo '</td>';
				//}
				echo '</tr>';
			echo '	</form>';
			}
		?>
	</tbody>
</table>

<?php
	echo '<script type="text/javascript" src="'.dirname(__FILE__)."/js/xmlimport.js".'"></script>';
?>
