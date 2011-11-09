<?php 

/**
 * @file sidebar.inc.php
 * @brief file da includere nelle pagine come menu sinistro.
 * @author  Francesco Lorenzon <grepcesco@gmail.com>
 * @version 0.1
 * @todo
 *   Sistemare il blocco html per la stampa delle categorie e trasferirlo come
 *   funzione di template.
 *
 * @section LICENSE
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details at
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @section DESCRIPTION
 *
 * Questo file viene incluso nelle pagine php per importare il menu
 * a sinistra dei contenuti (categorie).
 *
 */

$args = parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY);
parse_str($args, $array_base);

?>

<aside>
	<ul id="menu">
	<?php // TODO: grafica,polishing : stampa più carina??
	$categorie = Categorie::get_categories('');
	if(empty($categorie))
		echo "<li><div>Non ci sono categorie!</div></li>";
	else {
	foreach($categorie as $categoria) {
		// usare un pizzico di jquery per risolvere il problema del link e freccetta gialla
		$array_base['cat'] = $categoria['Titolo'];
		unset($array_base['subcat']);
		$url = htmlentities($_SERVER['PHP_SELF']) . '?' . http_build_query($array_base);

		echo "<li><div><a title=\"Filtra per categoria ".$categoria['Titolo']."\" href=\"$url\">".$categoria['Titolo']."</a><span title=\"Visualizza le sottocategorie\" class=\"click_subcategory\">▼</span></div>\n";

		$sottocategorie = Categorie::get_categories($categoria['Titolo']);
		if(!empty($sottocategorie)) {
			echo "\n<ul>";
			foreach($sottocategorie as $sottocategoria) {
				$array_base['subcat'] = $sottocategoria['Titolo'];
				$url = htmlentities($_SERVER['PHP_SELF']) . '?' . http_build_query($array_base);
				echo "<li><a href=\"$url\">".$sottocategoria['Titolo']."</a></li>";
			}
			echo '</ul>';
		}
		echo "</li>";
	}
	?><li><div></div></li> <?php } ?>
	</ul>
	<ul id="menu_filtro">
		<li>
			<?php
			parse_str($args, $array_base);
			$array_senza_filtro = $array_base;
			$array_without_cat = $array_base;
			$array_without_subcat = $array_base;
			unset($array_without_cat['cat']);
			unset($array_without_subcat['subcat']);
			unset($array_senza_filtro['filter_by']);
			if(!empty($array_without_cat))
				$url_without_cat = htmlentities($_SERVER['PHP_SELF'] . '?' . http_build_query($array_without_cat));
			else 
				$url_without_cat = htmlentities($_SERVER['PHP_SELF']);

			if(!empty($array_without_subcat))
				$url_without_subcat = htmlentities($_SERVER['PHP_SELF'] . '?' . http_build_query($array_without_subcat));
			else 
				$url_without_subcat = htmlentities($_SERVER['PHP_SELF']);

			if(!empty($array_senza_filtro))
				$url_without_filters = htmlentities($_SERVER['PHP_SELF'] . '?' . http_build_query($array_senza_filtro));
			else 
				$url_without_filters = htmlentities($_SERVER['PHP_SELF']);

			if(isset($_GET['filter_by']) and $_GET['filter_by'] == 'offers') 
				echo "<a title=\"Rimuovi filtro\" href=\"$url_without_filters\">Filtra per offerte X</a>";
			else {
				$array_base['filter_by']="offers";
				$array_url_offerte = $array_base;
				$url_offerte = htmlentities($_SERVER['PHP_SELF'] . '?' . http_build_query($array_url_offerte));
				echo "<a title=\"Filtra per offerte\" href=\"$url_offerte\">Filtra per offerte</a>";
			}
			?>
		</li>
		<li>
			<?php
			if(isset($_GET['filter_by']) and $_GET['filter_by'] == 'requests') 
				echo "<a title=\"Rimuovi filtro\" href=\"$url_without_filters\">Filtra per richieste X</a>";
			else {
				$array_base['filter_by']="requests";
				$array_url_richieste = $array_base;
				$url_richieste = htmlentities($_SERVER['PHP_SELF'] . '?' . http_build_query($array_url_richieste));
				echo "<a title=\"Filtra per offerte\" href=\"$url_richieste\">Filtra per richieste</a>";
			}
			?>
		</li>
	</ul>
</aside>

<div id="main-content">
