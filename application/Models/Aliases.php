<?php

class Models_Aliases {
    private $aliases = array();
    private $preferredNamespace;

    function __construct(Array $aliases=NULL, $preferredNamespace="totalimpact") {
        if (is_array($aliases)) {
            $this->addAliases($aliases);
        }
        else {
            $this->aliases = array();
        }
        $this->preferredNamespace = $preferredNamespace;
    }

    public function setPreferredNamespace($preferredNamespace) {
        $this->preferredNamespace = $preferredNamespace;
    }
    public function getPreferredNamespace() {
        return $this->preferredNamespace;
    }

    public function addAlias($namespace, $id) {
        $this->aliases[$namespace] = $id;
    }
    public function getAliases() {
        return $this->aliases;
    }
    public function clearAliases() {
        
    }

    /**
     * Adds aliases from an array
     *
     * @param array $aliases An array of associative arrays, each of form array(namespace => id)
     * @return int number aliases added, on success
     */
    public function addAliases($aliases) {
        if (!is_array($aliases) ) {
            throw new Exception("addAliases requires an array.");
        }
        foreach ($aliases as $namespace => $id){
            if (!is_string($namespace)) {
                throw new Exception("array must be associative: array(namespace => id)");
            }
            $this->addAlias($namespace, $id);
        }
        return count($aliases);
    }

    /**
     * gets the id that goes with a certain namespace, if it exists.
     *
     * @param string $namespace
     * @return bool|string the id for that namespace, if there is one; otherwise, false.
     */
    public function getId($namespace) {
        if (isset($this->aliases[$namespace])) {
            return $this->aliases[$namespace];
        }
        else {
            return false;
        }
    }

    /**
     * Tries to get the id from
     *  1. the preferred namespace (as held in this->preferredNamespace
     *  2. the totalimpact namespace
     *  3. whatever's namespace is first in the aliases array
     * @param bool $returnIndexedArray true to output result as array(0 => $namespace, 1 => $id);
     * @return array namespace as array($namespace => $id)
     */
    public function getBestAlias($returnIndexedArray = false) {
        $ret = array();
        if (!count($this->aliases)) {
            throw new Exception("There are no aliases to get!");
        }
        else if ($this->getId($this->preferredNamespace)) {
            $ret = array(
                $this->preferredNamespace => $this->getId($this->preferredNamespace)
                        );
        }
        else if ($this->getId("totalimpact")) {
            $ret = array(
                "totalimpact" => $this->getId("totalimpact")
                    );
        }
        else {
            $ret = array( 
                key($this->aliases) => reset($this->aliases)
                        );
        }

        if ($returnIndexedArray) {
            $keys = array_keys($ret);
            $vals = array_values($ret);
            $ret = array($keys[0], $vals[0]);
        }

        return $ret;
    }


}
?>
