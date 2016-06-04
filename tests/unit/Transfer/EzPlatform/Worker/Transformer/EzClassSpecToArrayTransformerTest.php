<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
use Transfer\EzPlatform\Worker\Transformer\EzClassSpecToArrayTransformer;

class EzClassSpecToArrayTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testFull()
    {
        $json = $this->getDetailedEzClassSpecExample();

        $transformer = new EzClassSpecToArrayTransformer();
        $array = $transformer->handle($json);

        $this->assertArrayHasKey('website', $array);
        $ct0 = $array['website'];
        $this->assertEquals('Setup', $ct0['contenttype_groups'][0]);
        $this->assertEquals('Nettsted', $ct0['name']);

        $this->assertArrayHasKey('name', $ct0['fields']);
        $f0 = $ct0['fields']['name'];
        $this->assertEquals('Navn', $f0['name']);
        $this->assertEquals('ezstring', $f0['type']);
        $this->assertEquals('Navnet på nettstedet', $f0['description']);
        $this->assertTrue($f0['is_required']);

        $this->assertArrayHasKey('breadcrumb_name', $ct0['fields']);
        $f1 = $ct0['fields']['breadcrumb_name'];
        $this->assertEquals('Navn i brødsmulesti', $f1['name']);
        $this->assertEquals('ezstring', $f1['type']);
        $this->assertEquals('', $f1['description']);
        $this->assertTrue($f1['is_required']);

        $this->assertArrayHasKey('logo', $ct0['fields']);
        $f2 = $ct0['fields']['logo'];
        $this->assertEquals('Logo', $f2['name']);
        $this->assertEquals('ezobjectrelation', $f2['type']);
        $this->assertEquals('', $f2['description']);
    }

    protected function getDetailedEzClassSpecExample()
    {
        return
'{
    "site": "AcmeAppBundle",
    "class_list": [
        {
            "class_name": "Nettsted",
            "class_identifier": "website",
            "class_group": "Setup",
            "attribute_list": [
                {
                    "name": "Navn",
                    "identifier": "name",
                    "datatype": "ezstring",
                    "desc": "Navnet p\u00e5 nettstedet",
                    "required": "1"
                },
                {
                    "name": "Navn i br\u00f8dsmulesti",
                    "identifier": "breadcrumb_name",
                    "datatype": "ezstring",
                    "desc": "",
                    "required": "1"
                },
                {
                    "name": "Logo",
                    "identifier": "logo",
                    "datatype": "ezobjectrelation",
                    "desc": ""
                }
            ]
        }
    ]
}';
    }
}
