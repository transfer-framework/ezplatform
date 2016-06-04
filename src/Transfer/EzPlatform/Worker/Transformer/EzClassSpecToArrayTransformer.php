<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\Worker\Transformer;

use Transfer\Worker\WorkerInterface;

/**
 * Transforms array to Transfer eZPlatform ContentType object.
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class EzClassSpecToArrayTransformer implements WorkerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle($ezclasspec)
    {
        $ezclasspec = json_decode($ezclasspec, true);

        $array = [];
        foreach ($ezclasspec['class_list'] as $class) {
            $array[$class['class_identifier']] = $this->mapContentType($class);
        }

        return $array;
    }

    protected function mapContentType($class)
    {
        $contentType = array();
        foreach ($class as $key => $value) {
            switch ($key) {
                case 'class_name':
                    $contentType['name'] = $this->uctToUtf($value);
                    break;

                case 'class_group':
                    $contentType['contenttype_groups'] = array($value);
                    break;

                case 'attribute_list':
                    foreach ($value as $attribute) {
                        $contentType['fields'][$attribute['identifier']] = $this->mapField($attribute);
                    }
                    break;
            }
        }

        return $contentType;
    }

    /**
     * @param array $attibute
     *
     * @return array
     */
    protected function mapField($attibute)
    {
        $field = array();
        foreach ($attibute as $key => $value) {
            switch ($key) {
                case 'name':
                    $field['name'] = $this->uctToUtf($value);
                    break;
                case 'datatype':
                    $field['type'] = $value;
                    break;
                case 'desc':
                    $field['description'] = $this->uctToUtf($value);
                    break;
                case 'required':
                    $field['is_required'] = (bool) $value;
                    break;
            }
        }

        return $field;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function uctToUtf($string)
    {
        return html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", '&#x\\1;', $string), ENT_NOQUOTES, 'UTF-8');
    }
}
