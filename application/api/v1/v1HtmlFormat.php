<?php
require_once '../../library/restler/restler.php';

/**
 * Javascript Object Notation Format
 * @category   Framework
 * @package    restler
 * @subpackage format
 * @author     R.Arul Kumaran <arul@luracast.com>
 * @copyright  2010 Luracast
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://luracast.com/products/restler/
 */
class V1HtmlFormat implements iFormat
{
	const MIME ='text/html';
	const EXTENSION = 'html';
	public function getMIMEMap(){
		return array(V1HtmlFormat::EXTENSION=>V1HtmlFormat::MIME);
	}
	public function getMIME(){
		return V1HtmlFormat::MIME;
	}
	public function getExtension(){
		return V1HtmlFormat::EXTENSION;
	}
	public function setMIME($mime){
		//do nothing
	}
	public function setExtension($extension){
		//do nothing
	}
	public function encode($data, $human_readable=TRUE){
		#$html = "<html><head>";
		$html .= '<link rel="stylesheet" type="text/css" href="http://total-impact.org/ui/totalimpact.css" />';
		#$html .= '</head><body>';
		$html .= '<div id="report"><ul>';
		foreach ($data->items as $item) {
			$html .= "<li class='artifact'>";
			if (isset($item->biblio)) {
				$html .= "<h4>$item->id</h4>"; 
				$biblioString = "";
				foreach ($item->biblio as $biblio) {
					$title = "<span class='title'>$biblio->title</span>";
	                $authors = "<span class='meta-author'>$biblio->authors</span>";
	                $year = "<span class='meta-year'>($biblio->year) </span>";
	                $repo = "<span class='meta-repo'>$biblio->repository.</span>";
	                $url = $biblio->url;				
					$biblioString .= "<li class='biblio'>" . "$authors $year <a class='meta-url' target='_blank' href='$url'> $title</a> $repo <br/>" . "</li>";					
				}
				$html .= "<ul class='biblio'>" . $biblioString . "</ul>";
			} 
			if (isset($item->metrics)) {
				$metricString = "";
				foreach ($item->metrics as $metric) {
					$displayName = $metric->meta->display_name;
					$icon = $metric->meta->icon;
					$tooltiptext = $metric->meta->tooltiptext;
					$metricString .= "<li title='$tooltiptext'>";
					$metricString .= "<a target='_blank' href='$metric->drilldown_url'><span class='metric-value'>$metric->value</span><span class='metric-img-name'><img class='metric-image' src='$icon' width='16'' height='16' border=0 alt='' /><span class='metric-name'>$displayName</span></span></a>";
					$metricString .= "</li>";
				}
				$html .= "<ul class='metrics'>" . $metricString . "</ul>";
			}
		}
		$html .= "</ul></div>";			
		#$html .= "</body><html>";
		return $html;
	}
	public function decode($data){
		return object_to_array(json_decode($data));
	}

	public function __toString(){
		return $this->getExtension();
	}
}