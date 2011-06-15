<?php
/**
 * Makes a CollectionInput object
 *
 * @author jason
 */
class Models_CollectionInputFactory {
    public static function make(){
        $config = new Zend_Config_Ini(APP_PATH . '/config/app.ini', "production");
        $couch = new Couch_Client($config->db->dsn, $config->db->name);
        return new CollectionInput($couch);
    }
}
?>
