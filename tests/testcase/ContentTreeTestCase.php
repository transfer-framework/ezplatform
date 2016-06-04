<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\tests\testcase;

use Transfer\Data\TreeObject;

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
