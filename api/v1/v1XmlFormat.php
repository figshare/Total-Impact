<?php
require_once '../../library/restler/restler.php';
require_once '../../library/restler/xmlformat.php';

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
class V1XmlFormat extends XmlFormat
{

	public function encode($data, $human_readable=FALSE){
#		return json_encode(object_to_array($data));
		#return "<boo>" . parent::encode($data, $human_readable) . "</boo>";
		$data->hi = "heather";
		return parent::encode($data, $human_readable);
	}
}