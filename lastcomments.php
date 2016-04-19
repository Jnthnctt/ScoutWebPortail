<?php
/*
* 12/12/2005 - 22:40:00 - Scout Web Portail - v 1.1.1
*
* lastcomments.php v 1.1 - Derniers commentaires des photos dans la galerie
* Copyright (C) 2005 ChMat
* http://www.scoutwebportail.org
*
* This file is part of Scout Web Portail.
*
* Scout Web Portail is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* Scout Web Portail is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Scout Web Portail; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA.
*/
/* 
* Modifications v 1.1
*	Utilisation du 3e param�tre de getimagesize
*/

include_once('connex.php');
include_once('fonc.php');
if (!defined('IN_SITE'))
{
?>
<?php echo '<'.'?xml version="1.0" encoding="iso-8859-1"?'.'>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<title>Derniers commentaires de la galerie photo</title>
<link rel="stylesheet" href="templates/default/style.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body bgcolor="#FFFFFF">
<?php
}
?>
<h1>Les derniers commentaires de la galerie photo</h1>
<p align="center"><a href="<?php echo ($site['url_rewriting_actif'] == 1) ? 'galerie.htm' : 'index.php?page=galerie'; ?>">Retour &agrave; la Galerie Photo</a></p>
<div id="commentaires">
<?php
	$sql = "SELECT count(*) as nbre FROM ".PREFIXE_TABLES."commentaires WHERE commentairebanni != '1'";
	if ($res = send_sql($db, $sql))
	{
		$x = mysql_fetch_assoc($res);
		$nbre_commentaires = $x['nbre'];
		$par = 10;
		if ($nbre_commentaires > $par)
		{
			$nbre_pages_commentaires = floor($nbre_commentaires / $par);
			if ($nbre_pages_commentaires * $par < $nbre_commentaires) $nbre_pages_commentaires++;
		}
		else
		{
			$nbre_pages_commentaires = 1;
		}
	}
	$pg = $_GET['pg'];
	if (isset($pg) and $pg < 1) {$pg = 1;} else if (isset($pg) and $pg > $nbre_pages_commentaires) {$pg = $nbre_pages_commentaires;}
	if (!isset($pg)) {$debut = 0; $pg= 1;} // page en cours
	else {$debut = $par * ($pg-1);}

	// Affichage des commentaires de la photo en cours
	$sql = "SELECT c.auteur, x.pseudo, g.titre, a.posphoto, g.galerie_section, a.numalbum, a.nomfichier, c.numcommentaire as numcom, c.commentaire as txtcom, c.datecreation as datecom, g.dossierpt FROM ".PREFIXE_TABLES."commentaires as c, ".PREFIXE_TABLES."albums as a, ".PREFIXE_TABLES."galerie as g, ".PREFIXE_TABLES."auteurs as x WHERE c.auteur = x.num AND c.album = a.numalbum AND c.album = g.numgalerie AND c.photo = a.posphoto AND commentairebanni != '1' ORDER BY datecom DESC LIMIT $debut, $par";
	$commentaires = send_sql($db, $sql);
	if (mysql_num_rows($commentaires) > 0)
	{
		if ($nbre_pages_commentaires > 1)
		{
			echo '<p align="center">';
			if ($pg > 1)
			{
				$pgpcdte = $pg - 1;
				$lien_commentaires_pcdte = ($site['url_rewriting_actif'] == 1) ? $niv.'_lastcomments_'.$pgpcdte.'.htm' : 'index.php?niv='.$niv.'&amp;page=lastcomments&amp;pg='.$pgpcdte;
				echo '<a href="'.$lien_commentaires_pcdte.'">Commentaires plus r&eacute;cents</a> - ';
			}
			echo 'Page '.$pg.' de '.$nbre_pages_commentaires;
			if ($pg < $nbre_pages_commentaires)
			{
				$pgsvte = $pg + 1;
				$lien_commentaires_svte = ($site['url_rewriting_actif'] == 1) ? $niv.'_lastcomments_'.$pgsvte.'.htm' : 'index.php?niv='.$niv.'&amp;page=lastcomments&amp;pg='.$pgsvte;
				echo ' - <a href="'.$lien_commentaires_svte.'">Commentaires plus anciens</a>';
			}
			echo '</p>';
		}
		$position = 1;
		while ($comment = mysql_fetch_assoc($commentaires))
		{
 			$ref = $comment['dossierpt'].$comment['nomfichier'];
			$taille = @getimagesize($ref);
			$taille = $taille[3];
			$niv_dest = ($comment['galerie_section'] != 0) ? $sections[$comment['galerie_section']]['site_section'] : 'g';
			$lien_commentaire = ($site['url_rewriting_actif'] == 1) ? $niv_dest.'_galerie_'.$comment['numalbum'].'_'.$comment['posphoto'].'.htm' : 'index.php?niv='.$niv_dest.'&amp;page=galerie&amp;show='.$comment['numalbum'].'&amp;photo='.$comment['posphoto'];
?>
<div class="commentaire">
<h2><?php echo $comment['titre'].' - photo '.$comment['posphoto']; ?></h2>
<p><?php echo '<a href="'.$lien_commentaire.'"><img src="'.$comment['dossierpt'].$comment['nomfichier'].'" class="photo" '.$taille.' border="0" />'; ?>
<span class="auteur"><a href="<?php echo ($site['url_rewriting_actif'] == 1) ? 'membre'.$comment['auteur'].'.htm' : 'index.php?page=profil_user&amp;user='.$comment['auteur']; ?>" target="_blank" title="Voir son profil" class="lienmort"><?php echo $comment['pseudo']; ?></a>
<?php
			if ($user['niveau']['numniveau'] > 2)
			{
?>
 <a href="index.php?page=commentpost&amp;do=mod&amp;n=<?php echo $comment['numcom']; ?>&amp;r=<?php echo urlencode('index.php?'.$_SERVER['QUERY_STRING']); ?>" title="Supprimer ce commentaire"><img src="templates/default/images/moins.png" alt="Supprimer" border="0" /></a> 
<?php
			}
?>
</span> 
<br />
<?php echo makehtml($comment['txtcom']); ?>
</p>
<p class="date">Ajout&eacute; le <?php echo date_ymd_dmy($comment['datecom'], 'dateheure'); ?></p>
</div>
<?php
		}
	}
	// fin affichage des commentaires
	else
	{
?>
<div class="msg">
<p align="center" class="rmq">Il n'y a encore aucun commentaire de photo</p>
</div>
<?php
	}
?>
</div>
<?php
if (!defined('IN_SITE'))
{
?>
</body>
</html>
<?php
}
?>