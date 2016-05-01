<?php

namespace Transfer\EzPlatform\tests\testcase;

use Psr\Log\LoggerInterface;
use Transfer\Data\TreeObject;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Repository\Values\ContentObject;

class ContentTreeTestCase extends ContentTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Creates a TreeObject skeleton.
     *
     * @param int   $locationId
     * @param array $data
     *
     * @return TreeObject
     */
    protected function getTreeObject($locationId, $data)
    {
        $tree = new TreeObject($data);
        $tree->setProperty('parent_location_id', $locationId);

        return $tree;
    }
}