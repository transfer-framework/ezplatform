<?php

use Transfer\EzPlatform\Worker\Transformer\ArrayToEzPlatformContentTypeObjectTransformer;

class InvalidDataStructureExceptionTest extends \PHPUnit_Framework_TestCase
{

    public function testException()
    {
        $array = array('_no_fields' => array(
            'fields' => array()
        ));
        $transformer = new ArrayToEzPlatformContentTypeObjectTransformer();
        $this->setExpectedException('Transfer\EzPlatform\Exception\InvalidDataStructureException');
        $transformer->handle($array);
    }
}