<?php
namespace PHPCR\Tests\Versioning;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * test some more NodeInterface::isNodeType (read) §8.6 things that can only
 * be done if there is mixin node types that inherit from another mixin.
 *
 * the only case for that in the default types is with mix:versionable
 */
class NodeNodeTypeReadMethodsTest extends \PHPCR\Test\BaseCase
{
    protected $versioned;
    protected $simpleVersioned;

    static public function setupBeforeClass($fixtures = '15_Versioning/base')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        parent::setUp();

        $this->versioned = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');
        $this->simpleVersioned = $this->sharedFixture['session']->getNode('/tests_version_base/simpleVersioned');
    }

    public function testIsMixin()
    {
        $this->assertTrue($this->versioned->isNodeType('mix:versionable'));
        $this->assertTrue($this->versioned->isNodeType('mix:simpleVersionable'));
        $this->assertFalse($this->simpleVersioned->isNodeType('mix:versionable'));
        $this->assertTrue($this->simpleVersioned->isNodeType('mix:simpleVersionable'));
    }

    public function testIsParentMixin()
    {
        $this->assertTrue($this->versioned->isNodeType('mix:referenceable'));
        $this->assertFalse($this->simpleVersioned->isNodeType('mix:referenceable'));
    }

    public function testIsNotMixin()
    {
        $this->assertFalse($this->versioned->isNodeType('mix:language'));
    }

}
