<?php

namespace TorneLIB\IO\Data;

use TorneLIB\Exception\Constants;
use TorneLIB\Exception\ExceptionHandler;
use TorneLIB\TORNELIB_CRYPTO_TYPES;
use TorneLIB\Utils\Security;

/**
 * Class Content
 * @package TorneLIB\IO\Data
 * @version 6.1.0
 */
class Content
{
    private $serializer;
    private $unserializer;
    private $simpleElement;

    /**
     * Special options array to use with XML requests.
     * @var array
     */
    private $xmlOptions = [
        'indent' => '    ',
        'linebreak' => "\n",
        'encoding' => 'UTF-8',
        'rootName' => null,
        'defaultTagName' => null,
    ];

    private $libXmlInternalErrorsSuppressed = true;

    public function __construct()
    {
        $this->getAvailableSerializers();
        return $this;
    }

    /**
     * @throws ExceptionHandler
     * @since 6.1.0
     */
    private function getAvailableSerializers()
    {
        $this->unserializer = Security::getCurrentStreamPath('XML/Unserializer');
        $this->serializer = Security::getCurrentStreamPath('XML/Serializer');
        $this->simpleElement = Security::getCurrentClassState('SimpleXMLElement', false);

        // Do not throw at this moment.
        if (Security::getCurrentFunctionState('libxml_use_internal_errors', false)) {
            $this->libXmlInternalErrorsSuppressed = true;
            libxml_use_internal_errors(false);
        }
    }

    /**
     * @return array
     * @since 6.1.0
     */
    public function getXmlOptions()
    {
        return $this->xmlOptions;
    }

    /**
     * @param string $dataIn
     * @return mixed|string
     * @since 6.0.5
     */
    public function getFromJson($dataIn = '')
    {
        if (is_string($dataIn)) {
            return @json_decode($dataIn);
        } elseif (is_object($dataIn)) {
            return null;
        } elseif (is_array($dataIn)) {
            return null;
        } else {
            // Fail.
            return null;
        }
    }

    /**
     * @param $data
     * @param bool $normalize
     * @return array
     * @throws ExceptionHandler
     * @since 6.0.5
     */
    public function getFromXml($data, $normalize = true)
    {
        $return = [];

        if ($this->simpleElement) {
            $return = $this->getFromSimpleXml($data, $normalize);
        }

        return $return;
    }

    /**
     * @param $data
     * @return string
     * @since 6.1.0
     */
    private function validateXml($data)
    {
        $return = '';

        if (is_string($data) && preg_match("/\<(.*?)\>/s", $data)) {
            $return = $data;
        } else {
            if (!preg_match("/^\</", $data) && preg_match("/&\b(.*?)+;(.*)/is", $data)) {
                $dataEntity = trim(html_entity_decode($data));
                if (preg_match("/^\</", $dataEntity)) {
                    $return = $dataEntity;
                }
            }
        }

        return $return;
    }

    /**
     * Set options for xml input.
     *
     * @param $options
     * @param null $optionsValue
     * @return $this
     */
    public function setXmlOptions($options, $optionsValue = null)
    {
        if (is_array($options)) {
            foreach ($options as $optionKey => $optionValue) {
                $this->xmlOptions[$optionKey] = $optionValue;
            }
        } elseif (!is_null($optionsValue)) {
            $this->xmlOptions[$options] = $optionsValue;
        }

        return $this;
    }

    /**
     * @param $data
     * @param bool $normalize
     * @return object|null
     * @throws ExceptionHandler
     * @since 6.1.0
     */
    public function getFromSimpleXml($data, $normalize = true)
    {
        $return = '';
        $data = $this->validateXml($data);

        // Assume this is proper content.
        if (!empty($data)) {
            // LIBXML_NOCDATA = 16384 - merge CDATA as text nodes.
            $simpleXmlElement = new \SimpleXMLElement($data, 16384);

            if ($normalize) {
                $return = (new Arrays())->arrayObjectToStdClass($simpleXmlElement);
            }
        }

        return $return;
    }

    /**
     * @param $data
     * @param \SimpleXMLElement $xml
     * @return \SimpleXMLElement
     * @since 6.1.0
     */
    private function getXmlTransformed($data, $xml)
    {
        foreach ($data as $key => $value) {
            $key = is_numeric($key) ? 'item' : $key;
            if (is_array($value)) {
                $this->getXmlTransformed($value, $xml->addChild($key));
            } else {
                $xml->addChild($key, $value);
            }
        }

        return $xml;
    }

    /**
     * @param $data
     * @param string $rootName
     * @param string $initalTagName
     * @param bool $toUtf8
     * @return mixed
     */
    public function getXmlFromArray($data, $rootName = 'XMLResponse', $initalTagName = 'item', $toUtf8 = true)
    {
        if ($toUtf8) {
            $data = (new Strings())->getUtf8($data);
        }

        $xml = new \SimpleXMLElement('<?xml version="1.0"?>' . '<' . $rootName . '></' . $rootName . '>');
        return $this->getXmlTransformed(
            $data,
            $xml
        )->asXML();
    }

    /**
     * @param array $contentData
     * @param null $renderAndDie
     * @param null $compression
     * @param string $initialTagName
     * @param string $rootName
     * @return mixed
     * @since 6.0.1
     * @deprecated From 6.0, use 6.1 getXmlFromArray instead.
     */
    public function renderXml(
        $contentData = [],
        $renderAndDie = null,
        $compression = null,
        $initialTagName = 'item',
        $rootName = 'XMLResponse'
    ) {
        return $this->getXmlFromArray($data);
    }

    public function __call($name, $arguments)
    {
        if ($name === 'array_to_xml') {
            return call_user_func_array(
                [
                    $this,
                    'renderXml',
                ],
                $arguments
            );
        }

        throw new ExceptionHandler(
            sprintf(
                'There is no method named "%s" in class %s',
                $name,
                __CLASS__
            ),
            Constants::LIB_METHOD_OR_LIBRARY_UNAVAILABLE
        );
    }

}