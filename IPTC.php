<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003-2004 TownNews.com                                 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Patrick O'Lone <polone@townnews.com>                        |
// +----------------------------------------------------------------------+
//
// $Id$

// The following constants can be used directly with this class to access the
// various elements. Note that these values can be used directly, but to make
// code a bit more clear, use the constants instead.

define('IMAGE_IPTC_OBJECT_NAME', 005);
define('IMAGE_IPTC_EDIT_STATUS', 007);
define('IMAGE_IPTC_PRIORITY', 010);
define('IMAGE_IPTC_CATEGORY', 015);
define('IMAGE_IPTC_SUPPLEMENTARY_CATEGORY', 020);
define('IMAGE_IPTC_FIXTURE_IDENTIFIER', 022);
define('IMAGE_IPTC_KEYWORDS', 025);
define('IMAGE_IPTC_RELEASE_DATE', 030);
define('IMAGE_IPTC_RELEASE_TIME', 035);
define('IMAGE_IPTC_SPECIAL_INSTRUCTIONS', 040);
define('IMAGE_IPTC_REFERENCE_SERVICE', 045);
define('IMAGE_IPTC_REFERENCE_DATE', 047);
define('IMAGE_IPTC_REFERENCE_NUMBER', 050);
define('IMAGE_IPTC_CREATED_DATE', 055);
define('IMAGE_IPTC_CREATED_TIME', 060);
define('IMAGE_IPTC_ORIGINATING_PROGRAM', 065);
define('IMAGE_IPTC_PROGRA_VERSION', 070);
define('IMAGE_IPTC_OBJECT_CYCLE', 075);
define('IMAGE_IPTC_BYLINE', 080);
define('IMAGE_IPTC_BYLINE_TITLE', 085);
define('IMAGE_IPTC_CITY', 090);
define('IMAGE_IPTC_PROVINCE_STATE', 095);
define('IMAGE_IPTC_COUNTRY_CODE', 100);
define('IMAGE_IPTC_COUNTRY', 101);
define('IMAGE_IPTC_ORIGINAL_TRANSMISSION_REFERENCE', 103);
define('IMAGE_IPTC_HEADLINE', 105);
define('IMAGE_IPTC_CREDIT', 110);
define('IMAGE_IPTC_SOURCE', 115);
define('IMAGE_IPTC_COPYRIGHT_STRING', 116);
define('IMAGE_IPTC_CAPTION', 120);
define('IMAGE_IPTC_LOCAL_CAPTION', 121);

/**
* An abstraction layer for working with IPTC fields
*
* This class encapsulates the functions iptcparse() and iptcembed(). It provides
* the necessary methods for extracting, modifying, and saving IPTC data with
* image files (JPEG and TIFF files only).
*
* @author Patrick O'Lone <polone@townnews.com>
* @copyright 2003-2004 TownNews.com
* @version $Revision$
*/
class Image_IPTC
{
    /**
    * @var string
    * The name of the image file that contains the IPTC fields to extract and
    * modify.
    * @see Image_IPTC()
    * @access private
    */
    var $_sFilename = null;

    /**
    * @var array
    * The IPTC fields that were extracted from the image or updated by this
    * class.
    * @see getAllTags(), getTag(), setTag()
    * @access private
    */
    var $_aIPTC = array();

    /**
    * Constructor
    *
    * @param string
    * The name of the image file to access and extract IPTC information from.
    *
    * @access public
    */
    function Image_IPTC( $sFilename )
    {
        $this->_sFilename = $sFilename;

        if (is_file($this->_sFilename)) {

           if (@getimagesize($this->_sFilename, $aAPP) && !empty($aAPP)) {

               $this->_aIPTC = iptcparse($aAPP['APP13']);

           }
        }
    }

    /**
    * Set IPTC fields to a specific value or values
    *
    * @param integer
    * The field (by number) of the IPTC data you wish to update
    *
    * @param mixed
    * If the value supplied is scalar, then the block assigned will be set to
    * the given value. If the value supplied is an array, then the entire tag
    * will be given the value of the array.
    *
    * @param integer
    * The block to update. Most tags only use the 0th block, but certain tags,
    * like the IMAGE_IPTC_KEYWORDS tag, use a list of values. If set to a
    * negative value, the entire tag block will be replaced by the value of
    * the second parameter.
    *
    * @access public
    */
    function setTag( $nTagName, $xValue, $nBlock = 0 )
    {
        $sTagName = sprintf('2#%03d', $nTagName);
        if (($nBlock < 0) || is_array($xValue)) {

            $this->_aIPTC[$sTagName] = $xValue;

        } else {

            $this->_aIPTC[$sTagName][$nBlock] = $xValue;

        }
    }

