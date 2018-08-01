<?php

namespace FightTheIce\Domain;

use LayerShifter\TLDExtract\Extract;
use TrueBV\Punycode;
use Webmozart\Assert\Assert;

class Parser
{
    /**
     * url
     * The url object
     *
     * @access protected
     * @var string
     */
    protected $url = '';

    /**
     * subdomain
     * The subdomain that was extracted from
     * the url object
     *
     * @access protected
     * @var string
     */
    protected $subdomain = '';

    /**
     * domain
     * The domain that was extracted from
     * the url object
     *
     * @access protected
     * @var string
     */
    protected $domain = '';

    /**
     * gld
     * (Generic level domain) GLD that was
     * extracted from the url object
     *
     * @access protected
     * @var string
     */
    protected $gld = '';

    /**
     * __construct
     * Class construct
     *
     * @access public
     * @param string $url [description]
     */
    public function __construct($url = '')
    {
        //if the url parameter is not empty lets try
        //parsing it
        if (!empty($this->url)) {
            $this->parse($url);
        }
    }

    /**
     * parse
     * Parse the url object
     *
     * @access public
     * @param  string $url [description]
     * @return
     */
    public function parse($url)
    {
        Assert::string($url);

        //lets "normalize" the url a bit
        $url = strtolower($url);
        $url = ltrim($url, '/');
        $url = rtrim($url, '/');
        $url = trim($url);

        //check punycode
        $puny       = new Punycode();
        $punyString = $puny->encode($url);

        //if encoding the url changed it lets update our url
        if ($url != $punyString) {
            $url = $punyString;
        }

        //now lets try parsing the url object using php's parse_url
        $parseUrl = parse_url($url);

        //if parse_url returned false something is really wrong
        //so lets throw an exception
        if ($parseUrl == false) {
            throw new \ErrorException('PHP\'s parse_url thinks your url is seriously malformed. [E-1]');
        }

        //if we don't have a host this is typically caused by not having a defined schema
        //so lets try adding a default schema of "https" and trying to reparse
        if (!isset($parseUrl['host'])) {
            $url = 'https://' . $url;
            //now lets try parsing the url object using php's parse_url
            $parseUrl = parse_url($url);

            //if parse_url returned false something is really wrong
            //so lets throw an exception
            if ($parseUrl == false) {
                throw new \ErrorException('PHP\'s parse_url thinks your url is seriously malformed. [E-2]');
            }
        }

        $tldData = (new Extract(null, null, Extract::MODE_ALLOW_ICCAN))->parse($parseUrl['host']);

        //if we have no tldData throw an exception
        if (!$tldData) {
            throw new \ErrorException('Unable to parse TLD Data. [E-3]');
        }

        //if the parser says this isn't a valid domain lets throw an exception
        if ($tldData->isValidDomain() == false) {
            throw new \ErrorException('The domain is not valid. [E-4]');
        }

        //if the "domain" is actually an IP lets throw an exception
        if ($tldData->isIp() == true) {
            throw new \ErrorException('The "domain" is unparsable as it is an I.P. address. [E-5]');
        }

        //set a bunch of our data
        $this->subdomain = $tldData->getSubdomain();
        $this->domain    = $tldData->getHostname();
        $this->gld       = $tldData->getSuffix();
        $this->url       = $url;

        return $this;
    }

    /**
     * getSubdomain
     * Returns the parsed subdomain from the url object
     *
     * @access public
     * @return string
     */
    public function getSubdomain()
    {
        return $this->subdomain;
    }

    /**
     * getDomain
     * Returns the parsed domain from the url object
     *
     * @access public
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * getDomainName
     * Alias of getDomain
     * @return string
     */
    public function getDomainName()
    {
        return $this->getDomain();
    }

    /**
     * getGld
     * (Generic level domain)
     * Returns the parsed gld from the url object
     *
     * @access public
     * @return string
     */
    public function getGld()
    {
        return $this->gld;
    }

    /**
     * getUrl
     * Returns the url object
     *
     * @access public
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
