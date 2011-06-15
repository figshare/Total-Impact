<?php
/**
 * Makes an updater, loaded with the correct plugin
 *
 * @author jason
 */
class Models_UpdaterFactory {
    static function makeUpdater($sourceName){

        $config = new Zend_Config_Ini(APP_PATH . '/config/app.ini', "production");
        $plugin = new Plugin();
        $couch = new Couch_Client($config->db->dsn, $config->db->name);

        if (isset($config->plugins->$sourceName)) {
            $plugin->setName($sourceName);
            $plugin->setUri($config->plugins->$sourceName);
        }
        else {
            throw new Exception("Source name '$sourceName' given, but it's not in the config file");
        }

        $updater = new Updater($couch, $plugin);
        return $updater;

    }
}
?>
