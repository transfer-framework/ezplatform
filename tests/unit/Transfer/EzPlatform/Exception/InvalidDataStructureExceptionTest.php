<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
use Transfer\EzPlatform\Worker\Transformer\ArrayToEzPlatformContentTypeObjectTransformer;

class InvalidDataStructureExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $array = array('_no_fields' => array(
            'fields' => array(),
        ));
        $transformer = new ArrayToEzPlatformContentTypeObjectTransformer();

        /*
         * @todo Reimplement exception
         */
        //$this->setExpectedException('Transfer\EzPlatform\Exception\InvalidDataStructureException');
        //$transformer->handle($array);
    }
}