    /**
    * Get a specific tag/block from the IPTC fields
    *
    * @return mixed
    * If the requested tag exists, a scalar value will be returned. If the block
    * is negative, the entire
    *
    * @param integer
    * The tag name (by number) to access. Use the IMAGE_IPTC_* constants for this
    * field value.
    *
    * @param integer
    * The block to reference. Most fields only have one block (the 0th block),
    * but others, like the IMAGE_IPTC_KEYWORDS block, are an array. If you want
    * to get the whole array, set this to a negative number like -1.
    *
    * @access public
    */
    function getTag( $nTagName, $nBlock = 0 )
    {
        $sTagName = sprintf('2#%03d', $nTagName);
        if (is_array($this->_aIPTC[$sTagName])) {

            if ($nBlock < 0) {

                return $this->_aIPTC[$sTagName];

            } else if (isset($this->_aIPTC[$sTagName][$nBlock])) {

                return $this->_aIPTC[$sTagName][$nBlock];

            }

        }

        return null;
    }

    /**
    * Get a copy of all IPTC tags extracted from the image
    *
    * @return array
    * An array of IPTC fields as it extracted by the iptcparse() function
    *
    * @access public
    */
    function getAllTags()
    {
        return $this->_aIPTC;
    }

    /**
    * Save the IPTC block to an image file
    *
    * @return boolean
    *
    * @param string
    * If supplied, the altered IPTC block and image data will be saved to another
    * file instead of the same file.
    *
    * @access public
    */
    function save( $sOutputFile = null )
    {
        if (empty($sOutputFile)) {

           $sOutputFile = $this->_sFilename;

        }

        $sIPTCBlock = $this->_getIPTCBlock();
        $sImageData = @iptcembed($sIPTCBlock, $this->_sFilename, 0);

        $hImageFile = @fopen($sOutputFile, 'wb');
        if (is_resource($hImageFile)) {

           flock($hImageFile, LOCK_EX);
           fwrite($hImageFile, $sImageData);
           flock($hImageFile, LOCK_UN);
           return fclose($hImageFile);

        }

        return false;
    }

    /**
    * Embed IPTC data block and output to standard output
    *
    * @access public
    */
    function output()
    {
        $sIPTCBlock = $this->_getIPTCBlock();
        @iptcembed($sIPTCBlock, $this->_sFilename, 2);
    }

    /**
    * Generate an IPTC block from the current tags
    *
    * @return string
    * Returns a binary string that contains the new IPTC block that can be used
    * in the iptcembed() function call
    *
    * @access private
    */
    function &_getIPTCBlock()
    {
        $sIPTCBlock = null;

        foreach($this->_aIPTC as $sTagID => $aTag) {

            $sTag = str_replace('2#', null, $sTagID);
            for($ci = 0; $ci < sizeof($aTag); $ci++) {

                $nLen = strlen($aTag[$ci]);

                // The below code is based on code contributed by Thies C. Arntzen
                // on the PHP website at the URL: http://www.php.net/iptcembed

                $sIPTCBlock .= pack('C*', 0x1C, 2, $sTag);

                if ($nLen < 32768) {

                    $sIPTCBlock .= pack('C*', $nLen >> 8, $nLen & 0xFF);

                } else {

                    $sIPTCBlock .= pack('C*', 0x80, 0x04);
                    $sIPTCBlock .= pack('C', $nLen >> 24 & 0xFF);
                    $sIPTCBlock .= pack('C', $nLen >> 16 & 0xFF);
                    $sIPTCBlock .= pack('C', $nLen >> 8 & 0xFF);
                    $sIPTCBlock .= pack('C', $nLen & 0xFF);

                }

                $sIPTCBlock .= $aTag[$ci];
            }
        }

        return $sIPTCBlock;
    }
}

?>

