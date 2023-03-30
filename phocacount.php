<?php 
/**
 * @version		2.0.0
 * @package		PhocaCount content plugin
 * @author		ConseilGouz
 * @copyright	Copyright (C) 2023 ConseilGouz. All rights reserved.
 * @license		GNU/GPL v2; see LICENSE.php
 **/
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
class plgContentPhocaCount extends CMSPlugin
{	
	public function onContentPrepare($context, &$article, &$params, $page = 0) {
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer') {
			return true;
		}
		$output = '';
		$regex_one		= '/({phocacount\s*)(.*?)(})/si';
		$regex_all		= '/{phocacount\s*.*?}/si';
		$matches 		= array();
		$count_matches	= preg_match_all($regex_all,$article->text,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
		if ($count_matches == 0) {
			return true;
		}
		for($i = 0; $i < $count_matches; $i++) {
			$phocacount	= $matches[0][$i][0];
			preg_match($regex_one,$phocacount,$phocacount_parts);
			$parts			= explode("|", $phocacount_parts[2]);
			$values_replace = array ("/^'/", "/'$/", "/^&#39;/", "/&#39;$/", "/<br \/>/");
			$values = explode("=", $phocacount_parts[2], 2);
			$id				= $values[1];
			$db 			= Factory::getDBO();
			if (strpos($id,',')) { // multiple ids
			    $query = 'SELECT SUM(a.hits)'
			        . ' FROM #__phocadownload AS a';
			        $query .= ' WHERE a.id IN ('.$id.')';
			} else {
			    $query = 'SELECT a.hits'
					. ' FROM #__phocadownload AS a';
			     $query .= ' WHERE a.id = '.(int)$id;
			}
			$db->setQuery($query);
			$item = $db->loadResult();
			if (!empty($item)) {
				$output = '';
				$output .= '<span class="phocacount">';
				$output .= $item;
				$output .= '</span>';
				$article->text = preg_replace($regex_all, $output, $article->text, 1);
			}
		}
		return true;
	}
}
?>