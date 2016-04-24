<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Mapper;

use eZ\Publish\API\Repository\Values\Content\Language;
use Transfer\EzPlatform\Data\LanguageObject;

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

    public function languageToObject(Language $language)
    {
        $this->languageObject->data['code'] = $language->languageCode;
        $this->languageObject->data['name'] = $language->name;
        $this->languageObject->data['enabled'] = $language->enabled;

        $this->languageObject->setProperty('id', $language->id);
    }
}
