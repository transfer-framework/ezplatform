<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Data;

use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Exception\LanguageNotFoundException;

/*

** Available keys: **

    $data = [
        code => string
        name => string
    ],
    $properties = [
        <none>
    ]


** Required on `create`:
**** Required by transfer:
    code

**** Required by eZ:
    code
    name

** Required on `update`:
**** Required by transfer:
    code
    name

**** Required by eZ:
    code
    name

*/

/**
 * Content type object.
 */
class LanguageObject extends ValueObject
{
    /**
     * Because a name is required.
     *
     * @var array
     */
    protected $defaultNameMapping = array(
        'ara-SA' => 'Arabic',
        'cat-ES' => 'Catalan',
        'chi-CN' => 'Simplified Chinese',
        'chi-HK' => 'Traditional Chinese (HongKong)',
        'chi-TW' => 'Traditional Chinese (Taiwan)',
        'cro-HR' => 'Croatian (Hrvatski)',
        'cze-CZ' => 'Czech',
        'dan-DK' => 'Danish',
        'dut-NL' => 'Dutch',
        'ell-GR' => 'Greek (Hellenic)',
        'eng-AU' => 'English (Australia)',
        'eng-CA' => 'English (Canada)',
        'eng-GB' => 'English (United Kingdom)',
        'eng-NZ' => 'English (New Zealand)',
        'eng-US' => 'English (American)',
        'epo-EO' => 'Esperanto',
        'esl-ES' => 'Spanish (Spain)',
        'esl-MX' => 'Spanish (Mexico)',
        'fin-FI' => 'Finnish',
        'fre-BE' => 'French (Belgium)',
        'fre-CA' => 'French (Canada)',
        'fre-FR' => 'French (France)',
        'ger-DE' => 'German',
        'heb-IL' => 'Hebrew',
        'hin-IN' => 'Hindi (India)',
        'hun-HU' => 'Hungarian',
        'ind-ID' => 'Indonesian',
        'ita-IT' => 'Italian',
        'jpn-JP' => 'Japanese',
        'kor-KR' => 'Korean',
        'nno-NO' => 'Norwegian (Nynorsk)',
        'nor-NO' => 'Norwegian (Bokmal)',
        'pol-PL' => 'Polish',
        'por-BR' => 'Portuguese (Brazil)',
        'por-MZ' => 'Portuguese (Mozambique)',
        'por-PT' => 'Portuguese (Portugal)',
        'rus-RU' => 'Russian',
        'ser-SR' => 'Serbian (Srpski)',
        'slk-SK' => 'Slovak',
        'srp-RS' => 'Serbian (Српски)',
        'swe-SE' => 'Swedish',
        'tur-TR' => 'Turkish',
        'ukr-UA' => 'Ukrainian',
    );

    /**
     * LanguageObject constructor.
     *
     * @param array $data
     *
     * @throws LanguageNotFoundException
     */
    public function __construct($data)
    {
        parent::__construct($data);
        if (!isset($this->data['name'])) {
            $this->data['name'] = $this->getDefaultName($this->data['code']);
        }
    }

    /**
     * @param string $code
     *
     * @return string
     *
     * @throws LanguageNotFoundException
     */
    public function getDefaultName($code)
    {
        if (!array_key_exists($code, $this->defaultNameMapping)) {
            throw new LanguageNotFoundException(sprintf('Default language name for code "%s" not found.', $code));
        }

        return $this->defaultNameMapping[$code];
    }
}
