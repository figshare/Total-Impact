<?php

class Models_AliasesTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Models_Aliases
     */
    protected $obj;
    protected $aliasesArr = array(
        "PubMed" => "0000000001",
         "DOI" => "10.fake/1",
         "totalimpact" => "0001"
        );


    protected function setUp() {
        $this->obj = new Models_Aliases;
    }

    protected function tearDown() {
        $this->obj->clearAliases();
    }



    public function testAddAliasesThrowsExceptionWithStringArg() {
        try {
            $this->obj->addAliases("mynamespace");
        }
        catch (Exception $e) {
            return;
        }
        $this->fail("failed to throw excpected exception");
    }

    public function testAddAliasesThrowsExceptionWithArrayOfIndexedArraysArg() {
        try {
            $this->obj->addAliases(array(
                array("foo", "bar"),
                array("baz", "bam")
                ));
        }
        catch (Exception $e) {
            return;
        }
        $this->fail("failed to throw excpected exception");
    }

    public function testAddAliases() {

        $this->obj->addAliases($this->aliasesArr);
        $this->assertEquals(
                $this->obj->getAliases(),
                $this->aliasesArr
                );
        $aliasesArr2 = array("Dryad" => "18");

        $this->obj->addAliases($aliasesArr2);
        $this->assertEquals(
                $this->obj->getAliases(),
                array_merge($this->aliasesArr, $aliasesArr2)
                );
    }

    public function testgetId() {
        $alias = $this->obj->getId("PubMed");
        $this->assertFalse($alias);

        $this->obj->addAliases($this->aliasesArr);
        $alias = $this->obj->getId("PubMed");
        $this->assertEquals(
                "0000000001",
                $alias
                );
    }

    public function testGetBestAliasThrowsExceptionIfNoAliasesLoaded() {
        try {
            $best = $this->obj->getBestAlias();
        }
        catch (Exception $e){
            return true;
        }
        $this->fail("this should've thrown an exception");
    }

    public function testGetBestAliasGetsPreferredNamespaceDefault() {
        $this->obj->addAliases($this->aliasesArr);
        $this->obj->setPreferredNamespace("DOI");
        $best = $this->obj->getBestAlias();
        $this->assertEquals(
                array("DOI" => "10.fake/1"),
                $best
                );
    }
    public function testGetBestAliasFlattenArgWorks() {
        $this->obj->addAliases($this->aliasesArr);
        $best = $this->obj->getBestAlias(true);
        $this->assertEquals(
                array("totalimpact", "0001"),
                $best
                );
    }
    public function testGetBestAliaGetsFirstAliasIfPreferredIsntThere() {
        $aliasesArr = $this->aliasesArr;
        unset($aliasesArr['totalimpact']);

        $this->obj->addAliases($aliasesArr);
        $best = $this->obj->getBestAlias(true);
        $this->assertEquals(
                array("PubMed", "0000000001"),
                $best
                );
    }

}

?>
