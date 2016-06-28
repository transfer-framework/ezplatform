<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Values\Mapper;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use Transfer\EzPlatform\Repository\Values\LanguageObject;

/**
 * Language mapper.
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class LanguageMapper
{
    /**
     * @var LanguageObject
     */
    public $languageObject;

    /**
     * @param LanguageObject $languageObject
     */
    public function __construct(LanguageObject $languageObject)
    {
        $this->languageObject = $languageObject;
    }

    /**
     * @param Language $language
     */
    public function languageToObject(Language $language)
    {
        $this->languageObject->data['code'] = $language->languageCode;
        $this->languageObject->data['name'] = $language->name;
        $this->languageObject->data['enabled'] = $language->enabled;

        $this->languageObject->setProperty('id', $language->id);
    }

    /**
     * @param LanguageCreateStruct $createStruct
     */
    public function mapObjectToCreateStruct(LanguageCreateStruct $createStruct)
    {
        // Name collection (ez => transfer)
        $keys = array(
            'enabled' => 'enabled',
            'languageCode' => 'code',
            'name' => 'name',
        );

        $this->arrayToStruct($createStruct, $keys);

        $this->callStruct($createStruct);
    }

    /**
     * @param LanguageCreateStruct $struct
     * @param array                $keys
     */
    private function arrayToStruct($struct, $keys)
    {
        foreach ($keys as $ezKey => $transferKey) {
            if (isset($this->languageObject->data[$transferKey])) {
                $struct->$ezKey = $this->languageObject->data[$transferKey];
            }
        }
    }

    /**
     * @param LanguageCreateStruct $struct
     */
    private function callStruct($struct)
    {
        if ($this->languageObject->getProperty('struct_callback')) {
            $callback = $this->languageObject->getProperty('struct_callback');
            $callback($struct);
        }
    }
}
