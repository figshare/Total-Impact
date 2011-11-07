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
	public function encode($data, $human_readable=FALSE){
		return "HTMLified" . json_encode(object_to_array($data));
	}
	public function decode($data){
		return object_to_array(json_decode($data));
	}

	public function __toString(){
		return $this->getExtension();
	}
}