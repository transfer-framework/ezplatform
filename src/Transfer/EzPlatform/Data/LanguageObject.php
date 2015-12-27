<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Data;

use Transfer\EzPlatform\Exception\LanguageNotFoundException;

/**
 * Content type object.
 */
class LanguageObject
{
    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    protected $name;

    /**
     * LanguageObject constructor.
     *
     * @param string $code
     */
    public function __construct($code)
    {
        $this->code = $code;
        $this->setName();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @throws \Exception
     */
    protected function setName($name = '')
    {
        if (empty($name)) {
            if (isset($this->defaultNameMapping[$this->code])) {
                $name = $this->defaultNameMapping[$this->code];
            } else {
                throw new LanguageNotFoundException(sprintf('Default language name for code "%s" not found.', $this->code));
            }
        }
        $this->name = $name;
    }

    /**
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
}
