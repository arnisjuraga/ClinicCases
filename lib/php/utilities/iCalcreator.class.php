<?php
/*********************************************************************************/
/**
 * iCalcreator v2.12
 * copyright (c) 2007-2011 Kjell-Inge Gustafsson kigkonsult
 * kigkonsult.se/iCalcreator/index.php
 * ical@kigkonsult.se
 *
 * Description:
 * This file is a PHP implementation of RFC 2445.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
/*********************************************************************************/
/*********************************************************************************/
/*         A little setup                                                        */
/*********************************************************************************/
/* your local language code */
// define( 'ICAL_LANG', 'sv' );
// alt. autosetting
/*
$langstr     = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$pos         = strpos( $langstr, ';' );
if ($pos   !== false) {
  $langstr   = substr( $langstr, 0, $pos );
  $pos       = strpos( $langstr, ',' );
  if ($pos !== false) {
    $pos     = strpos( $langstr, ',' );
    $langstr = substr( $langstr, 0, $pos );
  }
  define( 'ICAL_LANG', $langstr );
}
*/
/*********************************************************************************/
/*         only for phpversion 5.1 and later,                                    */
/*         date management, default timezone setting                             */
/*         since 2.6.36 - 2010-12-31 */
/*if( substr( phpversion(), 0, 3 ) >= '5.1' )
  // && ( 'UTC' == date_default_timezone_get()))
  date_default_timezone_set( 'Europe/Stockholm' );
/*********************************************************************************/
/*         version, do NOT remove!!                                              */
define('ICALCREATOR_VERSION', 'iCalcreator 2.12');
/*********************************************************************************/
/*********************************************************************************/

/**
 * vcalendar class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.9.6 - 2011-05-14
 */
class vcalendar
{
    //  calendar property variables
    var $calscale;
    var $method;
    var $prodid;
    var $version;
    var $xprop;
    //  container for calendar components
    var $components;
    //  component config variables
    var $allowEmpty;
    var $unique_id;
    var $language;
    var $directory;
    var $filename;
    var $url;
    var $delimiter;
    var $nl;
    var $format;
    var $dtzid;
    //  component internal variables
    var $attributeDelimiter;
    var $valueInit;
    //  component xCal declaration container
    var $xcaldecl;

    /**
     * constructor for calendar object
     *
     * @param array $config
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.9.6 - 2011-05-14
     */
    function vcalendar($config = [])
    {
        $this->_makeVersion();
        $this->calscale = null;
        $this->method = null;
        $this->_makeUnique_id();
        $this->prodid = null;
        $this->xprop = [];
        $this->language = null;
        $this->directory = null;
        $this->filename = null;
        $this->url = null;
        $this->dtzid = null;
        /**
         *   language = <Text identifying a language, as defined in [RFC 1766]>
         */
        if (defined('ICAL_LANG') && !isset($config['language'])) {
            $config['language'] = ICAL_LANG;
        }
        if (!isset($config['allowEmpty'])) {
            $config['allowEmpty'] = true;
        }
        if (!isset($config['nl'])) {
            $config['nl'] = "\r\n";
        }
        if (!isset($config['format'])) {
            $config['format'] = 'iCal';
        }
        if (!isset($config['delimiter'])) {
            $config['delimiter'] = DIRECTORY_SEPARATOR;
        }
        $this->setConfig($config);

        $this->xcaldecl = [];
        $this->components = [];
    }
    /*********************************************************************************/
    /**
     * Property Name: CALSCALE
     */
    /**
     * creates formatted output for calendar property calscale
     *
     * @return string
     * @since 2.10.16 - 2011-10-28
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createCalscale()
    {
        if (empty($this->calscale)) {
            return false;
        }
        switch ($this->format) {
            case 'xcal':
                return $this->nl . ' calscale="' . $this->calscale . '"';
                break;
            default:
                return 'CALSCALE:' . $this->calscale . $this->nl;
                break;
        }
    }

    /**
     * set calendar property calscale
     *
     * @param string $value
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.4.8 - 2008-10-21
     */
    function setCalscale($value)
    {
        if (empty($value)) {
            return false;
        }
        $this->calscale = $value;
    }
    /*********************************************************************************/
    /**
     * Property Name: METHOD
     */
    /**
     * creates formatted output for calendar property method
     *
     * @return string
     * @since 2.10.16 - 2011-10-28
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createMethod()
    {
        if (empty($this->method)) {
            return false;
        }
        switch ($this->format) {
            case 'xcal':
                return $this->nl . ' method="' . $this->method . '"';
                break;
            default:
                return 'METHOD:' . $this->method . $this->nl;
                break;
        }
    }

    /**
     * set calendar property method
     *
     * @param string $value
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.4.8 - 2008-20-23
     */
    function setMethod($value)
    {
        if (empty($value)) {
            return false;
        }
        $this->method = $value;
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: PRODID
     *
     *  The identifier is RECOMMENDED to be the identical syntax to the
     * [RFC 822] addr-spec. A good method to assure uniqueness is to put the
     * domain name or a domain literal IP address of the host on which.. .
     */
    /**
     * creates formatted output for calendar property prodid
     *
     * @return string
     * @since 2.10.16 - 2011-10-28
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createProdid()
    {
        if (!isset($this->prodid)) {
            $this->_makeProdid();
        }
        switch ($this->format) {
            case 'xcal':
                return $this->nl . ' prodid="' . $this->prodid . '"';
                break;
            default:
                return 'PRODID:' . $this->prodid . $this->nl;
                break;
        }
    }

    /**
     * make default value for calendar prodid
     *
     * @return void
     * @since 2.6.8 - 2009-12-30
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function _makeProdid()
    {
        $this->prodid = '-//' . $this->unique_id . '//NONSGML kigkonsult.se ' . ICALCREATOR_VERSION . '//' . strtoupper($this->language);
    }
    /**
     * Conformance: The property MUST be specified once in an iCalendar object.
     * Description: The vendor of the implementation SHOULD assure that this
     * is a globally unique identifier; using some technique such as an FPI
     * value, as defined in [ISO 9070].
     */
    /**
     * make default unique_id for calendar prodid
     *
     * @return void
     * @since 0.3.0 - 2006-08-10
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function _makeUnique_id()
    {
        $this->unique_id = (isset($_SERVER['SERVER_NAME'])) ? gethostbyname($_SERVER['SERVER_NAME']) : 'localhost';
    }
    /*********************************************************************************/
    /**
     * Property Name: VERSION
     *
     * Description: A value of "2.0" corresponds to this memo.
     */
    /**
     * creates formatted output for calendar property version
     *
     * @return string
     * @since 2.10.16 - 2011-10-28
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createVersion()
    {
        if (empty($this->version)) {
            $this->_makeVersion();
        }
        switch ($this->format) {
            case 'xcal':
                return $this->nl . ' version="' . $this->version . '"';
                break;
            default:
                return 'VERSION:' . $this->version . $this->nl;
                break;
        }
    }

    /**
     * set default calendar version
     *
     * @return void
     * @since 0.3.0 - 2006-08-10
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function _makeVersion()
    {
        $this->version = '2.0';
    }

    /**
     * set calendar version
     *
     * @param string $value
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.4.8 - 2008-10-23
     */
    function setVersion($value)
    {
        if (empty($value)) {
            return false;
        }
        $this->version = $value;
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: x-prop
     */
    /**
     * creates formatted output for calendar property x-prop, iCal format only
     *
     * @return string
     * @since 2.10.16 - 2011-11-01
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createXprop()
    {
        if (empty($this->xprop) || !is_array($this->xprop)) {
            return false;
        }
        $output = null;
        $toolbox = new calendarComponent();
        $toolbox->setConfig($this->getConfig());
        foreach ($this->xprop as $label => $xpropPart) {
            if (!isset($xpropPart['value']) || (empty($xpropPart['value']) && !is_numeric($xpropPart['value']))) {
                $output .= $toolbox->_createElement($label);
                continue;
            }
            $attributes = $toolbox->_createParams($xpropPart['params'], ['LANGUAGE']);
            if (is_array($xpropPart['value'])) {
                foreach ($xpropPart['value'] as $pix => $theXpart) {
                    $xpropPart['value'][$pix] = $toolbox->_strrep($theXpart);
                }
                $xpropPart['value'] = implode(',', $xpropPart['value']);
            } else {
                $xpropPart['value'] = $toolbox->_strrep($xpropPart['value']);
            }
            $output .= $toolbox->_createElement($label, $attributes, $xpropPart['value']);
            if (is_array($toolbox->xcaldecl) && (0 < count($toolbox->xcaldecl))) {
                foreach ($toolbox->xcaldecl as $localxcaldecl) {
                    $this->xcaldecl[] = $localxcaldecl;
                }
            }
        }
        return $output;
    }

    /**
     * set calendar property x-prop
     *
     * @param string $label
     * @param string $value
     * @param array $params optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.9 - 2012-01-16
     */
    function setXprop($label, $value, $params = false)
    {
        if (empty($label)) {
            return false;
        }
        if ('X-' != strtoupper(substr($label, 0, 2))) {
            return false;
        }
        if (empty($value) && !is_numeric($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $xprop = ['value' => $value];
        $xprop['params'] = iCalUtilityFunctions::_setParams($params);
        if (!is_array($this->xprop)) {
            $this->xprop = [];
        }
        $this->xprop[strtoupper($label)] = $xprop;
        return true;
    }
    /*********************************************************************************/
    /**
     * delete calendar property value
     *
     * @param mixed $propName , bool FALSE => X-property
     * @param int $propix , optional, if specific property is wanted in case of multiply occurences
     * @return bool, if successfull delete
     * @since 2.8.8 - 2011-03-15
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function deleteProperty($propName = false, $propix = false)
    {
        $propName = ($propName) ? strtoupper($propName) : 'X-PROP';
        if (!$propix) {
            $propix = (isset($this->propdelix[$propName]) && ('X-PROP' != $propName)) ? $this->propdelix[$propName] + 2 : 1;
        }
        $this->propdelix[$propName] = --$propix;
        $return = false;
        switch ($propName) {
            case 'CALSCALE':
                if (isset($this->calscale)) {
                    $this->calscale = null;
                    $return = true;
                }
                break;
            case 'METHOD':
                if (isset($this->method)) {
                    $this->method = null;
                    $return = true;
                }
                break;
            default:
                $reduced = [];
                if ($propName != 'X-PROP') {
                    if (!isset($this->xprop[$propName])) {
                        unset($this->propdelix[$propName]);
                        return false;
                    }
                    foreach ($this->xprop as $k => $a) {
                        if (($k != $propName) && !empty($a)) {
                            $reduced[$k] = $a;
                        }
                    }
                } else {
                    if (count($this->xprop) <= $propix) {
                        return false;
                    }
                    $xpropno = 0;
                    foreach ($this->xprop as $xpropkey => $xpropvalue) {
                        if ($propix != $xpropno) {
                            $reduced[$xpropkey] = $xpropvalue;
                        }
                        $xpropno++;
                    }
                }
                $this->xprop = $reduced;
                if (empty($this->xprop)) {
                    unset($this->propdelix[$propName]);
                    return false;
                }
                return true;
        }
        return $return;
    }

    /**
     * get calendar property value/params
     *
     * @param string $propName , optional
     * @param int $propix , optional, if specific property is wanted in case of multiply occurences
     * @param bool $inclParam =FALSE
     * @return mixed
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.8.8 - 2011-04-16
     */
    function getProperty($propName = false, $propix = false, $inclParam = false)
    {
        $propName = ($propName) ? strtoupper($propName) : 'X-PROP';
        if ('X-PROP' == $propName) {
            if (!$propix) {
                $propix = (isset($this->propix[$propName])) ? $this->propix[$propName] + 2 : 1;
            }
            $this->propix[$propName] = --$propix;
        }
        switch ($propName) {
            case 'ATTENDEE':
            case 'CATEGORIES':
            case 'DTSTART':
            case 'LOCATION':
            case 'ORGANIZER':
            case 'PRIORITY':
            case 'RESOURCES':
            case 'STATUS':
            case 'SUMMARY':
            case 'RECURRENCE-ID-UID':
            case 'R-UID':
            case 'UID':
                $output = [];
                foreach ($this->components as $cix => $component) {
                    if (!in_array($component->objName, ['vevent', 'vtodo', 'vjournal', 'vfreebusy'])) {
                        continue;
                    }
                    if (('ATTENDEE' == $propName) || ('CATEGORIES' == $propName) || ('RESOURCES' == $propName)) {
                        $component->_getProperties($propName, $output);
                        continue;
                    } elseif ((3 < strlen($propName)) && ('UID' == substr($propName, -3))) {
                        if (false !== ($content = $component->getProperty('RECURRENCE-ID'))) {
                            $content = $component->getProperty('UID');
                        }
                    } elseif (false === ($content = $component->getProperty($propName))) {
                        continue;
                    }
                    if (false === $content) {
                        continue;
                    } elseif (is_array($content)) {
                        if (isset($content['year'])) {
                            $key = sprintf('%04d%02d%02d', $content['year'], $content['month'], $content['day']);
                            if (!isset($output[$key])) {
                                $output[$key] = 1;
                            } else {
                                $output[$key] += 1;
                            }
                        } else {
                            foreach ($content as $partValue => $partCount) {
                                if (!isset($output[$partValue])) {
                                    $output[$partValue] = $partCount;
                                } else {
                                    $output[$partValue] += $partCount;
                                }
                            }
                        }
                    } // end elseif( is_array( $content )) {
                    elseif (!isset($output[$content])) {
                        $output[$content] = 1;
                    } else {
                        $output[$content] += 1;
                    }
                } // end foreach ( $this->components as $cix => $component)
                if (!empty($output)) {
                    ksort($output);
                }
                return $output;
                break;

            case 'CALSCALE':
                return (!empty($this->calscale)) ? $this->calscale : false;
                break;
            case 'METHOD':
                return (!empty($this->method)) ? $this->method : false;
                break;
            case 'PRODID':
                if (empty($this->prodid)) {
                    $this->_makeProdid();
                }
                return $this->prodid;
                break;
            case 'VERSION':
                return (!empty($this->version)) ? $this->version : false;
                break;
            default:
                if ($propName != 'X-PROP') {
                    if (!isset($this->xprop[$propName])) {
                        return false;
                    }
                    return ($inclParam) ? [$propName, $this->xprop[$propName]]
                        : [$propName, $this->xprop[$propName]['value']];
                } else {
                    if (empty($this->xprop)) {
                        return false;
                    }
                    $xpropno = 0;
                    foreach ($this->xprop as $xpropkey => $xpropvalue) {
                        if ($propix == $xpropno) {
                            return ($inclParam) ? [$xpropkey, $this->xprop[$xpropkey]]
                                : [$xpropkey, $this->xprop[$xpropkey]['value']];
                        } else {
                            $xpropno++;
                        }
                    }
                    unset($this->propix[$propName]);
                    return false; // not found ??
                }
        }
        return false;
    }

    /**
     * general vcalendar property setting
     *
     * @param mixed $args variable number of function arguments,
     *                    first argument is ALWAYS component name,
     *                    second ALWAYS component value!
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.2.13 - 2007-11-04
     */
    function setProperty()
    {
        $numargs = func_num_args();
        if (1 > $numargs) {
            return false;
        }
        $arglist = func_get_args();
        $arglist[0] = strtoupper($arglist[0]);
        switch ($arglist[0]) {
            case 'CALSCALE':
                return $this->setCalscale($arglist[1]);
            case 'METHOD':
                return $this->setMethod($arglist[1]);
            case 'VERSION':
                return $this->setVersion($arglist[1]);
            default:
                if (!isset($arglist[1])) {
                    $arglist[1] = null;
                }
                if (!isset($arglist[2])) {
                    $arglist[2] = null;
                }
                return $this->setXprop($arglist[0], $arglist[1], $arglist[2]);
        }
        return false;
    }
    /*********************************************************************************/
    /**
     * get vcalendar config values or * calendar components
     *
     * @param mixed $config
     * @return value
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.7 - 2012-01-12
     */
    function getConfig($config = false)
    {
        if (!$config) {
            $return = [];
            $return['ALLOWEMPTY'] = $this->getConfig('ALLOWEMPTY');
            $return['DELIMITER'] = $this->getConfig('DELIMITER');
            $return['DIRECTORY'] = $this->getConfig('DIRECTORY');
            $return['FILENAME'] = $this->getConfig('FILENAME');
            $return['DIRFILE'] = $this->getConfig('DIRFILE');
            $return['FILESIZE'] = $this->getConfig('FILESIZE');
            $return['FORMAT'] = $this->getConfig('FORMAT');
            if (false !== ($lang = $this->getConfig('LANGUAGE'))) {
                $return['LANGUAGE'] = $lang;
            }
            $return['NEWLINECHAR'] = $this->getConfig('NEWLINECHAR');
            $return['UNIQUE_ID'] = $this->getConfig('UNIQUE_ID');
            if (false !== ($url = $this->getConfig('URL'))) {
                $return['URL'] = $url;
            }
            $return['TZID'] = $this->getConfig('TZID');
            return $return;
        }
        switch (strtoupper($config)) {
            case 'ALLOWEMPTY':
                return $this->allowEmpty;
                break;
            case 'COMPSINFO':
                unset($this->compix);
                $info = [];
                foreach ($this->components as $cix => $component) {
                    if (empty($component)) {
                        continue;
                    }
                    $info[$cix]['ordno'] = $cix + 1;
                    $info[$cix]['type'] = $component->objName;
                    $info[$cix]['uid'] = $component->getProperty('uid');
                    $info[$cix]['props'] = $component->getConfig('propinfo');
                    $info[$cix]['sub'] = $component->getConfig('compsinfo');
                }
                return $info;
                break;
            case 'DELIMITER':
                return $this->delimiter;
                break;
            case 'DIRECTORY':
                if (empty($this->directory) && ('0' != $this->directory)) {
                    $this->directory = '.';
                }
                return $this->directory;
                break;
            case 'DIRFILE':
                return $this->getConfig('directory') . $this->getConfig('delimiter') . $this->getConfig('filename');
                break;
            case 'FILEINFO':
                return [
                    $this->getConfig('directory')
                    ,
                    $this->getConfig('filename')
                    ,
                    $this->getConfig('filesize'),
                ];
                break;
            case 'FILENAME':
                if (empty($this->filename) && ('0' != $this->filename)) {
                    if ('xcal' == $this->format) {
                        $this->filename = date('YmdHis') . '.xml';
                    } // recommended xcs.. .
                    else {
                        $this->filename = date('YmdHis') . '.ics';
                    }
                }
                return $this->filename;
                break;
            case 'FILESIZE':
                $size = 0;
                if (empty($this->url)) {
                    $dirfile = $this->getConfig('dirfile');
                    if (!is_file($dirfile) || (false === ($size = filesize($dirfile)))) {
                        $size = 0;
                    }
                    clearstatcache();
                }
                return $size;
                break;
            case 'FORMAT':
                return ($this->format == 'xcal') ? 'xCal' : 'iCal';
                break;
            case 'LANGUAGE':
                /* get language for calendar component as defined in [RFC 1766] */
                return $this->language;
                break;
            case 'NL':
            case 'NEWLINECHAR':
                return $this->nl;
                break;
            case 'TZID':
                return $this->dtzid;
                break;
            case 'UNIQUE_ID':
                return $this->unique_id;
                break;
            case 'URL':
                if (!empty($this->url)) {
                    return $this->url;
                } else {
                    return false;
                }
                break;
        }
    }

    /**
     * general vcalendar config setting
     *
     * @param mixed $config
     * @param string $value
     * @return void
     * @since 2.11.11 - 2011-01-16
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setConfig($config, $value = false)
    {
        if (is_array($config)) {
            $ak = array_keys($config);
            foreach ($ak as $k) {
                if ('DIRECTORY' == strtoupper($k)) {
                    if (false === $this->setConfig('DIRECTORY', $config[$k])) {
                        return false;
                    }
                    unset($config[$k]);
                } elseif ('NEWLINECHAR' == strtoupper($k)) {
                    if (false === $this->setConfig('NEWLINECHAR', $config[$k])) {
                        return false;
                    }
                    unset($config[$k]);
                }
            }
            foreach ($config as $cKey => $cValue) {
                if (false === $this->setConfig($cKey, $cValue)) {
                    return false;
                }
            }
            return true;
        }
        $res = false;
        switch (strtoupper($config)) {
            case 'ALLOWEMPTY':
                $this->allowEmpty = $value;
                $subcfg = ['ALLOWEMPTY' => $value];
                $res = true;
                break;
            case 'DELIMITER':
                $this->delimiter = $value;
                return true;
                break;
            case 'DIRECTORY':
                $value = trim($value);
                $del = $this->getConfig('delimiter');
                if ($del == substr($value, (0 - strlen($del)))) {
                    $value = substr($value, 0, (strlen($value) - strlen($del)));
                }
                if (is_dir($value)) {
                    /* local directory */
                    clearstatcache();
                    $this->directory = $value;
                    $this->url = null;
                    return true;
                } else {
                    return false;
                }
                break;
            case 'FILENAME':
                $value = trim($value);
                if (!empty($this->url)) {
                    /* remote directory+file -> URL */
                    $this->filename = $value;
                    return true;
                }
                $dirfile = $this->getConfig('directory') . $this->getConfig('delimiter') . $value;
                if (file_exists($dirfile)) {
                    /* local file exists */
                    if (is_readable($dirfile) || is_writable($dirfile)) {
                        clearstatcache();
                        $this->filename = $value;
                        return true;
                    } else {
                        return false;
                    }
                } elseif (is_readable($this->getConfig('directory')) || is_writable($this->getConfig('directory'))) {
                    /* read- or writable directory */
                    $this->filename = $value;
                    return true;
                } else {
                    return false;
                }
                break;
            case 'FORMAT':
                $value = trim(strtolower($value));
                if ('xcal' == $value) {
                    $this->format = 'xcal';
                    $this->attributeDelimiter = $this->nl;
                    $this->valueInit = null;
                } else {
                    $this->format = null;
                    $this->attributeDelimiter = ';';
                    $this->valueInit = ':';
                }
                $subcfg = ['FORMAT' => $value];
                $res = true;
                break;
            case 'LANGUAGE':
                // set language for calendar component as defined in [RFC 1766]
                $value = trim($value);
                $this->language = $value;
                $subcfg = ['LANGUAGE' => $value];
                $res = true;
                break;
            case 'NL':
            case 'NEWLINECHAR':
                $this->nl = $value;
                if ('xcal' == $value) {
                    $this->attributeDelimiter = $this->nl;
                    $this->valueInit = null;
                } else {
                    $this->attributeDelimiter = ';';
                    $this->valueInit = ':';
                }
                $subcfg = ['NL' => $value];
                $res = true;
                break;
            case 'TZID':
                $this->dtzid = $value;
                $subcfg = ['TZID' => $value];
                $res = true;
                break;
            case 'UNIQUE_ID':
                $value = trim($value);
                $this->unique_id = $value;
                $this->_makeProdid();
                $subcfg = ['UNIQUE_ID' => $value];
                $res = true;
                break;
            case 'URL':
                /* remote file - URL */
                $value = trim($value);
                $value = str_replace('HTTP://', 'http://', $value);
                $value = str_replace('WEBCAL://', 'http://', $value);
                $value = str_replace('webcal://', 'http://', $value);
                $this->url = $value;
                $this->directory = null;
                $parts = pathinfo($value);
                return $this->setConfig('filename', $parts['basename']);
                break;
            default:  // any unvalid config key.. .
                return true;
        }
        if (!$res) {
            return false;
        }
        if (isset($subcfg) && !empty($this->components)) {
            foreach ($subcfg as $cfgkey => $cfgvalue) {
                foreach ($this->components as $cix => $component) {
                    $res = $component->setConfig($cfgkey, $cfgvalue, true);
                    if (!$res) {
                        break 2;
                    }
                    $this->components[$cix] = $component->copy(); // PHP4 compliant
                }
            }
        }
        return $res;
    }
    /*********************************************************************************/
    /**
     * add calendar component to container
     *
     * alias to setComponent
     *
     * @param object $component calendar component
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 1.x.x - 2007-04-24
     */
    function addComponent($component)
    {
        $this->setComponent($component);
    }

    /**
     * delete calendar component from container
     *
     * @param mixed $arg1 ordno / component type / component uid
     * @param mixed $arg2 optional, ordno if arg1 = component type
     * @return void
     * @since 2.8.8 - 2011-03-15
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function deleteComponent($arg1, $arg2 = false)
    {
        $argType = $index = null;
        if (ctype_digit((string)$arg1)) {
            $argType = 'INDEX';
            $index = (int)$arg1 - 1;
        } elseif ((strlen($arg1) <= strlen('vfreebusy')) && (false === strpos($arg1, '@'))) {
            $argType = strtolower($arg1);
            $index = (!empty($arg2) && ctype_digit((string)$arg2)) ? (( int )$arg2 - 1) : 0;
        }
        $cix1dC = 0;
        foreach ($this->components as $cix => $component) {
            if (empty($component)) {
                continue;
            }
            if (('INDEX' == $argType) && ($index == $cix)) {
                unset($this->components[$cix]);
                return true;
            } elseif ($argType == $component->objName) {
                if ($index == $cix1dC) {
                    unset($this->components[$cix]);
                    return true;
                }
                $cix1dC++;
            } elseif (!$argType && ($arg1 == $component->getProperty('uid'))) {
                unset($this->components[$cix]);
                return true;
            }
        }
        return false;
    }

    /**
     * get calendar component from container
     *
     * @param mixed $arg1 optional, ordno/component type/ component uid
     * @param mixed $arg2 optional, ordno if arg1 = component type
     * @return object
     * @since 2.9.1 - 2011-04-16
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function getComponent($arg1 = false, $arg2 = false)
    {
        $index = $argType = null;
        if (!$arg1) { // first or next in component chain
            $argType = 'INDEX';
            $index = $this->compix['INDEX'] = (isset($this->compix['INDEX'])) ? $this->compix['INDEX'] + 1 : 1;
        } elseif (ctype_digit((string)$arg1)) { // specific component in chain
            $argType = 'INDEX';
            $index = (int)$arg1;
            unset($this->compix);
        } elseif (is_array($arg1)) { // array( *[propertyName => propertyValue] )
            $arg2 = implode('-', array_keys($arg1));
            $index = $this->compix[$arg2] = (isset($this->compix[$arg2])) ? $this->compix[$arg2] + 1 : 1;
            $dateProps = ['DTSTART', 'DTEND', 'DUE', 'CREATED', 'COMPLETED', 'DTSTAMP', 'LAST-MODIFIED', 'RECURRENCE-ID'];
            $otherProps = ['ATTENDEE', 'CATEGORIES', 'LOCATION', 'ORGANIZER', 'PRIORITY', 'RESOURCES', 'STATUS', 'SUMMARY', 'UID'];
        } elseif ((strlen($arg1) <= strlen('vfreebusy')) && (false === strpos($arg1, '@'))) { // object class name
            unset($this->compix['INDEX']);
            $argType = strtolower($arg1);
            if (!$arg2) {
                $index = $this->compix[$argType] = (isset($this->compix[$argType])) ? $this->compix[$argType] + 1 : 1;
            } elseif (isset($arg2) && ctype_digit((string)$arg2)) {
                $index = (int)$arg2;
            }
        } elseif ((strlen($arg1) > strlen('vfreebusy')) && (false !== strpos($arg1, '@'))) { // UID as 1st argument
            if (!$arg2) {
                $index = $this->compix[$arg1] = (isset($this->compix[$arg1])) ? $this->compix[$arg1] + 1 : 1;
            } elseif (isset($arg2) && ctype_digit((string)$arg2)) {
                $index = (int)$arg2;
            }
        }
        if (isset($index)) {
            $index -= 1;
        }
        $ckeys = array_keys($this->components);
        if (!empty($index) && ($index > end($ckeys))) {
            return false;
        }
        $cix1gC = 0;
        foreach ($this->components as $cix => $component) {
            if (empty($component)) {
                continue;
            }
            if (('INDEX' == $argType) && ($index == $cix)) {
                return $component->copy();
            } elseif ($argType == $component->objName) {
                if ($index == $cix1gC) {
                    return $component->copy();
                }
                $cix1gC++;
            } elseif (is_array($arg1)) { // array( *[propertyName => propertyValue] )
                $hit = false;
                foreach ($arg1 as $pName => $pValue) {
                    $pName = strtoupper($pName);
                    if (!in_array($pName, $dateProps) && !in_array($pName, $otherProps)) {
                        continue;
                    }
                    if (('ATTENDEE' == $pName) || ('CATEGORIES' == $pName) || ('RESOURCES' == $pName)) { // multiple ocurrence may occur
                        $propValues = [];
                        $component->_getProperties($pName, $propValues);
                        $propValues = array_keys($propValues);
                        $hit = (in_array($pValue, $propValues)) ? true : false;
                        continue;
                    } // end   if(( 'CATEGORIES' == $propName ) || ( 'RESOURCES' == $propName )) { // multiple ocurrence may occur
                    if (false === ($value = $component->getProperty($pName))) { // single ocurrency
                        $hit = false; // missing property
                        continue;
                    }
                    if ('SUMMARY' == $pName) { // exists within (any case)
                        $hit = (false !== stripos($d, $pValue)) ? true : false;
                        continue;
                    }
                    if (in_array(strtoupper($pName), $dateProps)) {
                        $valuedate = sprintf('%04d%02d%02d', $value['year'], $value['month'], $value['day']);
                        if (8 < strlen($pValue)) {
                            if (isset($value['hour'])) {
                                if ('T' == substr($pValue, 8, 1)) {
                                    $pValue = str_replace('T', '', $pValue);
                                }
                                $valuedate .= sprintf('%02d%02d%02d', $value['hour'], $value['min'], $value['sec']);
                            } else {
                                $pValue = substr($pValue, 0, 8);
                            }
                        }
                        $hit = ($pValue == $valuedate) ? true : false;
                        continue;
                    } elseif (!is_array($value)) {
                        $value = [$value];
                    }
                    foreach ($value as $part) {
                        $part = (false !== strpos($part, ',')) ? explode(',', $part) : [$part];
                        foreach ($part as $subPart) {
                            if ($pValue == $subPart) {
                                $hit = true;
                                continue 2;
                            }
                        }
                    }
                    $hit = false; // no hit in property
                } // end  foreach( $arg1 as $pName => $pValue )
                if ($hit) {
                    if ($index == $cix1gC) {
                        return $component->copy();
                    }
                    $cix1gC++;
                }
            } // end elseif( is_array( $arg1 )) { // array( *[propertyName => propertyValue] )
            elseif (!$argType && ($arg1 == $component->getProperty('uid'))) { // UID
                if ($index == $cix1gC) {
                    return $component->copy();
                }
                $cix1gC++;
            }
        } // end foreach ( $this->components.. .
        /* not found.. . */
        unset($this->compix);
        return false;
    }

    /**
     * create new calendar component, already included within calendar
     *
     * @param string $compType component type
     * @return object (reference)
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.6.33 - 2011-01-03
     */
    function & newComponent($compType)
    {
        $config = $this->getConfig();
        $keys = array_keys($this->components);
        $ix = end($keys) + 1;
        switch (strtoupper($compType)) {
            case 'EVENT':
            case 'VEVENT':
                $this->components[$ix] = new vevent($config);
                break;
            case 'TODO':
            case 'VTODO':
                $this->components[$ix] = new vtodo($config);
                break;
            case 'JOURNAL':
            case 'VJOURNAL':
                $this->components[$ix] = new vjournal($config);
                break;
            case 'FREEBUSY':
            case 'VFREEBUSY':
                $this->components[$ix] = new vfreebusy($config);
                break;
            case 'TIMEZONE':
            case 'VTIMEZONE':
                array_unshift($this->components, new vtimezone($config));
                $ix = 0;
                break;
            default:
                return false;
        }
        return $this->components[$ix];
    }

    /**
     * select components from calendar on date or selectOption basis
     *
     * Ensure DTSTART is set for every component.
     * No date controls occurs.
     *
     * @param mixed $startY optional, start Year,  default current Year ALT. array selecOptions ( *[ <propName> => <uniqueValue> ] )
     * @param int $startM optional, start Month, default current Month
     * @param int $startD optional, start Day,   default current Day
     * @param int $endY optional, end   Year,  default $startY
     * @param int $endY optional, end   Month, default $startM
     * @param int $endY optional, end   Day,   default $startD
     * @param mixed $cType optional, calendar component type(-s), default FALSE=all else string/array type(-s)
     * @param bool $flat optional, FALSE (default) => output : array[Year][Month][Day][]
     *                                TRUE            => output : array[] (ignores split)
     * @param bool $any optional, TRUE (default) - select component(-s) that occurs within period
     *                                FALSE          - only component(-s) that starts within period
     * @param bool $split optional, TRUE (default) - one component copy every DAY it occurs during the
     *                                                 period (implies flat=FALSE)
     *                                FALSE          - one occurance of component only in output array
     * @return array or FALSE
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.22 - 2012-02-13
     */
    function selectComponents($startY = false, $startM = false, $startD = false, $endY = false, $endM = false, $endD = false, $cType = false, $flat = false, $any = true, $split = true)
    {
        /* check  if empty calendar */
        if (0 >= count($this->components)) {
            return false;
        }
        if (is_array($startY)) {
            return $this->selectComponents2($startY);
        }
        /* check default dates */
        if (!$startY) {
            $startY = date('Y');
        }
        if (!$startM) {
            $startM = date('m');
        }
        if (!$startD) {
            $startD = date('d');
        }
        $startDate = mktime(0, 0, 0, $startM, $startD, $startY);
        if (!$endY) {
            $endY = $startY;
        }
        if (!$endM) {
            $endM = $startM;
        }
        if (!$endD) {
            $endD = $startD;
        }
        $endDate = mktime(23, 59, 59, $endM, $endD, $endY);
        //echo 'selectComp arg='.date( 'Y-m-d H:i:s', $startDate).' -- '.date( 'Y-m-d H:i:s', $endDate)."<br />\n"; $tcnt = 0;// test ###
        /* check component types */
        $validTypes = ['vevent', 'vtodo', 'vjournal', 'vfreebusy'];
        if (is_array($cType)) {
            foreach ($cType as $cix => $theType) {
                $cType[$cix] = $theType = strtolower($theType);
                if (!in_array($theType, $validTypes)) {
                    $cType[$cix] = 'vevent';
                }
            }
            $cType = array_unique($cType);
        } elseif (!empty($cType)) {
            $cType = strtolower($cType);
            if (!in_array($cType, $validTypes)) {
                $cType = ['vevent'];
            } else {
                $cType = [$cType];
            }
        } else {
            $cType = $validTypes;
        }
        if (0 >= count($cType)) {
            $cType = $validTypes;
        }
        if ((false === $flat) && (false === $any)) // invalid combination
        {
            $split = false;
        }
        if ((true === $flat) && (true === $split)) // invalid combination
        {
            $split = false;
        }
        /* iterate components */
        $result = [];
        foreach ($this->components as $cix => $component) {
            if (empty($component)) {
                continue;
            }
            unset($start);
            /* deselect unvalid type components */
            if (!in_array($component->objName, $cType)) {
                continue;
            }
            $start = $component->getProperty('dtstart');
            /* select due when dtstart is missing */
            if (empty($start) && ($component->objName == 'vtodo') && (false === ($start = $component->getProperty('due')))) {
                continue;
            }
            if (empty($start)) {
                continue;
            }
            $dtendExist = $dueExist = $durationExist = $endAllDayEvent = $recurrid = false;
            unset($end, $startWdate, $endWdate, $rdurWsecs, $rdur, $exdatelist, $workstart, $workend, $endDateFormat); // clean up
            $startWdate = iCalUtilityFunctions::_date2timestamp($start);
            $startDateFormat = (isset($start['hour'])) ? 'Y-m-d H:i:s' : 'Y-m-d';
            /* get end date from dtend/due/duration properties */
            $end = $component->getProperty('dtend');
            if (!empty($end)) {
                $dtendExist = true;
                $endDateFormat = (isset($end['hour'])) ? 'Y-m-d H:i:s' : 'Y-m-d';
            }
            if (empty($end) && ($component->objName == 'vtodo')) {
                $end = $component->getProperty('due');
                if (!empty($end)) {
                    $dueExist = true;
                    $endDateFormat = (isset($end['hour'])) ? 'Y-m-d H:i:s' : 'Y-m-d';
                }
            }
            if (!empty($end) && !isset($end['hour'])) {
                /* a DTEND without time part regards an event that ends the day before,
             for an all-day event DTSTART=20071201 DTEND=20071202 (taking place 20071201!!! */
                $endAllDayEvent = true;
                $endWdate = mktime(23, 59, 59, $end['month'], ($end['day'] - 1), $end['year']);
                $end['year'] = date('Y', $endWdate);
                $end['month'] = date('m', $endWdate);
                $end['day'] = date('d', $endWdate);
                $end['hour'] = 23;
                $end['min'] = $end['sec'] = 59;
            }
            if (empty($end)) {
                $end = $component->getProperty('duration', false, false, true);// in dtend (array) format
                if (!empty($end)) {
                    $durationExist = true;
                }
                $endDateFormat = (isset($start['hour'])) ? 'Y-m-d H:i:s' : 'Y-m-d';
                // if( !empty($end))  echo 'selectComp 4 start='.implode('-',$start).' end='.implode('-',$end)."<br />\n"; // test ###
            }
            if (empty($end)) { // assume one day duration if missing end date
                $end = ['year' => $start['year'], 'month' => $start['month'], 'day' => $start['day'], 'hour' => 23, 'min' => 59, 'sec' => 59];
            }
            // if( isset($end))  echo 'selectComp 5 start='.implode('-',$start).' end='.implode('-',$end)."<br />\n"; // test ###
            $endWdate = iCalUtilityFunctions::_date2timestamp($end);
            if ($endWdate < $startWdate) { // MUST be after start date!!
                $end = ['year' => $start['year'], 'month' => $start['month'], 'day' => $start['day'], 'hour' => 23, 'min' => 59, 'sec' => 59];
                $endWdate = iCalUtilityFunctions::_date2timestamp($end);
            }
            $rdurWsecs = $endWdate - $startWdate; // compute event (component) duration in seconds
            /* make a list of optional exclude dates for component occurence from exrule and exdate */
            $exdatelist = [];
            $workstart = iCalUtilityFunctions::_timestamp2date(($startDate - $rdurWsecs), 6);
            $workend = iCalUtilityFunctions::_timestamp2date(($endDate + $rdurWsecs), 6);
            while (false !== ($exrule = $component->getProperty('exrule')))    // check exrule
            {
                iCalUtilityFunctions::_recur2date($exdatelist, $exrule, $start, $workstart, $workend);
            }
            while (false !== ($exdate = $component->getProperty('exdate'))) {  // check exdate
                foreach ($exdate as $theExdate) {
                    $exWdate = iCalUtilityFunctions::_date2timestamp($theExdate);
                    $exWdate = mktime(0, 0, 0, date('m', $exWdate), date('d', $exWdate), date('Y', $exWdate)); // on a day-basis !!!
                    if ((($startDate - $rdurWsecs) <= $exWdate) && ($endDate >= $exWdate)) {
                        $exdatelist[$exWdate] = true;
                    }
                } // end - foreach( $exdate as $theExdate )
            }  // end - check exdate
            $compUID = $component->getProperty('UID');
            /* check recurrence-id (with sequence), remove hit with reccurr-id date */
            if ((false !== ($recurrid = $component->getProperty('recurrence-id'))) &&
                (false !== ($sequence = $component->getProperty('sequence')))) {
                $recurrid = iCalUtilityFunctions::_date2timestamp($recurrid);
                $recurrid = mktime(0, 0, 0, date('m', $recurrid), date('d', $recurrid), date('Y', $recurrid)); // on a day-basis !!!
                $endD = $recurrid + $rdurWsecs;
                do {
                    if (date('Ymd', $startWdate) != date('Ymd', $recurrid)) {
                        $exdatelist[$recurrid] = true;
                    } // exclude all other days than startdate
                    $wd = getdate($recurrid);
                    if (isset($result[$wd['year']][$wd['mon']][$wd['mday']][$compUID])) {
                        unset($result[$wd['year']][$wd['mon']][$wd['mday']][$compUID]);
                    } // remove from output, dtstart etc added below
                    if ($split && ($recurrid <= $endD)) {
                        $recurrid = mktime(0, 0, 0, date('m', $recurrid), date('d', $recurrid) + 1, date('Y', $recurrid));
                    } // step one day
                    else {
                        break;
                    }
                } while (true);
            } // end recurrence-id test
            /* select only components with.. . */
            if ((!$any && ($startWdate >= $startDate) && ($startWdate <= $endDate)) || // (dt)start within the period
                ($any && ($startWdate < $endDate) && ($endWdate >= $startDate))) {    // occurs within the period
                /* add the selected component (WITHIN valid dates) to output array */
                if ($flat) { // any=true/false, ignores split
                    if (!$recurrid) {
                        $result[$compUID] = $component->copy();
                    } // copy original to output (but not anyone with recurrence-id)
                } elseif ($split) { // split the original component
                    if ($endWdate > $endDate) {
                        $endWdate = $endDate;
                    }     // use period end date
                    $rstart = $startWdate;
                    if ($rstart < $startDate) {
                        $rstart = $startDate;
                    } // use period start date
                    $startYMD = date('Ymd', $rstart);
                    $endYMD = date('Ymd', $endWdate);
                    $checkDate = mktime(0, 0, 0, date('m', $rstart), date('d', $rstart), date('Y', $rstart)); // on a day-basis !!!
                    while (date('Ymd', $rstart) <= $endYMD) { // iterate
                        $checkDate = mktime(0, 0, 0, date('m', $rstart), date('d', $rstart), date('Y', $rstart)); // on a day-basis !!!
                        if (isset($exdatelist[$checkDate])) { // exclude any recurrence date, found in exdatelist
                            $rstart = mktime(date('H', $rstart), date('i', $rstart), date('s', $rstart), date('m', $rstart), date('d', $rstart) + 1, date('Y', $rstart)); // step one day
                            continue;
                        }
                        if (date('Ymd', $rstart) > $startYMD) // date after dtstart
                        {
                            $datestring = date($startDateFormat, mktime(0, 0, 0, date('m', $rstart), date('d', $rstart), date('Y', $rstart)));
                        } else {
                            $datestring = date($startDateFormat, $rstart);
                        }
                        if (isset($start['tz'])) {
                            $datestring .= ' ' . $start['tz'];
                        }
                        // echo "X-CURRENT-DTSTART 3 = $datestring xRecurrence=$xRecurrence tcnt =".++$tcnt."<br />";$component->setProperty( 'X-CNT', $tcnt ); // test ###
                        $component->setProperty('X-CURRENT-DTSTART', $datestring);
                        if ($dtendExist || $dueExist || $durationExist) {
                            if (date('Ymd', $rstart) < $endYMD) // not the last day
                            {
                                $tend = mktime(23, 59, 59, date('m', $rstart), date('d', $rstart), date('Y', $rstart));
                            } else {
                                $tend = mktime(date('H', $endWdate), date('i', $endWdate), date('s', $endWdate), date('m', $rstart), date('d', $rstart), date('Y', $rstart));
                            } // on a day-basis !!!
                            if ($endAllDayEvent && $dtendExist) {
                                $tend += (24 * 3600);
                            } // alldaysevents has an end date 'day after' meaning this day
                            $datestring = date($endDateFormat, $tend);
                            if (isset($end['tz'])) {
                                $datestring .= ' ' . $end['tz'];
                            }
                            $propName = (!$dueExist) ? 'X-CURRENT-DTEND' : 'X-CURRENT-DUE';
                            $component->setProperty($propName, $datestring);
                        } // end if( $dtendExist || $dueExist || $durationExist )
                        $wd = getdate($rstart);
                        $result[$wd['year']][$wd['mon']][$wd['mday']][$compUID] = $component->copy(); // copy to output
                        $rstart = mktime(date('H', $rstart), date('i', $rstart), date('s', $rstart), date('m', $rstart), date('d', $rstart) + 1, date('Y', $rstart)); // step one day
                    } // end while( $rstart <= $endWdate )
                } // end if( $split )   -  else use component date
                elseif ($recurrid && !$flat && !$any && !$split) {
                    $continue = true;
                } else { // !$flat && !$split, i.e. no flat array and DTSTART within period
                    $checkDate = mktime(0, 0, 0, date('m', $startWdate), date('d', $startWdate), date('Y', $startWdate)); // on a day-basis !!!
                    if (!$any || !isset($exdatelist[$checkDate])) { // exclude any recurrence date, found in exdatelist
                        $wd = getdate($startWdate);
                        $result[$wd['year']][$wd['mon']][$wd['mday']][$compUID] = $component->copy(); // copy to output
                    }
                }
            } // end if(( $startWdate >= $startDate ) && ( $startWdate <= $endDate ))

            /* if 'any' components, check components with reccurrence rules, removing all excluding dates */
            if (true === $any) {
                /* make a list of optional repeating dates for component occurence, rrule, rdate */
                $recurlist = [];
                while (false !== ($rrule = $component->getProperty('rrule')))    // check rrule
                {
                    iCalUtilityFunctions::_recur2date($recurlist, $rrule, $start, $workstart, $workend);
                }
                foreach ($recurlist as $recurkey => $recurvalue) // key=match date as timestamp
                {
                    $recurlist[$recurkey] = $rdurWsecs;
                } // add duration in seconds
                while (false !== ($rdate = $component->getProperty('rdate'))) {  // check rdate
                    foreach ($rdate as $theRdate) {
                        if (is_array($theRdate) && (2 == count($theRdate)) &&  // all days within PERIOD
                            array_key_exists('0', $theRdate) && array_key_exists('1', $theRdate)) {
                            $rstart = iCalUtilityFunctions::_date2timestamp($theRdate[0]);
                            if (($rstart < ($startDate - $rdurWsecs)) || ($rstart > $endDate)) {
                                continue;
                            }
                            if (isset($theRdate[1]['year'])) // date-date period
                            {
                                $rend = iCalUtilityFunctions::_date2timestamp($theRdate[1]);
                            } else {                             // date-duration period
                                $rend = iCalUtilityFunctions::_duration2date($theRdate[0], $theRdate[1]);
                                $rend = iCalUtilityFunctions::_date2timestamp($rend);
                            }
                            while ($rstart < $rend) {
                                $recurlist[$rstart] = $rdurWsecs; // set start date for recurrence instance + rdate duration in seconds
                                $rstart = mktime(date('H', $rstart), date('i', $rstart), date('s', $rstart), date('m', $rstart), date('d', $rstart) + 1, date('Y', $rstart)); // step one day
                            }
                        } // PERIOD end
                        else { // single date
                            $theRdate = iCalUtilityFunctions::_date2timestamp($theRdate);
                            if ((($startDate - $rdurWsecs) <= $theRdate) && ($endDate >= $theRdate)) {
                                $recurlist[$theRdate] = $rdurWsecs;
                            } // set start date for recurrence instance + event duration in seconds
                        }
                    }
                }  // end - check rdate
                if (0 < count($recurlist)) {
                    ksort($recurlist);
                    $xRecurrence = 1;
                    $component2 = $component->copy();
                    $compUID = $component2->getProperty('UID');
                    foreach ($recurlist as $recurkey => $durvalue) {
                        // echo "recurKey=".date( 'Y-m-d H:i:s', $recurkey ).' dur='.iCalUtilityFunctions::offsetSec2His( $durvalue )."<br />\n"; // test ###;
                        if ((($startDate - $rdurWsecs) > $recurkey) || ($endDate < $recurkey)) // not within period
                        {
                            continue;
                        }
                        $checkDate = mktime(0, 0, 0, date('m', $recurkey), date('d', $recurkey), date('Y', $recurkey)); // on a day-basis !!!
                        if (isset($exdatelist[$checkDate])) // check excluded dates
                        {
                            continue;
                        }
                        if ($startWdate >= $recurkey) // exclude component start date
                        {
                            continue;
                        }
                        $rstart = $recurkey;
                        $rend = $recurkey + $durvalue;
                        /* add repeating components within valid dates to output array, only start date set */
                        if ($flat) {
                            if (!isset($result[$compUID])) // only one comp
                            {
                                $result[$compUID] = $component2->copy();
                            } // copy to output
                        } /* add repeating components within valid dates to output array, one each day */
                        elseif ($split) {
                            if ($rend > $endDate) {
                                $rend = $endDate;
                            }
                            $startYMD = date('Ymd', $rstart);
                            $endYMD = date('Ymd', $rend);
                            // echo "splitStart=".date( 'Y-m-d H:i:s', $rstart ).' end='.date( 'Y-m-d H:i:s', $rend )."<br />\n"; // test ###;
                            while ($rstart <= $rend) { // iterate.. .
                                $checkDate = mktime(0, 0, 0, date('m', $rstart), date('d', $rstart), date('Y', $rstart)); // on a day-basis !!!
                                if (isset($exdatelist[$checkDate]))  // exclude any recurrence START date, found in exdatelist
                                {
                                    break;
                                }
                                // echo "checking date after startdate=".date( 'Y-m-d H:i:s', $rstart ).' mot '.date( 'Y-m-d H:i:s', $startDate )."<br />"; // test ###;
                                if ($rstart >= $startDate) {    // date after dtstart
                                    if (date('Ymd', $rstart) > $startYMD) // date after dtstart
                                    {
                                        $datestring = date($startDateFormat, $checkDate);
                                    } else {
                                        $datestring = date($startDateFormat, $rstart);
                                    }
                                    if (isset($start['tz'])) {
                                        $datestring .= ' ' . $start['tz'];
                                    }
                                    //echo "X-CURRENT-DTSTART 1 = $datestring xRecurrence=$xRecurrence tcnt =".++$tcnt."<br />";$component2->setProperty( 'X-CNT', $tcnt ); // test ###
                                    $component2->setProperty('X-CURRENT-DTSTART', $datestring);
                                    if ($dtendExist || $dueExist || $durationExist) {
                                        if (date('Ymd', $rstart) < $endYMD) // not the last day
                                        {
                                            $tend = mktime(23, 59, 59, date('m', $rstart), date('d', $rstart), date('Y', $rstart));
                                        } else {
                                            $tend = mktime(date('H', $endWdate), date('i', $endWdate), date('s', $endWdate), date('m', $rstart), date('d', $rstart), date('Y', $rstart));
                                        } // on a day-basis !!!
                                        if ($endAllDayEvent && $dtendExist) {
                                            $tend += (24 * 3600);
                                        } // alldaysevents has an end date 'day after' meaning this day
                                        $datestring = date($endDateFormat, $tend);
                                        if (isset($end['tz'])) {
                                            $datestring .= ' ' . $end['tz'];
                                        }
                                        $propName = (!$dueExist) ? 'X-CURRENT-DTEND' : 'X-CURRENT-DUE';
                                        $component2->setProperty($propName, $datestring);
                                    } // end if( $dtendExist || $dueExist || $durationExist )
                                    $component2->setProperty('X-RECURRENCE', $xRecurrence);
                                    $wd = getdate($rstart);
                                    $result[$wd['year']][$wd['mon']][$wd['mday']][$compUID] = $component2->copy(); // copy to output
                                } // end if( $checkDate > $startYMD ) {    // date after dtstart
                                $rstart = mktime(date('H', $rstart), date('i', $rstart), date('s', $rstart), date('m', $rstart), date('d', $rstart) + 1, date('Y', $rstart)); // step one day
                            } // end while( $rstart <= $rend )
                            $xRecurrence += 1;
                        } // end elseif( $split )
                        elseif ($rstart >= $startDate) {     // date within period   //* flat=FALSE && split=FALSE => one comp every recur startdate *//
                            $checkDate = mktime(0, 0, 0, date('m', $rstart), date('d', $rstart), date('Y', $rstart)); // on a day-basis !!!
                            if (!isset($exdatelist[$checkDate])) { // exclude any recurrence START date, found in exdatelist
                                $xRecurrence += 1;
                                $datestring = date($startDateFormat, $rstart);
                                if (isset($start['tz'])) {
                                    $datestring .= ' ' . $start['tz'];
                                }
                                //echo "X-CURRENT-DTSTART 2 = $datestring xRecurrence=$xRecurrence tcnt =".++$tcnt."<br />";$component2->setProperty( 'X-CNT', $tcnt ); // test ###
                                $component2->setProperty('X-CURRENT-DTSTART', $datestring);
                                if ($dtendExist || $dueExist || $durationExist) {
                                    $tend = $rstart + $rdurWsecs;
                                    if (date('Ymd', $tend) < date('Ymd', $endWdate)) {
                                        $tend = mktime(23, 59, 59, date('m', $tend), date('d', $tend), date('Y', $tend));
                                    } else {
                                        $tend = mktime(date('H', $endWdate), date('i', $endWdate), date('s', $endWdate), date('m', $tend), date('d', $tend), date('Y', $tend));
                                    } // on a day-basis !!!
                                    if ($endAllDayEvent && $dtendExist) {
                                        $tend += (24 * 3600);
                                    } // alldaysevents has an end date 'day after' meaning this day
                                    $datestring = date($endDateFormat, $tend);
                                    if (isset($end['tz'])) {
                                        $datestring .= ' ' . $end['tz'];
                                    }
                                    $propName = (!$dueExist) ? 'X-CURRENT-DTEND' : 'X-CURRENT-DUE';
                                    $component2->setProperty($propName, $datestring);
                                } // end if( $dtendExist || $dueExist || $durationExist )
                                $component2->setProperty('X-RECURRENCE', $xRecurrence);
                                $wd = getdate($rstart);
                                $result[$wd['year']][$wd['mon']][$wd['mday']][$compUID] = $component2->copy(); // copy to output
                            } // end if( !isset( $exdatelist[$checkDate] ))
                        } // end elseif( $rstart >= $startDate )
                    } // end foreach( $recurlist as $recurkey => $durvalue )
                } // end if( 0 < count( $recurlist ))
                /* deselect components with startdate/enddate not within period */
                if (($endWdate < $startDate) || ($startWdate > $endDate)) {
                    continue;
                }
            } // end if( TRUE === $any )
        } // end foreach ( $this->components as $cix => $component )
        if (0 >= count($result)) {
            return false;
        } elseif (!$flat) {
            foreach ($result as $y => $yeararr) {
                foreach ($yeararr as $m => $montharr) {
                    foreach ($montharr as $d => $dayarr) {
                        if (empty($result[$y][$m][$d])) {
                            unset($result[$y][$m][$d]);
                        } else {
                            $result[$y][$m][$d] = array_values($dayarr);
                        } // skip tricky UID-index, hoping they are in hour order.. .
                    }
                    if (empty($result[$y][$m])) {
                        unset($result[$y][$m]);
                    } else {
                        ksort($result[$y][$m]);
                    }
                }
                if (empty($result[$y])) {
                    unset($result[$y]);
                } else {
                    ksort($result[$y]);
                }
            }
            if (empty($result)) {
                unset($result);
            } else {
                ksort($result);
            }
        } // end elseif( !$flat )
        if (0 >= count($result)) {
            return false;
        }
        return $result;
    }

    /**
     * select components from calendar on based on Categories, Location, Resources and/or Summary
     *
     * @param array $selectOptions , (string) key => (mixed) value, (key=propertyName)
     * @return array
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.8.8 - 2011-05-03
     */
    function selectComponents2($selectOptions)
    {
        $output = [];
        $allowedProperties = ['ATTENDEE', 'CATEGORIES', 'LOCATION', 'ORGANIZER', 'RESOURCES', 'PRIORITY', 'STATUS', 'SUMMARY', 'UID'];
        foreach ($this->components as $cix => $component3) {
            if (!in_array($component3->objName, ['vevent', 'vtodo', 'vjournal', 'vfreebusy'])) {
                continue;
            }
            $uid = $component3->getProperty('UID');
            foreach ($selectOptions as $propName => $pvalue) {
                $propName = strtoupper($propName);
                if (!in_array($propName, $allowedProperties)) {
                    continue;
                }
                if (!is_array($pvalue)) {
                    $pvalue = [$pvalue];
                }
                if (('UID' == $propName) && in_array($uid, $pvalue)) {
                    $output[] = $component3->copy();
                    continue;
                } elseif (('ATTENDEE' == $propName) || ('CATEGORIES' == $propName) || ('RESOURCES' == $propName)) {
                    $propValues = [];
                    $component3->_getProperties($propName, $propValues);
                    $propValues = array_keys($propValues);
                    foreach ($pvalue as $theValue) {
                        if (in_array($theValue, $propValues) && !isset($output[$uid])) {
                            $output[$uid] = $component3->copy();
                            break;
                        }
                    }
                    continue;
                } // end   elseif(( 'ATTENDEE' == $propName ) || ( 'CATEGORIES' == $propName ) || ( 'RESOURCES' == $propName ))
                elseif (false === ($d = $component3->getProperty($propName))) // single ocurrence
                {
                    continue;
                }
                if (is_array($d)) {
                    foreach ($d as $part) {
                        if (in_array($part, $pvalue) && !isset($output[$uid])) {
                            $output[$uid] = $component3->copy();
                        }
                    }
                } elseif (('SUMMARY' == $propName) && !isset($output[$uid])) {
                    foreach ($pvalue as $pval) {
                        if (false !== stripos($d, $pval)) {
                            $output[$uid] = $component3->copy();
                            break;
                        }
                    }
                } elseif (in_array($d, $pvalue) && !isset($output[$uid])) {
                    $output[$uid] = $component3->copy();
                }
            } // end foreach( $selectOptions as $propName => $pvalue ) {
        } // end foreach( $this->components as $cix => $component3 ) {
        if (!empty($output)) {
            ksort($output);
            $output = array_values($output);
        }
        return $output;
    }

    /**
     * add calendar component to container
     *
     * @param object $component calendar component
     * @param mixed $arg1 optional, ordno/component type/ component uid
     * @param mixed $arg2 optional, ordno if arg1 = component type
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.8.8 - 2011-03-15
     */
    function setComponent($component, $arg1 = false, $arg2 = false)
    {
        $component->setConfig($this->getConfig(), false, true);
        if (!in_array($component->objName, ['valarm', 'vtimezone'])) {
            /* make sure dtstamp and uid is set */
            $dummy1 = $component->getProperty('dtstamp');
            $dummy2 = $component->getProperty('uid');
        }
        if (!$arg1) { // plain insert, last in chain
            $this->components[] = $component->copy();
            return true;
        }
        $argType = $index = null;
        if (ctype_digit((string)$arg1)) { // index insert/replace
            $argType = 'INDEX';
            $index = (int)$arg1 - 1;
        } elseif (in_array(strtolower($arg1), ['vevent', 'vtodo', 'vjournal', 'vfreebusy', 'valarm', 'vtimezone'])) {
            $argType = strtolower($arg1);
            $index = (ctype_digit((string)$arg2)) ? ((int)$arg2) - 1 : 0;
        }
        // else if arg1 is set, arg1 must be an UID
        $cix1sC = 0;
        foreach ($this->components as $cix => $component2) {
            if (empty($component2)) {
                continue;
            }
            if (('INDEX' == $argType) && ($index == $cix)) { // index insert/replace
                $this->components[$cix] = $component->copy();
                return true;
            } elseif ($argType == $component2->objName) { // component Type index insert/replace
                if ($index == $cix1sC) {
                    $this->components[$cix] = $component->copy();
                    return true;
                }
                $cix1sC++;
            } elseif (!$argType && ($arg1 == $component2->getProperty('uid'))) { // UID insert/replace
                $this->components[$cix] = $component->copy();
                return true;
            }
        }
        /* arg1=index and not found.. . insert at index .. .*/
        if ('INDEX' == $argType) {
            $this->components[$index] = $component->copy();
            ksort($this->components, SORT_NUMERIC);
        } else    /* not found.. . insert last in chain anyway .. .*/ {
            $this->components[] = $component->copy();
        }
        return true;
    }

    /**
     * sort iCal compoments
     *
     * ascending sort on properties (if exist) x-current-dtstart, dtstart,
     * x-current-dtend, dtend, x-current-due, due, duration, created, dtstamp, uid
     * if no arguments, otherwise sorting on argument CATEGORIES, LOCATION, SUMMARY or RESOURCES
     *
     * @param string $sortArg , optional
     * @return void
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.8.4 - 2011-06-02
     */
    function sort($sortArg = false)
    {
        if (is_array($this->components)) {
            if ($sortArg) {
                $sortArg = strtoupper($sortArg);
                if (!in_array($sortArg, ['ATTENDEE', 'CATEGORIES', 'DTSTAMP', 'LOCATION', 'ORGANIZER', 'RESOURCES', 'PRIORITY', 'STATUS', 'SUMMARY'])) {
                    $sortArg = false;
                }
            }
            /* set sort parameters for each component */
            foreach ($this->components as $cix => & $c) {
                $c->srtk = ['0', '0', '0', '0'];
                if ('vtimezone' == $c->objName) {
                    if (false === ($c->srtk[0] = $c->getProperty('tzid'))) {
                        $c->srtk[0] = 0;
                    }
                    continue;
                } elseif ($sortArg) {
                    if (('ATTENDEE' == $sortArg) || ('CATEGORIES' == $sortArg) || ('RESOURCES' == $sortArg)) {
                        $propValues = [];
                        $c->_getProperties($sortArg, $propValues);
                        $c->srtk[0] = reset(array_keys($propValues));
                    } elseif (false !== ($d = $c->getProperty($sortArg))) {
                        $c->srtk[0] = $d;
                    }
                    continue;
                }
                if (false !== ($d = $c->getProperty('X-CURRENT-DTSTART'))) {
                    $c->srtk[0] = iCalUtilityFunctions::_date_time_string($d[1]);
                    unset($c->srtk[0]['unparsedtext']);
                } elseif (false === ($c->srtk[0] = $c->getProperty('dtstart'))) {
                    $c->srtk[1] = 0;
                }                                                  // sortkey 0 : dtstart
                if (false !== ($d = $c->getProperty('X-CURRENT-DTEND'))) {
                    $c->srtk[1] = iCalUtilityFunctions::_date_time_string($d[1]);   // sortkey 1 : dtend/due(/dtstart+duration)
                    unset($c->srtk[1]['unparsedtext']);
                } elseif (false === ($c->srtk[1] = $c->getProperty('dtend'))) {
                    if (false !== ($d = $c->getProperty('X-CURRENT-DUE'))) {
                        $c->srtk[1] = iCalUtilityFunctions::_date_time_string($d[1]);
                        unset($c->srtk[1]['unparsedtext']);
                    } elseif (false === ($c->srtk[1] = $c->getProperty('due'))) {
                        if (false === ($c->srtk[1] = $c->getProperty('duration', false, false, true))) {
                            $c->srtk[1] = 0;
                        }
                    }
                }
                if (false === ($c->srtk[2] = $c->getProperty('created')))      // sortkey 2 : created/dtstamp
                {
                    if (false === ($c->srtk[2] = $c->getProperty('dtstamp'))) {
                        $c->srtk[2] = 0;
                    }
                }
                if (false === ($c->srtk[3] = $c->getProperty('uid')))          // sortkey 3 : uid
                {
                    $c->srtk[3] = 0;
                }
            } // end foreach( $this->components as & $c
            /* sort */
            usort($this->components, [$this, '_cmpfcn']);
        }
    }

    function _cmpfcn($a, $b)
    {
        if (empty($a)) {
            return -1;
        }
        if (empty($b)) {
            return 1;
        }
        if ('vtimezone' == $a->objName) {
            if ('vtimezone' != $b->objName) {
                return -1;
            } elseif ($a->srtk[0] <= $b->srtk[0]) {
                return -1;
            } else {
                return 1;
            }
        } elseif ('vtimezone' == $b->objName) {
            return 1;
        }
        $sortkeys = ['year', 'month', 'day', 'hour', 'min', 'sec'];
        for ($k = 0; $k < 4; $k++) {
            if (empty($a->srtk[$k])) {
                return -1;
            } elseif (empty($b->srtk[$k])) {
                return 1;
            }
            if (is_array($a->srtk[$k])) {
                if (is_array($b->srtk[$k])) {
                    foreach ($sortkeys as $key) {
                        if (empty($a->srtk[$k][$key])) {
                            return -1;
                        } elseif (empty($b->srtk[$k][$key])) {
                            return 1;
                        }
                        if ($a->srtk[$k][$key] == $b->srtk[$k][$key]) {
                            continue;
                        }
                        if (((int)$a->srtk[$k][$key]) < ((int)$b->srtk[$k][$key])) {
                            return -1;
                        } elseif (((int)$a->srtk[$k][$key]) > ((int)$b->srtk[$k][$key])) {
                            return 1;
                        }
                    }
                } else {
                    return -1;
                }
            } elseif (is_array($b->srtk[$k])) {
                return 1;
            } elseif ($a->srtk[$k] < $b->srtk[$k]) {
                return -1;
            } elseif ($a->srtk[$k] > $b->srtk[$k]) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * parse iCal text/file into vcalendar, components, properties and parameters
     *
     * @param mixed $unparsedtext , optional, strict rfc2445 formatted, single property string or array of property strings
     * @return bool FALSE if error occurs during parsing
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.10 - 2012-01-31
     */
    function parse($unparsedtext = false)
    {
        $nl = $this->getConfig('nl');
        if ((false === $unparsedtext) || empty($unparsedtext)) {
            /* directory+filename is set previously via setConfig directory+filename or url */
            if (false === ($filename = $this->getConfig('url'))) {
                $filename = $this->getConfig('dirfile');
            }
            /* READ FILE */
            if (false === ($rows = file_get_contents($filename))) {
                return false;
            }                 /* err 1 */
        } elseif (is_array($unparsedtext)) {
            $rows = implode('\n' . $nl, $unparsedtext);
        } else {
            $rows = &$unparsedtext;
        }
        /* identify BEGIN:VCALENDAR, MUST be first row */
        if ('BEGIN:VCALENDAR' != strtoupper(substr($rows, 0, 15))) {
            return false;
        }                   /* err 8 */
        /* fix line folding */
        $eolchars = ["\r\n", "\n\r", "\n", "\r"]; // check all line endings
        $EOLmark = false;
        foreach ($eolchars as $eolchar) {
            if (!$EOLmark && (false !== strpos($rows, $eolchar))) {
                $rows = str_replace($eolchar . " ", '', $rows);
                $rows = str_replace($eolchar . "\t", '', $rows);
                if ($eolchar != $nl) {
                    $rows = str_replace($eolchar, $nl, $rows);
                }
                $EOLmark = true;
            }
        }
        $rows = explode($nl, $rows);
        /* skip trailing empty lines */
        $lix = count($rows) - 1;
        while (empty($rows[$lix]) && (0 < $lix)) {
            $lix -= 1;
        }
        /* identify ending END:VCALENDAR row, MUST be last row */
        if ('END:VCALENDAR' != strtoupper(substr($rows[$lix], 0, 13))) {
            return false;
        }                   /* err 9 */
        if (3 > count($rows)) {
            return false;
        }                   /* err 10 */
        $comp = &$this;
        $calsync = 0;
        /* identify components and update unparsed data within component */
        $config = $this->getConfig();
        foreach ($rows as $line) {
            if ('BEGIN:VCALENDAR' == strtoupper(substr($line, 0, 15))) {
                $calsync++;
                continue;
            } elseif ('END:VCALENDAR' == strtoupper(substr($line, 0, 13))) {
                $calsync--;
                break;
            } elseif (1 != $calsync) {
                return false;
            }                 /* err 20 */
            elseif (in_array(strtoupper(substr($line, 0, 6)), ['END:VE', 'END:VF', 'END:VJ', 'END:VT'])) {
                $this->components[] = $comp->copy();
                continue;
            }
            if ('BEGIN:VEVENT' == strtoupper(substr($line, 0, 12))) {
                $comp = new vevent($config);
            } elseif ('BEGIN:VFREEBUSY' == strtoupper(substr($line, 0, 15))) {
                $comp = new vfreebusy($config);
            } elseif ('BEGIN:VJOURNAL' == strtoupper(substr($line, 0, 14))) {
                $comp = new vjournal($config);
            } elseif ('BEGIN:VTODO' == strtoupper(substr($line, 0, 11))) {
                $comp = new vtodo($config);
            } elseif ('BEGIN:VTIMEZONE' == strtoupper(substr($line, 0, 15))) {
                $comp = new vtimezone($config);
            } else { /* update component with unparsed data */
                $comp->unparsed[] = $line;
            }
        } // end foreach( $rows as $line )
        unset($config);
        /* parse data for calendar (this) object */
        if (isset($this->unparsed) && is_array($this->unparsed) && (0 < count($this->unparsed))) {
            /* concatenate property values spread over several lines */
            $lastix = -1;
            $propnames = ['calscale', 'method', 'prodid', 'version', 'x-'];
            $proprows = [];
            foreach ($this->unparsed as $line) {
                $newProp = false;
                foreach ($propnames as $propname) {
                    if ($propname == strtolower(substr($line, 0, strlen($propname)))) {
                        $newProp = true;
                        break;
                    }
                }
                if ($newProp) {
                    $newProp = false;
                    $lastix++;
                    $proprows[$lastix] = $line;
                } else {
                    $proprows[$lastix] .= '!"#¤%&/()=?' . $line;
                }
            }
            $paramMStz = ['utc-', 'utc+', 'gmt-', 'gmt+'];
            $paramProto3 = ['fax:', 'cid:', 'sms:', 'tel:', 'urn:'];
            $paramProto4 = ['crid:', 'news:', 'pres:'];
            foreach ($proprows as $line) {
                $line = str_replace('!"#¤%&/()=? ', '', $line);
                $line = str_replace('!"#¤%&/()=?', '', $line);
                if ('\n' == substr($line, -2)) {
                    $line = substr($line, 0, strlen($line) - 2);
                }
                /* get property name */
                $cix = $propname = null;
                for ($cix = 0, $clen = strlen($line); $cix < $clen; $cix++) {
                    if (in_array($line[$cix], [':', ';'])) {
                        break;
                    } else {
                        $propname .= $line[$cix];
                    }
                }
                /* ignore version/prodid properties */
                if (in_array(strtoupper($propname), ['VERSION', 'PRODID'])) {
                    continue;
                }
                $line = substr($line, $cix);
                /* separate attributes from value */
                $attr = [];
                $attrix = -1;
                $strlen = strlen($line);
                $WithinQuotes = false;
                for ($cix = 0; $cix < $strlen; $cix++) {
                    if ((':' == $line[$cix]) &&
                        (substr($line, $cix, 3) != '://') &&
                        (!in_array(strtolower(substr($line, $cix - 6, 4)), $paramMStz)) &&
                        (!in_array(strtolower(substr($line, $cix - 3, 4)), $paramProto3)) &&
                        (!in_array(strtolower(substr($line, $cix - 4, 5)), $paramProto4)) &&
                        (strtolower(substr($line, $cix - 6, 7)) != 'mailto:') &&
                        !$WithinQuotes) {
                        $attrEnd = true;
                        if (($cix < ($strlen - 4)) &&
                            ctype_digit(substr($line, $cix + 1, 4))) { // an URI with a (4pos) portnr??
                            for ($c2ix = $cix; 3 < $c2ix; $c2ix--) {
                                if ('://' == substr($line, $c2ix - 2, 3)) {
                                    $attrEnd = false;
                                    break; // an URI with a portnr!!
                                }
                            }
                        }
                        if ($attrEnd) {
                            $line = substr($line, ($cix + 1));
                            break;
                        }
                    }
                    if ('"' == $line[$cix]) {
                        $WithinQuotes = (false === $WithinQuotes) ? true : false;
                    }
                    if (';' == $line[$cix]) {
                        $attr[++$attrix] = null;
                    } else {
                        $attr[$attrix] .= $line[$cix];
                    }
                }
                /* make attributes in array format */
                $propattr = [];
                foreach ($attr as $attribute) {
                    $attrsplit = explode('=', $attribute, 2);
                    if (1 < count($attrsplit)) {
                        $propattr[$attrsplit[0]] = $attrsplit[1];
                    } else {
                        $propattr[] = $attribute;
                    }
                }
                /* update Property */
                if (false !== strpos($line, ',')) {
                    $llen = strlen($line);
                    $content = [0 => ''];
                    $cix = 0;
                    for ($lix = 0; $lix < $llen; $lix++) {
                        if ((',' == $line[$lix]) && ("\\" != $line[($lix - 1)])) {
                            $cix++;
                            $content[$cix] = '';
                        } else {
                            $content[$cix] .= $line[$lix];
                        }
                    }
                    if (1 < count($content)) {
                        foreach ($content as $cix => $contentPart) {
                            $content[$cix] = calendarComponent::_strunrep($contentPart);
                        }
                        $this->setProperty($propname, $content, $propattr);
                        continue;
                    } else {
                        $line = reset($content);
                    }
                    $line = calendarComponent::_strunrep($line);
                }
                $this->setProperty($propname, rtrim($line, "\x00..\x1F"), $propattr);
            } // end - foreach( $this->unparsed.. .
        } // end - if( is_array( $this->unparsed.. .
        unset($unparsedtext, $rows, $this->unparsed, $proprows);
        /* parse Components */
        if (is_array($this->components) && (0 < count($this->components))) {
            $ckeys = array_keys($this->components);
            foreach ($ckeys as $ckey) {
                if (!empty($this->components[$ckey]) && !empty($this->components[$ckey]->unparsed)) {
                    $this->components[$ckey]->parse();
                }
            }
        } else {
            return false;
        }                   /* err 91 or something.. . */
        return true;
    }
    /*********************************************************************************/
    /**
     * creates formatted output for calendar object instance
     *
     * @return string
     * @since 2.10.16 - 2011-10-28
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createCalendar()
    {
        $calendarInit = $calendarxCaldecl = $calendarStart = $calendar = '';
        switch ($this->format) {
            case 'xcal':
                $calendarInit = '<?xml version="1.0" encoding="UTF-8"?>' . $this->nl .
                    '<!DOCTYPE vcalendar PUBLIC "-//IETF//DTD XCAL/iCalendar XML//EN"' . $this->nl .
                    '"http://www.ietf.org/internet-drafts/draft-ietf-calsch-many-xcal-01.txt"';
                $calendarStart = '>' . $this->nl . '<vcalendar';
                break;
            default:
                $calendarStart = 'BEGIN:VCALENDAR' . $this->nl;
                break;
        }
        $calendarStart .= $this->createVersion();
        $calendarStart .= $this->createProdid();
        $calendarStart .= $this->createCalscale();
        $calendarStart .= $this->createMethod();
        if ('xcal' == $this->format) {
            $calendarStart .= '>' . $this->nl;
        }
        $calendar .= $this->createXprop();

        foreach ($this->components as $component) {
            if (empty($component)) {
                continue;
            }
            $component->setConfig($this->getConfig(), false, true);
            $calendar .= $component->createComponent($this->xcaldecl);
        }
        if (('xcal' == $this->format) && (0 < count($this->xcaldecl))) { // xCal only
            $calendarInit .= ' [';
            $old_xcaldecl = [];
            foreach ($this->xcaldecl as $declix => $declPart) {
                if ((0 < count($old_xcaldecl)) &&
                    isset($declPart['uri']) && isset($declPart['external']) &&
                    isset($old_xcaldecl['uri']) && isset($old_xcaldecl['external']) &&
                    (in_array($declPart['uri'], $old_xcaldecl['uri'])) &&
                    (in_array($declPart['external'], $old_xcaldecl['external']))) {
                    continue;
                } // no duplicate uri and ext. references
                if ((0 < count($old_xcaldecl)) &&
                    !isset($declPart['uri']) && !isset($declPart['uri']) &&
                    isset($declPart['ref']) && isset($old_xcaldecl['ref']) &&
                    (in_array($declPart['ref'], $old_xcaldecl['ref']))) {
                    continue;
                } // no duplicate element declarations
                $calendarxCaldecl .= $this->nl . '<!';
                foreach ($declPart as $declKey => $declValue) {
                    switch ($declKey) {                    // index
                        case 'xmldecl':                       // no 1
                            $calendarxCaldecl .= $declValue . ' ';
                            break;
                        case 'uri':                           // no 2
                            $calendarxCaldecl .= $declValue . ' ';
                            $old_xcaldecl['uri'][] = $declValue;
                            break;
                        case 'ref':                           // no 3
                            $calendarxCaldecl .= $declValue . ' ';
                            $old_xcaldecl['ref'][] = $declValue;
                            break;
                        case 'external':                      // no 4
                            $calendarxCaldecl .= '"' . $declValue . '" ';
                            $old_xcaldecl['external'][] = $declValue;
                            break;
                        case 'type':                          // no 5
                            $calendarxCaldecl .= $declValue . ' ';
                            break;
                        case 'type2':                         // no 6
                            $calendarxCaldecl .= $declValue;
                            break;
                    }
                }
                $calendarxCaldecl .= '>';
            }
            $calendarxCaldecl .= $this->nl . ']';
        }
        switch ($this->format) {
            case 'xcal':
                $calendar .= '</vcalendar>' . $this->nl;
                break;
            default:
                $calendar .= 'END:VCALENDAR' . $this->nl;
                break;
        }
        return $calendarInit . $calendarxCaldecl . $calendarStart . $calendar;
    }

    /**
     * a HTTP redirect header is sent with created, updated and/or parsed calendar
     *
     * @param bool $utf8Encode
     * @param bool $gzip
     * @return redirect
     * @since 2.10.24 - 2011-12-23
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function returnCalendar($utf8Encode = false, $gzip = false)
    {
        $filename = $this->getConfig('filename');
        $output = $this->createCalendar();
        if ($utf8Encode) {
            $output = utf8_encode($output);
        }
        if ($gzip) {
            $output = gzencode($output, 9);
            header('Content-Encoding: gzip');
            header('Vary: *');
            header('Content-Length: ' . strlen($output));
        }
        if ('xcal' == $this->format) {
            header('Content-Type: application/calendar+xml; charset=utf-8');
        } else {
            header('Content-Type: text/calendar; charset=utf-8');
        }
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=10');
        die($output);
    }

    /**
     * save content in a file
     *
     * @param string $directory optional
     * @param string $filename optional
     * @param string $delimiter optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.2.12 - 2007-12-30
     */
    function saveCalendar($directory = false, $filename = false, $delimiter = false)
    {
        if ($directory) {
            $this->setConfig('directory', $directory);
        }
        if ($filename) {
            $this->setConfig('filename', $filename);
        }
        if ($delimiter && ($delimiter != DIRECTORY_SEPARATOR)) {
            $this->setConfig('delimiter', $delimiter);
        }
        if (false === ($dirfile = $this->getConfig('url'))) {
            $dirfile = $this->getConfig('dirfile');
        }
        $iCalFile = @fopen($dirfile, 'w');
        if ($iCalFile) {
            if (false === fwrite($iCalFile, $this->createCalendar())) {
                return false;
            }
            fclose($iCalFile);
            return true;
        } else {
            return false;
        }
    }

    /**
     * if recent version of calendar file exists (default one hour), an HTTP redirect header is sent
     * else FALSE is returned
     *
     * @param string $directory optional alt. int timeout
     * @param string $filename optional
     * @param string $delimiter optional
     * @param int timeout optional, default 3600 sec
     * @return redirect/FALSE
     * @since 2.2.12 - 2007-10-28
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function useCachedCalendar($directory = false, $filename = false, $delimiter = false, $timeout = 3600)
    {
        if ($directory && ctype_digit((string)$directory) && !$filename) {
            $timeout = (int)$directory;
            $directory = false;
        }
        if ($directory) {
            $this->setConfig('directory', $directory);
        }
        if ($filename) {
            $this->setConfig('filename', $filename);
        }
        if ($delimiter && ($delimiter != DIRECTORY_SEPARATOR)) {
            $this->setConfig('delimiter', $delimiter);
        }
        $filesize = $this->getConfig('filesize');
        if (0 >= $filesize) {
            return false;
        }
        $dirfile = $this->getConfig('dirfile');
        if (time() - filemtime($dirfile) < $timeout) {
            clearstatcache();
            $dirfile = $this->getConfig('dirfile');
            $filename = $this->getConfig('filename');
            //    if( headers_sent( $filename, $linenum ))
            //      die( "Headers already sent in $filename on line $linenum\n" );
            if ('xcal' == $this->format) {
                header('Content-Type: application/calendar+xml; charset=utf-8');
            } else {
                header('Content-Type: text/calendar; charset=utf-8');
            }
            header('Content-Length: ' . $filesize);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=10');
            $fp = @fopen($dirfile, 'r');
            if ($fp) {
                fpassthru($fp);
                fclose($fp);
            }
            die();
        } else {
            return false;
        }
    }
}

/*********************************************************************************/
/*********************************************************************************/

/**
 *  abstract class for calendar components
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.9.6 - 2011-05-14
 */
class calendarComponent
{
    //  component property variables
    var $uid;
    var $dtstamp;

    //  component config variables
    var $allowEmpty;
    var $language;
    var $nl;
    var $unique_id;
    var $format;
    var $objName; // created automatically at instance creation
    var $dtzid;   // default (local) timezone
    //  component internal variables
    var $componentStart1;
    var $componentStart2;
    var $componentEnd1;
    var $componentEnd2;
    var $elementStart1;
    var $elementStart2;
    var $elementEnd1;
    var $elementEnd2;
    var $intAttrDelimiter;
    var $attributeDelimiter;
    var $valueInit;
    //  component xCal declaration container
    var $xcaldecl;

    /**
     * constructor for calendar component object
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.9.6 - 2011-05-17
     */
    function calendarComponent()
    {
        $this->objName = (isset($this->timezonetype)) ?
            strtolower($this->timezonetype) : get_class($this);
        $this->uid = [];
        $this->dtstamp = [];

        $this->language = null;
        $this->nl = null;
        $this->unique_id = null;
        $this->format = null;
        $this->dtzid = null;
        $this->allowEmpty = true;
        $this->xcaldecl = [];

        $this->_createFormat();
        $this->_makeDtstamp();
    }
    /*********************************************************************************/
    /**
     * Property Name: ACTION
     */
    /**
     * creates formatted output for calendar component property action
     *
     * @return string
     * @since 2.4.8 - 2008-10-22
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createAction()
    {
        if (empty($this->action)) {
            return false;
        }
        if (empty($this->action['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('ACTION') : false;
        }
        $attributes = $this->_createParams($this->action['params']);
        return $this->_createElement('ACTION', $attributes, $this->action['value']);
    }

    /**
     * set calendar component property action
     *
     * @param string $value "AUDIO" / "DISPLAY" / "EMAIL" / "PROCEDURE"
     * @param mixed $params
     * @return bool
     * @since 2.4.8 - 2008-11-04
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setAction($value, $params = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $this->action = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: ATTACH
     */
    /**
     * creates formatted output for calendar component property attach
     *
     * @return string
     * @since 2.11.16 - 2012-02-04
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createAttach()
    {
        if (empty($this->attach)) {
            return false;
        }
        $output = null;
        foreach ($this->attach as $attachPart) {
            if (!empty($attachPart['value'])) {
                $attributes = $this->_createParams($attachPart['params']);
                if (('xcal' != $this->format) && isset($attachPart['params']['VALUE']) && ('BINARY' == $attachPart['params']['VALUE'])) {
                    $attributes = str_replace($this->intAttrDelimiter, $this->attributeDelimiter, $attributes);
                    $str = 'ATTACH' . $attributes . $this->valueInit . $attachPart['value'];
                    $output = substr($str, 0, 75) . $this->nl;
                    $str = substr($str, 75);
                    $output .= ' ' . chunk_split($str, 74, $this->nl . ' ');
                    if (' ' == substr($output, -1)) {
                        $output = rtrim($output);
                    }
                    if ($this->nl != substr($output, (0 - strlen($this->nl)))) {
                        $output .= $this->nl;
                    }
                    return $output;
                }
                $output .= $this->_createElement('ATTACH', $attributes, $attachPart['value']);
            } elseif ($this->getConfig('allowEmpty')) {
                $output .= $this->_createElement('ATTACH');
            }
        }
        return $output;
    }

    /**
     * set calendar component property attach
     *
     * @param string $value
     * @param array $params , optional
     * @param integer $index , optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.5.1 - 2008-11-06
     */
    function setAttach($value, $params = false, $index = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        iCalUtilityFunctions::_setMval($this->attach, $value, $params, false, $index);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: ATTENDEE
     */
    /**
     * creates formatted output for calendar component property attendee
     *
     * @return string
     * @since 2.11.12 - 2012-01-31
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createAttendee()
    {
        if (empty($this->attendee)) {
            return false;
        }
        $output = null;
        foreach ($this->attendee as $attendeePart) {                      // start foreach 1
            if (empty($attendeePart['value'])) {
                if ($this->getConfig('allowEmpty')) {
                    $output .= $this->_createElement('ATTENDEE');
                }
                continue;
            }
            $attendee1 = $attendee2 = null;
            foreach ($attendeePart as $paramlabel => $paramvalue) {         // start foreach 2
                if ('value' == $paramlabel) {
                    $attendee2 .= $paramvalue;
                } elseif (('params' == $paramlabel) && (is_array($paramvalue))) { // start elseif
                    $mParams = ['MEMBER', 'DELEGATED-TO', 'DELEGATED-FROM'];
                    foreach ($paramvalue as $pKey => $pValue) {                 // fix (opt) quotes
                        if (is_array($pValue) || in_array($pKey, $mParams)) {
                            continue;
                        }
                        if ((false !== strpos($pValue, ':')) ||
                            (false !== strpos($pValue, ';')) ||
                            (false !== strpos($pValue, ','))) {
                            $paramvalue[$pKey] = '"' . $pValue . '"';
                        }
                    }
                    // set attenddee parameters in rfc2445 order
                    if (isset($paramvalue['CUTYPE'])) {
                        $attendee1 .= $this->intAttrDelimiter . 'CUTYPE=' . $paramvalue['CUTYPE'];
                    }
                    if (isset($paramvalue['MEMBER'])) {
                        $attendee1 .= $this->intAttrDelimiter . 'MEMBER=';
                        foreach ($paramvalue['MEMBER'] as $cix => $opv) {
                            $attendee1 .= ($cix) ? ',"' . $opv . '"' : '"' . $opv . '"';
                        }
                    }
                    if (isset($paramvalue['ROLE'])) {
                        $attendee1 .= $this->intAttrDelimiter . 'ROLE=' . $paramvalue['ROLE'];
                    }
                    if (isset($paramvalue['PARTSTAT'])) {
                        $attendee1 .= $this->intAttrDelimiter . 'PARTSTAT=' . $paramvalue['PARTSTAT'];
                    }
                    if (isset($paramvalue['RSVP'])) {
                        $attendee1 .= $this->intAttrDelimiter . 'RSVP=' . $paramvalue['RSVP'];
                    }
                    if (isset($paramvalue['DELEGATED-TO'])) {
                        $attendee1 .= $this->intAttrDelimiter . 'DELEGATED-TO=';
                        foreach ($paramvalue['DELEGATED-TO'] as $cix => $opv) {
                            $attendee1 .= ($cix) ? ',"' . $opv . '"' : '"' . $opv . '"';
                        }
                    }
                    if (isset($paramvalue['DELEGATED-FROM'])) {
                        $attendee1 .= $this->intAttrDelimiter . 'DELEGATED-FROM=';
                        foreach ($paramvalue['DELEGATED-FROM'] as $cix => $opv) {
                            $attendee1 .= ($cix) ? ',"' . $opv . '"' : '"' . $opv . '"';
                        }
                    }
                    if (isset($paramvalue['SENT-BY'])) {
                        $attendee1 .= $this->intAttrDelimiter . 'SENT-BY=' . $paramvalue['SENT-BY'];
                    }
                    if (isset($paramvalue['CN'])) {
                        $attendee1 .= $this->intAttrDelimiter . 'CN=' . $paramvalue['CN'];
                    }
                    if (isset($paramvalue['DIR'])) {
                        $delim = (false === strpos($paramvalue['DIR'], '"')) ? '"' : '';
                        $attendee1 .= $this->intAttrDelimiter . 'DIR=' . $delim . $paramvalue['DIR'] . $delim;
                    }
                    if (isset($paramvalue['LANGUAGE'])) {
                        $attendee1 .= $this->intAttrDelimiter . 'LANGUAGE=' . $paramvalue['LANGUAGE'];
                    }
                    $xparams = [];
                    foreach ($paramvalue as $optparamlabel => $optparamvalue) { // start foreach 3
                        if (ctype_digit((string)$optparamlabel)) {
                            $xparams[] = $optparamvalue;
                            continue;
                        }
                        if (!in_array($optparamlabel, ['CUTYPE', 'MEMBER', 'ROLE', 'PARTSTAT', 'RSVP', 'DELEGATED-TO', 'DELEGATED-FROM', 'SENT-BY', 'CN', 'DIR', 'LANGUAGE'])) {
                            $xparams[$optparamlabel] = $optparamvalue;
                        }
                    } // end foreach 3
                    ksort($xparams, SORT_STRING);
                    foreach ($xparams as $paramKey => $paramValue) {
                        if (ctype_digit((string)$paramKey)) {
                            $attendee1 .= $this->intAttrDelimiter . $paramValue;
                        } else {
                            $attendee1 .= $this->intAttrDelimiter . "$paramKey=$paramValue";
                        }
                    }      // end foreach 3
                }        // end elseif(( 'params' == $paramlabel ) && ( is_array( $paramvalue )))
            }          // end foreach 2
            $output .= $this->_createElement('ATTENDEE', $attendee1, $attendee2);
        }              // end foreach 1
        return $output;
    }

    /**
     * set calendar component property attach
     *
     * @param string $value
     * @param array $params , optional
     * @param integer $index , optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.17 - 2012-02-03
     */
    function setAttendee($value, $params = false, $index = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        // ftp://, http://, mailto:, file://, gopher://, news:, nntp://, telnet://, wais://, prospero://  may exist.. . also in params
        if (false !== ($pos = strpos(substr($value, 0, 9), ':'))) {
            $value = strtoupper(substr($value, 0, $pos)) . substr($value, $pos);
        } elseif (!empty($value)) {
            $value = 'MAILTO:' . $value;
        }
        $params2 = [];
        if (is_array($params)) {
            $optarrays = [];
            foreach ($params as $optparamlabel => $optparamvalue) {
                $optparamlabel = strtoupper($optparamlabel);
                switch ($optparamlabel) {
                    case 'MEMBER':
                    case 'DELEGATED-TO':
                    case 'DELEGATED-FROM':
                        if (!is_array($optparamvalue)) {
                            $optparamvalue = [$optparamvalue];
                        }
                        foreach ($optparamvalue as $part) {
                            $part = trim($part);
                            if (('"' == substr($part, 0, 1)) &&
                                ('"' == substr($part, -1))) {
                                $part = substr($part, 1, (strlen($part) - 2));
                            }
                            if ('mailto:' != strtolower(substr($part, 0, 7))) {
                                $part = "MAILTO:$part";
                            } else {
                                $part = 'MAILTO:' . substr($part, 7);
                            }
                            $optarrays[$optparamlabel][] = $part;
                        }
                        break;
                    default:
                        if (('"' == substr($optparamvalue, 0, 1)) &&
                            ('"' == substr($optparamvalue, -1))) {
                            $optparamvalue = substr($optparamvalue, 1, (strlen($optparamvalue) - 2));
                        }
                        if ('SENT-BY' == $optparamlabel) {
                            if ('mailto:' != strtolower(substr($optparamvalue, 0, 7))) {
                                $optparamvalue = "MAILTO:$optparamvalue";
                            } else {
                                $optparamvalue = 'MAILTO:' . substr($optparamvalue, 7);
                            }
                        }
                        $params2[$optparamlabel] = $optparamvalue;
                        break;
                } // end switch( $optparamlabel.. .
            } // end foreach( $optparam.. .
            foreach ($optarrays as $optparamlabel => $optparams) {
                $params2[$optparamlabel] = $optparams;
            }
        }
        // remove defaults
        iCalUtilityFunctions::_existRem($params2, 'CUTYPE', 'INDIVIDUAL');
        iCalUtilityFunctions::_existRem($params2, 'PARTSTAT', 'NEEDS-ACTION');
        iCalUtilityFunctions::_existRem($params2, 'ROLE', 'REQ-PARTICIPANT');
        iCalUtilityFunctions::_existRem($params2, 'RSVP', 'FALSE');
        // check language setting
        if (isset($params2['CN'])) {
            $lang = $this->getConfig('language');
            if (!isset($params2['LANGUAGE']) && !empty($lang)) {
                $params2['LANGUAGE'] = $lang;
            }
        }
        iCalUtilityFunctions::_setMval($this->attendee, $value, $params2, false, $index);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: CATEGORIES
     */
    /**
     * creates formatted output for calendar component property categories
     *
     * @return string
     * @since 2.4.8 - 2008-10-22
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createCategories()
    {
        if (empty($this->categories)) {
            return false;
        }
        $output = null;
        foreach ($this->categories as $category) {
            if (empty($category['value'])) {
                if ($this->getConfig('allowEmpty')) {
                    $output .= $this->_createElement('CATEGORIES');
                }
                continue;
            }
            $attributes = $this->_createParams($category['params'], ['LANGUAGE']);
            if (is_array($category['value'])) {
                foreach ($category['value'] as $cix => $categoryPart) {
                    $category['value'][$cix] = $this->_strrep($categoryPart);
                }
                $content = implode(',', $category['value']);
            } else {
                $content = $this->_strrep($category['value']);
            }
            $output .= $this->_createElement('CATEGORIES', $attributes, $content);
        }
        return $output;
    }

    /**
     * set calendar component property categories
     *
     * @param mixed $value
     * @param array $params , optional
     * @param integer $index , optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.5.1 - 2008-11-06
     */
    function setCategories($value, $params = false, $index = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        iCalUtilityFunctions::_setMval($this->categories, $value, $params, false, $index);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: CLASS
     */
    /**
     * creates formatted output for calendar component property class
     *
     * @return string
     * @since 0.9.7 - 2006-11-20
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createClass()
    {
        if (empty($this->class)) {
            return false;
        }
        if (empty($this->class['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('CLASS') : false;
        }
        $attributes = $this->_createParams($this->class['params']);
        return $this->_createElement('CLASS', $attributes, $this->class['value']);
    }

    /**
     * set calendar component property class
     *
     * @param string $value "PUBLIC" / "PRIVATE" / "CONFIDENTIAL" / iana-token / x-name
     * @param array $params optional
     * @return bool
     * @since 2.4.8 - 2008-11-04
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setClass($value, $params = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $this->class = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: COMMENT
     */
    /**
     * creates formatted output for calendar component property comment
     *
     * @return string
     * @since 2.4.8 - 2008-10-22
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createComment()
    {
        if (empty($this->comment)) {
            return false;
        }
        $output = null;
        foreach ($this->comment as $commentPart) {
            if (empty($commentPart['value'])) {
                if ($this->getConfig('allowEmpty')) {
                    $output .= $this->_createElement('COMMENT');
                }
                continue;
            }
            $attributes = $this->_createParams($commentPart['params'], ['ALTREP', 'LANGUAGE']);
            $content = $this->_strrep($commentPart['value']);
            $output .= $this->_createElement('COMMENT', $attributes, $content);
        }
        return $output;
    }

    /**
     * set calendar component property comment
     *
     * @param string $value
     * @param array $params , optional
     * @param integer $index , optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.5.1 - 2008-11-06
     */
    function setComment($value, $params = false, $index = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        iCalUtilityFunctions::_setMval($this->comment, $value, $params, false, $index);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: COMPLETED
     */
    /**
     * creates formatted output for calendar component property completed
     *
     * @return string
     * @since 2.4.8 - 2008-10-22
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createCompleted()
    {
        if (empty($this->completed)) {
            return false;
        }
        if (!isset($this->completed['value']['year']) &&
            !isset($this->completed['value']['month']) &&
            !isset($this->completed['value']['day']) &&
            !isset($this->completed['value']['hour']) &&
            !isset($this->completed['value']['min']) &&
            !isset($this->completed['value']['sec'])) {
            if ($this->getConfig('allowEmpty')) {
                return $this->_createElement('COMPLETED');
            } else {
                return false;
            }
        }
        $formatted = iCalUtilityFunctions::_format_date_time($this->completed['value'], 7);
        $attributes = $this->_createParams($this->completed['params']);
        return $this->_createElement('COMPLETED', $attributes, $formatted);
    }

    /**
     * set calendar component property completed
     *
     * @param mixed $year
     * @param mixed $month optional
     * @param int $day optional
     * @param int $hour optional
     * @param int $min optional
     * @param int $sec optional
     * @param array $params optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.4.8 - 2008-10-23
     */
    function setCompleted($year, $month = false, $day = false, $hour = false, $min = false, $sec = false, $params = false)
    {
        if (empty($year)) {
            if ($this->getConfig('allowEmpty')) {
                $this->completed = ['value' => null, 'params' => iCalUtilityFunctions::_setParams($params)];
                return true;
            } else {
                return false;
            }
        }
        $this->completed = iCalUtilityFunctions::_setDate2($year, $month, $day, $hour, $min, $sec, $params);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: CONTACT
     */
    /**
     * creates formatted output for calendar component property contact
     *
     * @return string
     * @since 2.4.8 - 2008-10-23
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createContact()
    {
        if (empty($this->contact)) {
            return false;
        }
        $output = null;
        foreach ($this->contact as $contact) {
            if (!empty($contact['value'])) {
                $attributes = $this->_createParams($contact['params'], ['ALTREP', 'LANGUAGE']);
                $content = $this->_strrep($contact['value']);
                $output .= $this->_createElement('CONTACT', $attributes, $content);
            } elseif ($this->getConfig('allowEmpty')) {
                $output .= $this->_createElement('CONTACT');
            }
        }
        return $output;
    }

    /**
     * set calendar component property contact
     *
     * @param string $value
     * @param array $params , optional
     * @param integer $index , optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.5.1 - 2008-11-05
     */
    function setContact($value, $params = false, $index = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        iCalUtilityFunctions::_setMval($this->contact, $value, $params, false, $index);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: CREATED
     */
    /**
     * creates formatted output for calendar component property created
     *
     * @return string
     * @since 2.4.8 - 2008-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createCreated()
    {
        if (empty($this->created)) {
            return false;
        }
        $formatted = iCalUtilityFunctions::_format_date_time($this->created['value'], 7);
        $attributes = $this->_createParams($this->created['params']);
        return $this->_createElement('CREATED', $attributes, $formatted);
    }

    /**
     * set calendar component property created
     *
     * @param mixed $year optional
     * @param mixed $month optional
     * @param int $day optional
     * @param int $hour optional
     * @param int $min optional
     * @param int $sec optional
     * @param mixed $params optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.4.8 - 2008-10-23
     */
    function setCreated($year = false, $month = false, $day = false, $hour = false, $min = false, $sec = false, $params = false)
    {
        if (!isset($year)) {
            $year = date('Ymd\THis', mktime(date('H'), date('i'), date('s') - date('Z'), date('m'), date('d'), date('Y')));
        }
        $this->created = iCalUtilityFunctions::_setDate2($year, $month, $day, $hour, $min, $sec, $params);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: DESCRIPTION
     */
    /**
     * creates formatted output for calendar component property description
     *
     * @return string
     * @since 2.4.8 - 2008-10-22
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createDescription()
    {
        if (empty($this->description)) {
            return false;
        }
        $output = null;
        foreach ($this->description as $description) {
            if (!empty($description['value'])) {
                $attributes = $this->_createParams($description['params'], ['ALTREP', 'LANGUAGE']);
                $content = $this->_strrep($description['value']);
                $output .= $this->_createElement('DESCRIPTION', $attributes, $content);
            } elseif ($this->getConfig('allowEmpty')) {
                $output .= $this->_createElement('DESCRIPTION');
            }
        }
        return $output;
    }

    /**
     * set calendar component property description
     *
     * @param string $value
     * @param array $params , optional
     * @param integer $index , optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.6.24 - 2010-11-06
     */
    function setDescription($value, $params = false, $index = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        if ('vjournal' != $this->objName) {
            $index = 1;
        }
        iCalUtilityFunctions::_setMval($this->description, $value, $params, false, $index);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: DTEND
     */
    /**
     * creates formatted output for calendar component property dtend
     *
     * @return string
     * @since 2.9.6 - 2011-05-14
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createDtend()
    {
        if (empty($this->dtend)) {
            return false;
        }
        if (!isset($this->dtend['value']['year']) &&
            !isset($this->dtend['value']['month']) &&
            !isset($this->dtend['value']['day']) &&
            !isset($this->dtend['value']['hour']) &&
            !isset($this->dtend['value']['min']) &&
            !isset($this->dtend['value']['sec'])) {
            if ($this->getConfig('allowEmpty')) {
                return $this->_createElement('DTEND');
            } else {
                return false;
            }
        }
        $formatted = iCalUtilityFunctions::_format_date_time($this->dtend['value']);
        if ((false !== ($tzid = $this->getConfig('TZID'))) &&
            (!isset($this->dtend['params']['VALUE']) || ($this->dtend['params']['VALUE'] != 'DATE')) &&
            !isset($this->dtend['params']['TZID'])) {
            $this->dtend['params']['TZID'] = $tzid;
        }
        $attributes = $this->_createParams($this->dtend['params']);
        return $this->_createElement('DTEND', $attributes, $formatted);
    }

    /**
     * set calendar component property dtend
     *
     * @param mixed $year
     * @param mixed $month optional
     * @param int $day optional
     * @param int $hour optional
     * @param int $min optional
     * @param int $sec optional
     * @param string $tz optional
     * @param array params optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.9.6 - 2011-05-14
     */
    function setDtend($year, $month = false, $day = false, $hour = false, $min = false, $sec = false, $tz = false, $params = false)
    {
        if (empty($year)) {
            if ($this->getConfig('allowEmpty')) {
                $this->dtend = ['value' => null, 'params' => iCalUtilityFunctions::_setParams($params)];
                return true;
            } else {
                return false;
            }
        }
        $this->dtend = iCalUtilityFunctions::_setDate($year, $month, $day, $hour, $min, $sec, $tz, $params, null, null, $this->getConfig('TZID'));
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: DTSTAMP
     */
    /**
     * creates formatted output for calendar component property dtstamp
     *
     * @return string
     * @since 2.4.4 - 2008-03-07
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createDtstamp()
    {
        if (!isset($this->dtstamp['value']['year']) &&
            !isset($this->dtstamp['value']['month']) &&
            !isset($this->dtstamp['value']['day']) &&
            !isset($this->dtstamp['value']['hour']) &&
            !isset($this->dtstamp['value']['min']) &&
            !isset($this->dtstamp['value']['sec'])) {
            $this->_makeDtstamp();
        }
        $formatted = iCalUtilityFunctions::_format_date_time($this->dtstamp['value'], 7);
        $attributes = $this->_createParams($this->dtstamp['params']);
        return $this->_createElement('DTSTAMP', $attributes, $formatted);
    }

    /**
     * computes datestamp for calendar component object instance dtstamp
     *
     * @return void
     * @since 2.10.9 - 2011-08-10
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function _makeDtstamp()
    {
        $d = mktime(date('H'), date('i'), (date('s') - date('Z')), date('m'), date('d'), date('Y'));
        $this->dtstamp['value'] = [
            'year'  => date('Y', $d)
            ,
            'month' => date('m', $d)
            ,
            'day'   => date('d', $d)
            ,
            'hour'  => date('H', $d)
            ,
            'min'   => date('i', $d)
            ,
            'sec'   => date('s', $d),
        ];
        $this->dtstamp['params'] = null;
    }

    /**
     * set calendar component property dtstamp
     *
     * @param mixed $year
     * @param mixed $month optional
     * @param int $day optional
     * @param int $hour optional
     * @param int $min optional
     * @param int $sec optional
     * @param array $params optional
     * @return TRUE
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.4.8 - 2008-10-23
     */
    function setDtstamp($year, $month = false, $day = false, $hour = false, $min = false, $sec = false, $params = false)
    {
        if (empty($year)) {
            $this->_makeDtstamp();
        } else {
            $this->dtstamp = iCalUtilityFunctions::_setDate2($year, $month, $day, $hour, $min, $sec, $params);
        }
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: DTSTART
     */
    /**
     * creates formatted output for calendar component property dtstart
     *
     * @return string
     * @since 2.9.6 - 2011-05-15
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createDtstart()
    {
        if (empty($this->dtstart)) {
            return false;
        }
        if (!isset($this->dtstart['value']['year']) &&
            !isset($this->dtstart['value']['month']) &&
            !isset($this->dtstart['value']['day']) &&
            !isset($this->dtstart['value']['hour']) &&
            !isset($this->dtstart['value']['min']) &&
            !isset($this->dtstart['value']['sec'])) {
            if ($this->getConfig('allowEmpty')) {
                return $this->_createElement('DTSTART');
            } else {
                return false;
            }
        }
        if (in_array($this->objName, ['vtimezone', 'standard', 'daylight'])) {
            unset($this->dtstart['value']['tz'], $this->dtstart['params']['TZID']);
        } elseif ((false !== ($tzid = $this->getConfig('TZID'))) &&
            (!isset($this->dtstart['params']['VALUE']) || ($this->dtstart['params']['VALUE'] != 'DATE')) &&
            !isset($this->dtstart['params']['TZID'])) {
            $this->dtstart['params']['TZID'] = $tzid;
        }
        $formatted = iCalUtilityFunctions::_format_date_time($this->dtstart['value']);
        $attributes = $this->_createParams($this->dtstart['params']);
        return $this->_createElement('DTSTART', $attributes, $formatted);
    }

    /**
     * set calendar component property dtstart
     *
     * @param mixed $year
     * @param mixed $month optional
     * @param int $day optional
     * @param int $hour optional
     * @param int $min optional
     * @param int $sec optional
     * @param string $tz optional
     * @param array $params optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.6.22 - 2010-09-22
     */
    function setDtstart($year, $month = false, $day = false, $hour = false, $min = false, $sec = false, $tz = false, $params = false)
    {
        if (empty($year)) {
            if ($this->getConfig('allowEmpty')) {
                $this->dtstart = ['value' => null, 'params' => iCalUtilityFunctions::_setParams($params)];
                return true;
            } else {
                return false;
            }
        }
        $this->dtstart = iCalUtilityFunctions::_setDate($year, $month, $day, $hour, $min, $sec, $tz, $params, 'dtstart', $this->objName, $this->getConfig('TZID'));
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: DUE
     */
    /**
     * creates formatted output for calendar component property due
     *
     * @return string
     * @since 2.4.8 - 2008-10-22
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createDue()
    {
        if (empty($this->due)) {
            return false;
        }
        if (!isset($this->due['value']['year']) &&
            !isset($this->due['value']['month']) &&
            !isset($this->due['value']['day']) &&
            !isset($this->due['value']['hour']) &&
            !isset($this->due['value']['min']) &&
            !isset($this->due['value']['sec'])) {
            if ($this->getConfig('allowEmpty')) {
                return $this->_createElement('DUE');
            } else {
                return false;
            }
        }
        $formatted = iCalUtilityFunctions::_format_date_time($this->due['value']);
        if ((false !== ($tzid = $this->getConfig('TZID'))) &&
            (!isset($this->due['params']['VALUE']) || ($this->due['params']['VALUE'] != 'DATE')) &&
            !isset($this->due['params']['TZID'])) {
            $this->due['params']['TZID'] = $tzid;
        }
        $attributes = $this->_createParams($this->due['params']);
        return $this->_createElement('DUE', $attributes, $formatted);
    }

    /**
     * set calendar component property due
     *
     * @param mixed $year
     * @param mixed $month optional
     * @param int $day optional
     * @param int $hour optional
     * @param int $min optional
     * @param int $sec optional
     * @param array $params optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.4.8 - 2008-11-04
     */
    function setDue($year, $month = false, $day = false, $hour = false, $min = false, $sec = false, $tz = false, $params = false)
    {
        if (empty($year)) {
            if ($this->getConfig('allowEmpty')) {
                $this->due = ['value' => null, 'params' => iCalUtilityFunctions::_setParams($params)];
                return true;
            } else {
                return false;
            }
        }
        $this->due = iCalUtilityFunctions::_setDate($year, $month, $day, $hour, $min, $sec, $tz, $params, null, null, $this->getConfig('TZID'));
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: DURATION
     */
    /**
     * creates formatted output for calendar component property duration
     *
     * @return string
     * @since 2.4.8 - 2008-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createDuration()
    {
        if (empty($this->duration)) {
            return false;
        }
        if (!isset($this->duration['value']['week']) &&
            !isset($this->duration['value']['day']) &&
            !isset($this->duration['value']['hour']) &&
            !isset($this->duration['value']['min']) &&
            !isset($this->duration['value']['sec'])) {
            if ($this->getConfig('allowEmpty')) {
                return $this->_createElement('DURATION', [], null);
            } else {
                return false;
            }
        }
        $attributes = $this->_createParams($this->duration['params']);
        return $this->_createElement('DURATION', $attributes, iCalUtilityFunctions::_format_duration($this->duration['value']));
    }

    /**
     * set calendar component property duration
     *
     * @param mixed $week
     * @param mixed $day optional
     * @param int $hour optional
     * @param int $min optional
     * @param int $sec optional
     * @param array $params optional
     * @return bool
     * @since 2.4.8 - 2008-11-04
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setDuration($week, $day = false, $hour = false, $min = false, $sec = false, $params = false)
    {
        if (empty($week)) {
            if ($this->getConfig('allowEmpty')) {
                $week = null;
            } else {
                return false;
            }
        }
        if (is_array($week) && (1 <= count($week))) {
            $this->duration = ['value' => iCalUtilityFunctions::_duration_array($week), 'params' => iCalUtilityFunctions::_setParams($day)];
        } elseif (is_string($week) && (3 <= strlen(trim($week)))) {
            $week = trim($week);
            if (in_array(substr($week, 0, 1), ['+', '-'])) {
                $week = substr($week, 1);
            }
            $this->duration = ['value' => iCalUtilityFunctions::_duration_string($week), 'params' => iCalUtilityFunctions::_setParams($day)];
        } elseif (empty($week) && empty($day) && empty($hour) && empty($min) && empty($sec)) {
            return false;
        } else {
            $this->duration = ['value' => iCalUtilityFunctions::_duration_array([$week, $day, $hour, $min, $sec]), 'params' => iCalUtilityFunctions::_setParams($params)];
        }
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: EXDATE
     */
    /**
     * creates formatted output for calendar component property exdate
     *
     * @return string
     * @since 2.4.8 - 2008-10-22
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createExdate()
    {
        if (empty($this->exdate)) {
            return false;
        }
        $output = null;
        foreach ($this->exdate as $ex => $theExdate) {
            if (empty($theExdate['value'])) {
                if ($this->getConfig('allowEmpty')) {
                    $output .= $this->_createElement('EXDATE');
                }
                continue;
            }
            $content = $attributes = null;
            foreach ($theExdate['value'] as $eix => $exdatePart) {
                $parno = count($exdatePart);
                $formatted = iCalUtilityFunctions::_format_date_time($exdatePart, $parno);
                if (isset($theExdate['params']['TZID'])) {
                    $formatted = str_replace('Z', '', $formatted);
                }
                if (0 < $eix) {
                    if (isset($theExdate['value'][0]['tz'])) {
                        if (ctype_digit(substr($theExdate['value'][0]['tz'], -4)) ||
                            ('Z' == $theExdate['value'][0]['tz'])) {
                            if ('Z' != substr($formatted, -1)) {
                                $formatted .= 'Z';
                            }
                        } else {
                            $formatted = str_replace('Z', '', $formatted);
                        }
                    } else {
                        $formatted = str_replace('Z', '', $formatted);
                    }
                }
                $content .= (0 < $eix) ? ',' . $formatted : $formatted;
            }
            $attributes .= $this->_createParams($theExdate['params']);
            $output .= $this->_createElement('EXDATE', $attributes, $content);
        }
        return $output;
    }

    /**
     * set calendar component property exdate
     *
     * @param array exdates
     * @param array $params , optional
     * @param integer $index , optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.8 - 2012-01-19
     */
    function setExdate($exdates, $params = false, $index = false)
    {
        if (empty($exdates)) {
            if ($this->getConfig('allowEmpty')) {
                iCalUtilityFunctions::_setMval($this->exdate, null, $params, false, $index);
                return true;
            } else {
                return false;
            }
        }
        $input = ['params' => iCalUtilityFunctions::_setParams($params, ['VALUE' => 'DATE-TIME'])];
        $toZ = (isset($input['params']['TZID']) && in_array(strtoupper($input['params']['TZID']), ['GMT', 'UTC', 'Z'])) ? true : false;
        /* ev. check 1:st date and save ev. timezone **/
        iCalUtilityFunctions::_chkdatecfg(reset($exdates), $parno, $input['params']);
        iCalUtilityFunctions::_existRem($input['params'], 'VALUE', 'DATE-TIME'); // remove default parameter
        foreach ($exdates as $eix => $theExdate) {
            iCalUtilityFunctions::_strDate2arr($theExdate);
            if (iCalUtilityFunctions::_isArrayTimestampDate($theExdate)) {
                $exdatea = iCalUtilityFunctions::_timestamp2date($theExdate, $parno);
            } elseif (is_array($theExdate)) {
                $exdatea = iCalUtilityFunctions::_date_time_array($theExdate, $parno);
            } elseif (8 <= strlen(trim($theExdate))) { // ex. 2006-08-03 10:12:18
                $exdatea = iCalUtilityFunctions::_date_time_string($theExdate, $parno);
                unset($exdatea['unparsedtext']);
            }
            if (3 == $parno) {
                unset($exdatea['hour'], $exdatea['min'], $exdatea['sec'], $exdatea['tz']);
            } elseif (isset($exdatea['tz'])) {
                $exdatea['tz'] = (string)$exdatea['tz'];
            }
            if (isset($input['params']['TZID']) ||
                (isset($exdatea['tz']) && !iCalUtilityFunctions::_isOffset($exdatea['tz'])) ||
                (isset($input['value'][0]) && (!isset($input['value'][0]['tz']))) ||
                (isset($input['value'][0]['tz']) && !iCalUtilityFunctions::_isOffset($input['value'][0]['tz']))) {
                unset($exdatea['tz']);
            }
            if ($toZ) // time zone Z
            {
                $exdatea['tz'] = 'Z';
            }
            $input['value'][] = $exdatea;
        }
        if (0 >= count($input['value'])) {
            return false;
        }
        if (3 == $parno) {
            $input['params']['VALUE'] = 'DATE';
            unset($input['params']['TZID']);
        }
        if ($toZ) // time zone Z
        {
            unset($input['params']['TZID']);
        }
        iCalUtilityFunctions::_setMval($this->exdate, $input['value'], $input['params'], false, $index);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: EXRULE
     */
    /**
     * creates formatted output for calendar component property exrule
     *
     * @return string
     * @since 2.4.8 - 2008-10-22
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createExrule()
    {
        if (empty($this->exrule)) {
            return false;
        }
        return $this->_format_recur('EXRULE', $this->exrule);
    }

    /**
     * set calendar component property exdate
     *
     * @param array $exruleset
     * @param array $params , optional
     * @param integer $index , optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.5.1 - 2008-11-05
     */
    function setExrule($exruleset, $params = false, $index = false)
    {
        if (empty($exruleset)) {
            if ($this->getConfig('allowEmpty')) {
                $exruleset = null;
            } else {
                return false;
            }
        }
        iCalUtilityFunctions::_setMval($this->exrule, iCalUtilityFunctions::_setRexrule($exruleset), $params, false, $index);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: FREEBUSY
     */
    /**
     * creates formatted output for calendar component property freebusy
     *
     * @return string
     * @since 2.1.23 - 2012-02-16
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createFreebusy()
    {
        if (empty($this->freebusy)) {
            return false;
        }
        $output = null;
        foreach ($this->freebusy as $freebusyPart) {
            if (empty($freebusyPart['value']) || ((1 == count($freebusyPart['value'])) && isset($freebusyPart['value']['fbtype']))) {
                if ($this->getConfig('allowEmpty')) {
                    $output .= $this->_createElement('FREEBUSY');
                }
                continue;
            }
            $attributes = $content = null;
            if (isset($freebusyPart['value']['fbtype'])) {
                $attributes .= $this->intAttrDelimiter . 'FBTYPE=' . $freebusyPart['value']['fbtype'];
                unset($freebusyPart['value']['fbtype']);
                $freebusyPart['value'] = array_values($freebusyPart['value']);
            } else {
                $attributes .= $this->intAttrDelimiter . 'FBTYPE=BUSY';
            }
            $attributes .= $this->_createParams($freebusyPart['params']);
            $fno = 1;
            $cnt = count($freebusyPart['value']);
            foreach ($freebusyPart['value'] as $periodix => $freebusyPeriod) {
                $formatted = iCalUtilityFunctions::_format_date_time($freebusyPeriod[0]);
                $content .= $formatted;
                $content .= '/';
                $cnt2 = count($freebusyPeriod[1]);
                if (array_key_exists('year', $freebusyPeriod[1]))      // date-time
                {
                    $cnt2 = 7;
                } elseif (array_key_exists('week', $freebusyPeriod[1]))  // duration
                {
                    $cnt2 = 5;
                }
                if ((7 == $cnt2) &&    // period=  -> date-time
                    isset($freebusyPeriod[1]['year']) &&
                    isset($freebusyPeriod[1]['month']) &&
                    isset($freebusyPeriod[1]['day'])) {
                    $content .= iCalUtilityFunctions::_format_date_time($freebusyPeriod[1]);
                } else {                                  // period=  -> dur-time
                    $content .= iCalUtilityFunctions::_format_duration($freebusyPeriod[1]);
                }
                if ($fno < $cnt) {
                    $content .= ',';
                }
                $fno++;
            }
            $output .= $this->_createElement('FREEBUSY', $attributes, $content);
        }
        return $output;
    }

    /**
     * set calendar component property freebusy
     *
     * @param string $fbType
     * @param array $fbValues
     * @param array $params , optional
     * @param integer $index , optional
     * @return bool
     * @since 2.10.30 - 2012-01-16
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setFreebusy($fbType, $fbValues, $params = false, $index = false)
    {
        if (empty($fbValues)) {
            if ($this->getConfig('allowEmpty')) {
                iCalUtilityFunctions::_setMval($this->freebusy, null, $params, false, $index);
                return true;
            } else {
                return false;
            }
        }
        $fbType = strtoupper($fbType);
        if ((!in_array($fbType, ['FREE', 'BUSY', 'BUSY-UNAVAILABLE', 'BUSY-TENTATIVE'])) &&
            ('X-' != substr($fbType, 0, 2))) {
            $fbType = 'BUSY';
        }
        $input = ['fbtype' => $fbType];
        foreach ($fbValues as $fbPeriod) {   // periods => period
            if (empty($fbPeriod)) {
                continue;
            }
            $freebusyPeriod = [];
            foreach ($fbPeriod as $fbMember) { // pairs => singlepart
                $freebusyPairMember = [];
                if (is_array($fbMember)) {
                    if (iCalUtilityFunctions::_isArrayDate($fbMember)) { // date-time value
                        $freebusyPairMember = iCalUtilityFunctions::_date_time_array($fbMember, 7);
                        $freebusyPairMember['tz'] = 'Z';
                    } elseif (iCalUtilityFunctions::_isArrayTimestampDate($fbMember)) { // timestamp value
                        $freebusyPairMember = iCalUtilityFunctions::_timestamp2date($fbMember['timestamp'], 7);
                        $freebusyPairMember['tz'] = 'Z';
                    } else {                                         // array format duration
                        $freebusyPairMember = iCalUtilityFunctions::_duration_array($fbMember);
                    }
                } elseif ((3 <= strlen(trim($fbMember))) &&    // string format duration
                    (in_array($fbMember{0}, ['P', '+', '-']))) {
                    if ('P' != $fbMember{0}) {
                        $fbmember = substr($fbMember, 1);
                    }
                    $freebusyPairMember = iCalUtilityFunctions::_duration_string($fbMember);
                } elseif (8 <= strlen(trim($fbMember))) { // text date ex. 2006-08-03 10:12:18
                    $freebusyPairMember = iCalUtilityFunctions::_date_time_string($fbMember, 7);
                    unset($freebusyPairMember['unparsedtext']);
                    $freebusyPairMember['tz'] = 'Z';
                }
                $freebusyPeriod[] = $freebusyPairMember;
            }
            $input[] = $freebusyPeriod;
        }
        iCalUtilityFunctions::_setMval($this->freebusy, $input, $params, false, $index);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: GEO
     */
    /**
     * creates formatted output for calendar component property geo
     *
     * @return string
     * @since 2.4.8 - 2008-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createGeo()
    {
        if (empty($this->geo)) {
            return false;
        }
        if (empty($this->geo['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('GEO') : false;
        }
        $attributes = $this->_createParams($this->geo['params']);
        $content = null;
        $content .= number_format((float)$this->geo['value']['latitude'], 6, '.', '');
        $content .= ';';
        $content .= number_format((float)$this->geo['value']['longitude'], 6, '.', '');
        return $this->_createElement('GEO', $attributes, $content);
    }

    /**
     * set calendar component property geo
     *
     * @param float $latitude
     * @param float $longitude
     * @param array $params optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.4.8 - 2008-11-04
     */
    function setGeo($latitude, $longitude, $params = false)
    {
        if (!empty($latitude) && !empty($longitude)) {
            if (!is_array($this->geo)) {
                $this->geo = [];
            }
            $this->geo['value']['latitude'] = $latitude;
            $this->geo['value']['longitude'] = $longitude;
            $this->geo['params'] = iCalUtilityFunctions::_setParams($params);
        } elseif ($this->getConfig('allowEmpty')) {
            $this->geo = ['value' => null, 'params' => iCalUtilityFunctions::_setParams($params)];
        } else {
            return false;
        }
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: LAST-MODIFIED
     */
    /**
     * creates formatted output for calendar component property last-modified
     *
     * @return string
     * @since 2.4.8 - 2008-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createLastModified()
    {
        if (empty($this->lastmodified)) {
            return false;
        }
        $attributes = $this->_createParams($this->lastmodified['params']);
        $formatted = iCalUtilityFunctions::_format_date_time($this->lastmodified['value'], 7);
        return $this->_createElement('LAST-MODIFIED', $attributes, $formatted);
    }

    /**
     * set calendar component property completed
     *
     * @param mixed $year optional
     * @param mixed $month optional
     * @param int $day optional
     * @param int $hour optional
     * @param int $min optional
     * @param int $sec optional
     * @param array $params optional
     * @return boll
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.4.8 - 2008-10-23
     */
    function setLastModified($year = false, $month = false, $day = false, $hour = false, $min = false, $sec = false, $params = false)
    {
        if (empty($year)) {
            $year = date('Ymd\THis', mktime(date('H'), date('i'), date('s') - date('Z'), date('m'), date('d'), date('Y')));
        }
        $this->lastmodified = iCalUtilityFunctions::_setDate2($year, $month, $day, $hour, $min, $sec, $params);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: LOCATION
     */
    /**
     * creates formatted output for calendar component property location
     *
     * @return string
     * @since 2.4.8 - 2008-10-22
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createLocation()
    {
        if (empty($this->location)) {
            return false;
        }
        if (empty($this->location['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('LOCATION') : false;
        }
        $attributes = $this->_createParams($this->location['params'], ['ALTREP', 'LANGUAGE']);
        $content = $this->_strrep($this->location['value']);
        return $this->_createElement('LOCATION', $attributes, $content);
    }

    /**
     * set calendar component property location
     * '
     * @param string $value
     * @param array params optional
     * @return bool
     * @since 2.4.8 - 2008-11-04
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setLocation($value, $params = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $this->location = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: ORGANIZER
     */
    /**
     * creates formatted output for calendar component property organizer
     *
     * @return string
     * @since 2.6.33 - 2010-12-17
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createOrganizer()
    {
        if (empty($this->organizer)) {
            return false;
        }
        if (empty($this->organizer['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('ORGANIZER') : false;
        }
        $attributes = $this->_createParams($this->organizer['params']
            , ['CN', 'DIR', 'SENT-BY', 'LANGUAGE']);
        return $this->_createElement('ORGANIZER', $attributes, $this->organizer['value']);
    }

    /**
     * set calendar component property organizer
     *
     * @param string $value
     * @param array params optional
     * @return bool
     * @since 2.6.27 - 2010-11-29
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setOrganizer($value, $params = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        if (false === ($pos = strpos(substr($value, 0, 9), ':'))) {
            $value = 'MAILTO:' . $value;
        } else {
            $value = strtolower(substr($value, 0, $pos)) . substr($value, $pos);
        }
        $value = str_replace('mailto:', 'MAILTO:', $value);
        $this->organizer = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        if (isset($this->organizer['params']['SENT-BY'])) {
            if ('mailto:' !== strtolower(substr($this->organizer['params']['SENT-BY'], 0, 7))) {
                $this->organizer['params']['SENT-BY'] = 'MAILTO:' . $this->organizer['params']['SENT-BY'];
            } else {
                $this->organizer['params']['SENT-BY'] = 'MAILTO:' . substr($this->organizer['params']['SENT-BY'], 7);
            }
        }
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: PERCENT-COMPLETE
     */
    /**
     * creates formatted output for calendar component property percent-complete
     *
     * @return string
     * @since 2.9.3 - 2011-05-14
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createPercentComplete()
    {
        if (!isset($this->percentcomplete) || (empty($this->percentcomplete) && !is_numeric($this->percentcomplete))) {
            return false;
        }
        if (!isset($this->percentcomplete['value']) || (empty($this->percentcomplete['value']) && !is_numeric($this->percentcomplete['value']))) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('PERCENT-COMPLETE') : false;
        }
        $attributes = $this->_createParams($this->percentcomplete['params']);
        return $this->_createElement('PERCENT-COMPLETE', $attributes, $this->percentcomplete['value']);
    }

    /**
     * set calendar component property percent-complete
     *
     * @param int $value
     * @param array $params optional
     * @return bool
     * @since 2.9.3 - 2011-05-14
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setPercentComplete($value, $params = false)
    {
        if (empty($value) && !is_numeric($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $this->percentcomplete = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: PRIORITY
     */
    /**
     * creates formatted output for calendar component property priority
     *
     * @return string
     * @since 2.9.3 - 2011-05-14
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createPriority()
    {
        if (!isset($this->priority) || (empty($this->priority) && !is_numeric($this->priority))) {
            return false;
        }
        if (!isset($this->priority['value']) || (empty($this->priority['value']) && !is_numeric($this->priority['value']))) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('PRIORITY') : false;
        }
        $attributes = $this->_createParams($this->priority['params']);
        return $this->_createElement('PRIORITY', $attributes, $this->priority['value']);
    }

    /**
     * set calendar component property priority
     *
     * @param int $value
     * @param array $params optional
     * @return bool
     * @since 2.9.3 - 2011-05-14
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setPriority($value, $params = false)
    {
        if (empty($value) && !is_numeric($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $this->priority = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: RDATE
     */
    /**
     * creates formatted output for calendar component property rdate
     *
     * @return string
     * @since 2.4.16 - 2008-10-26
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createRdate()
    {
        if (empty($this->rdate)) {
            return false;
        }
        $utctime = (in_array($this->objName, ['vtimezone', 'standard', 'daylight'])) ? true : false;
        $output = null;
        if ($utctime) {
            unset($this->rdate['params']['TZID']);
        }
        foreach ($this->rdate as $theRdate) {
            if (empty($theRdate['value'])) {
                if ($this->getConfig('allowEmpty')) {
                    $output .= $this->_createElement('RDATE');
                }
                continue;
            }
            if ($utctime) {
                unset($theRdate['params']['TZID']);
            }
            $attributes = $this->_createParams($theRdate['params']);
            $cnt = count($theRdate['value']);
            $content = null;
            $rno = 1;
            foreach ($theRdate['value'] as $rpix => $rdatePart) {
                $contentPart = null;
                if (is_array($rdatePart) &&
                    isset($theRdate['params']['VALUE']) && ('PERIOD' == $theRdate['params']['VALUE'])) { // PERIOD
                    if ($utctime) {
                        unset($rdatePart[0]['tz']);
                    }
                    $formatted = iCalUtilityFunctions::_format_date_time($rdatePart[0]); // PERIOD part 1
                    if ($utctime || !empty($theRdate['params']['TZID'])) {
                        $formatted = str_replace('Z', '', $formatted);
                    }
                    if (0 < $rpix) {
                        if (!empty($rdatePart[0]['tz']) && iCalUtilityFunctions::_isOffset($rdatePart[0]['tz'])) {
                            if ('Z' != substr($formatted, -1)) {
                                $formatted .= 'Z';
                            }
                        } else {
                            $formatted = str_replace('Z', '', $formatted);
                        }
                    }
                    $contentPart .= $formatted;
                    $contentPart .= '/';
                    $cnt2 = count($rdatePart[1]);
                    if (array_key_exists('year', $rdatePart[1])) {
                        if (array_key_exists('hour', $rdatePart[1])) {
                            $cnt2 = 7;
                        }                                      // date-time
                        else {
                            $cnt2 = 3;
                        }                                      // date
                    } elseif (array_key_exists('week', $rdatePart[1]))  // duration
                    {
                        $cnt2 = 5;
                    }
                    if ((7 == $cnt2) &&    // period=  -> date-time
                        isset($rdatePart[1]['year']) &&
                        isset($rdatePart[1]['month']) &&
                        isset($rdatePart[1]['day'])) {
                        if ($utctime) {
                            unset($rdatePart[1]['tz']);
                        }
                        $formatted = iCalUtilityFunctions::_format_date_time($rdatePart[1]); // PERIOD part 2
                        if ($utctime || !empty($theRdate['params']['TZID'])) {
                            $formatted = str_replace('Z', '', $formatted);
                        }
                        if (!empty($rdatePart[0]['tz']) && iCalUtilityFunctions::_isOffset($rdatePart[0]['tz'])) {
                            if ('Z' != substr($formatted, -1)) {
                                $formatted .= 'Z';
                            }
                        } else {
                            $formatted = str_replace('Z', '', $formatted);
                        }
                        $contentPart .= $formatted;
                    } else {                                  // period=  -> dur-time
                        $contentPart .= iCalUtilityFunctions::_format_duration($rdatePart[1]);
                    }
                } // PERIOD end
                else { // SINGLE date start
                    if ($utctime) {
                        unset($rdatePart['tz']);
                    }
                    $formatted = iCalUtilityFunctions::_format_date_time($rdatePart);
                    if ($utctime || !empty($theRdate['params']['TZID'])) {
                        $formatted = str_replace('Z', '', $formatted);
                    }
                    if (!$utctime && (0 < $rpix)) {
                        if (!empty($theRdate['value'][0]['tz']) && iCalUtilityFunctions::_isOffset($theRdate['value'][0]['tz'])) {
                            if ('Z' != substr($formatted, -1)) {
                                $formatted .= 'Z';
                            }
                        } else {
                            $formatted = str_replace('Z', '', $formatted);
                        }
                    }
                    $contentPart .= $formatted;
                }
                $content .= $contentPart;
                if ($rno < $cnt) {
                    $content .= ',';
                }
                $rno++;
            }
            $output .= $this->_createElement('RDATE', $attributes, $content);
        }
        return $output;
    }

    /**
     * set calendar component property rdate
     *
     * @param array $rdates
     * @param array $params , optional
     * @param integer $index , optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.8 - 2012-01-31
     */
    function setRdate($rdates, $params = false, $index = false)
    {
        if (empty($rdates)) {
            if ($this->getConfig('allowEmpty')) {
                iCalUtilityFunctions::_setMval($this->rdate, null, $params, false, $index);
                return true;
            } else {
                return false;
            }
        }
        $input = ['params' => iCalUtilityFunctions::_setParams($params, ['VALUE' => 'DATE-TIME'])];
        if (in_array($this->objName, ['vtimezone', 'standard', 'daylight'])) {
            unset($input['params']['TZID']);
            $input['params']['VALUE'] = 'DATE-TIME';
        }
        $zArr = ['GMT', 'UTC', 'Z'];
        $toZ = (isset($params['TZID']) && in_array(strtoupper($params['TZID']), $zArr)) ? true : false;
        /*  check if PERIOD, if not set */
        if ((!isset($input['params']['VALUE']) || !in_array($input['params']['VALUE'], ['DATE', 'PERIOD'])) &&
            isset($rdates[0]) && is_array($rdates[0]) && (2 == count($rdates[0])) &&
            isset($rdates[0][0]) && isset($rdates[0][1]) && !isset($rdates[0]['timestamp']) &&
            ((is_array($rdates[0][0]) && (isset($rdates[0][0]['timestamp']) ||
                        iCalUtilityFunctions::_isArrayDate($rdates[0][0]))) ||
                (is_string($rdates[0][0]) && (8 <= strlen(trim($rdates[0][0]))))) &&
            (is_array($rdates[0][1]) || (is_string($rdates[0][1]) && (3 <= strlen(trim($rdates[0][1])))))) {
            $input['params']['VALUE'] = 'PERIOD';
        }
        /* check 1:st date, upd. $parno (opt) and save ev. timezone **/
        $date = reset($rdates);
        if (isset($input['params']['VALUE']) && ('PERIOD' == $input['params']['VALUE'])) // PERIOD
        {
            $date = reset($date);
        }
        iCalUtilityFunctions::_chkdatecfg($date, $parno, $input['params']);
        iCalUtilityFunctions::_existRem($input['params'], 'VALUE', 'DATE-TIME'); // remove default
        foreach ($rdates as $rpix => $theRdate) {
            $inputa = null;
            iCalUtilityFunctions::_strDate2arr($theRdate);
            if (is_array($theRdate)) {
                if (isset($input['params']['VALUE']) && ('PERIOD' == $input['params']['VALUE'])) { // PERIOD
                    foreach ($theRdate as $rix => $rPeriod) {
                        iCalUtilityFunctions::_strDate2arr($theRdate);
                        if (is_array($rPeriod)) {
                            if (iCalUtilityFunctions::_isArrayTimestampDate($rPeriod))      // timestamp
                            {
                                $inputab = (isset($rPeriod['tz'])) ? iCalUtilityFunctions::_timestamp2date($rPeriod, $parno) : iCalUtilityFunctions::_timestamp2date($rPeriod, 6);
                            } elseif (iCalUtilityFunctions::_isArrayDate($rPeriod)) {
                                $inputab = (3 < count($rPeriod)) ? iCalUtilityFunctions::_date_time_array($rPeriod, $parno) : iCalUtilityFunctions::_date_time_array($rPeriod, 6);
                            } elseif ((1 == count($rPeriod)) && (8 <= strlen(reset($rPeriod)))) { // text-date
                                $inputab = iCalUtilityFunctions::_date_time_string(reset($rPeriod), $parno);
                                unset($inputab['unparsedtext']);
                            } else                                               // array format duration
                            {
                                $inputab = iCalUtilityFunctions::_duration_array($rPeriod);
                            }
                        } elseif ((3 <= strlen(trim($rPeriod))) &&          // string format duration
                            (in_array($rPeriod[0], ['P', '+', '-']))) {
                            if ('P' != $rPeriod[0]) {
                                $rPeriod = substr($rPeriod, 1);
                            }
                            $inputab = iCalUtilityFunctions::_duration_string($rPeriod);
                        } elseif (8 <= strlen(trim($rPeriod))) {            // text date ex. 2006-08-03 10:12:18
                            $inputab = iCalUtilityFunctions::_date_time_string($rPeriod, $parno);
                            unset($inputab['unparsedtext']);
                        }
                        if (isset($input['params']['TZID']) ||
                            (isset($inputab['tz']) && !iCalUtilityFunctions::_isOffset($inputab['tz'])) ||
                            (isset($inputa[0]) && (!isset($inputa[0]['tz']))) ||
                            (isset($inputa[0]['tz']) && !iCalUtilityFunctions::_isOffset($inputa[0]['tz']))) {
                            unset($inputab['tz']);
                        }
                        if ($toZ) {
                            $inputab['tz'] = 'Z';
                        }
                        $inputa[] = $inputab;
                    }
                } // PERIOD end
                elseif (iCalUtilityFunctions::_isArrayTimestampDate($theRdate)) {    // timestamp
                    $inputa = iCalUtilityFunctions::_timestamp2date($theRdate, $parno);
                    if ($toZ) {
                        $inputa['tz'] = 'Z';
                    }
                } else {                                                                  // date[-time]
                    $inputa = iCalUtilityFunctions::_date_time_array($theRdate, $parno);
                    $toZ = (isset($inputa['tz']) && in_array(strtoupper($inputa['tz']), $zArr)) ? true : false;
                    if ($toZ) {
                        $inputa['tz'] = 'Z';
                    }
                }
            } elseif (8 <= strlen(trim($theRdate))) {                 // text date ex. 2006-08-03 10:12:18
                $inputa = iCalUtilityFunctions::_date_time_string($theRdate, $parno);
                unset($inputa['unparsedtext']);
                if ($toZ) {
                    $inputa['tz'] = 'Z';
                }
            }
            if (!isset($input['params']['VALUE']) || ('PERIOD' != $input['params']['VALUE'])) { // no PERIOD
                if (3 == $parno) {
                    unset($inputa['hour'], $inputa['min'], $inputa['sec'], $inputa['tz']);
                } elseif (isset($inputa['tz'])) {
                    $inputa['tz'] = (string)$inputa['tz'];
                }
                if (isset($input['params']['TZID']) ||
                    (isset($inputa['tz']) && !iCalUtilityFunctions::_isOffset($inputa['tz'])) ||
                    (isset($input['value'][0]) && (!isset($input['value'][0]['tz']))) ||
                    (isset($input['value'][0]['tz']) && !iCalUtilityFunctions::_isOffset($input['value'][0]['tz']))) {
                    if (!$toZ) {
                        unset($inputa['tz']);
                    }
                }
            }
            $input['value'][] = $inputa;
        }
        if (3 == $parno) {
            $input['params']['VALUE'] = 'DATE';
            unset($input['params']['TZID']);
        }
        if ($toZ) {
            unset($input['params']['TZID']);
        }
        iCalUtilityFunctions::_setMval($this->rdate, $input['value'], $input['params'], false, $index);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: RECURRENCE-ID
     */
    /**
     * creates formatted output for calendar component property recurrence-id
     *
     * @return string
     * @since 2.9.6 - 2011-05-15
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createRecurrenceid()
    {
        if (empty($this->recurrenceid)) {
            return false;
        }
        if (empty($this->recurrenceid['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('RECURRENCE-ID') : false;
        }
        $formatted = iCalUtilityFunctions::_format_date_time($this->recurrenceid['value']);
        if ((false !== ($tzid = $this->getConfig('TZID'))) &&
            (!isset($this->recurrenceid['params']['VALUE']) || ($this->recurrenceid['params']['VALUE'] != 'DATE')) &&
            !isset($this->recurrenceid['params']['TZID'])) {
            $this->recurrenceid['params']['TZID'] = $tzid;
        }
        $attributes = $this->_createParams($this->recurrenceid['params']);
        return $this->_createElement('RECURRENCE-ID', $attributes, $formatted);
    }

    /**
     * set calendar component property recurrence-id
     *
     * @param mixed $year
     * @param mixed $month optional
     * @param int $day optional
     * @param int $hour optional
     * @param int $min optional
     * @param int $sec optional
     * @param array $params optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.9.6 - 2011-05-15
     */
    function setRecurrenceid($year, $month = false, $day = false, $hour = false, $min = false, $sec = false, $tz = false, $params = false)
    {
        if (empty($year)) {
            if ($this->getConfig('allowEmpty')) {
                $this->recurrenceid = ['value' => null, 'params' => null];
                return true;
            } else {
                return false;
            }
        }
        $this->recurrenceid = iCalUtilityFunctions::_setDate($year, $month, $day, $hour, $min, $sec, $tz, $params, null, null, $this->getConfig('TZID'));
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: RELATED-TO
     */
    /**
     * creates formatted output for calendar component property related-to
     *
     * @return string
     * @since 2.11.24 - 2012-02-23
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createRelatedTo()
    {
        if (empty($this->relatedto)) {
            return false;
        }
        $output = null;
        foreach ($this->relatedto as $relation) {
            if (!empty($relation['value'])) {
                $output .= $this->_createElement('RELATED-TO', $this->_createParams($relation['params']), $this->_strrep($relation['value']));
            } elseif ($this->getConfig('allowEmpty')) {
                $output .= $this->_createElement('RELATED-TO', $this->_createParams($relation['params']));
            }
        }
        return $output;
    }

    /**
     * set calendar component property related-to
     *
     * @param float $relid
     * @param array $params , optional
     * @param index $index , optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.24 - 2012-02-23
     */
    function setRelatedTo($value, $params = false, $index = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        iCalUtilityFunctions::_existRem($params, 'RELTYPE', 'PARENT', true); // remove default
        iCalUtilityFunctions::_setMval($this->relatedto, $value, $params, false, $index);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: REPEAT
     */
    /**
     * creates formatted output for calendar component property repeat
     *
     * @return string
     * @since 2.9.3 - 2011-05-14
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createRepeat()
    {
        if (!isset($this->repeat) || (empty($this->repeat) && !is_numeric($this->repeat))) {
            return false;
        }
        if (!isset($this->repeat['value']) || (empty($this->repeat['value']) && !is_numeric($this->repeat['value']))) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('REPEAT') : false;
        }
        $attributes = $this->_createParams($this->repeat['params']);
        return $this->_createElement('REPEAT', $attributes, $this->repeat['value']);
    }

    /**
     * set calendar component property repeat
     *
     * @param string $value
     * @param array $params optional
     * @return void
     * @since 2.9.3 - 2011-05-14
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setRepeat($value, $params = false)
    {
        if (empty($value) && !is_numeric($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $this->repeat = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: REQUEST-STATUS
     */
    /**
     * creates formatted output for calendar component property request-status
     * @return string
     * @since 2.4.8 - 2008-10-23
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createRequestStatus()
    {
        if (empty($this->requeststatus)) {
            return false;
        }
        $output = null;
        foreach ($this->requeststatus as $rstat) {
            if (empty($rstat['value']['statcode'])) {
                if ($this->getConfig('allowEmpty')) {
                    $output .= $this->_createElement('REQUEST-STATUS');
                }
                continue;
            }
            $attributes = $this->_createParams($rstat['params'], ['LANGUAGE']);
            $content = number_format((float)$rstat['value']['statcode'], 2, '.', '');
            $content .= ';' . $this->_strrep($rstat['value']['text']);
            if (isset($rstat['value']['extdata'])) {
                $content .= ';' . $this->_strrep($rstat['value']['extdata']);
            }
            $output .= $this->_createElement('REQUEST-STATUS', $attributes, $content);
        }
        return $output;
    }

    /**
     * set calendar component property request-status
     *
     * @param float $statcode
     * @param string $text
     * @param string $extdata , optional
     * @param array $params , optional
     * @param integer $index , optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.5.1 - 2008-11-05
     */
    function setRequestStatus($statcode, $text, $extdata = false, $params = false, $index = false)
    {
        if (empty($statcode) || empty($text)) {
            if ($this->getConfig('allowEmpty')) {
                $statcode = $text = null;
            } else {
                return false;
            }
        }
        $input = ['statcode' => $statcode, 'text' => $text];
        if ($extdata) {
            $input['extdata'] = $extdata;
        }
        iCalUtilityFunctions::_setMval($this->requeststatus, $input, $params, false, $index);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: RESOURCES
     */
    /**
     * creates formatted output for calendar component property resources
     *
     * @return string
     * @since 2.4.8 - 2008-10-23
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createResources()
    {
        if (empty($this->resources)) {
            return false;
        }
        $output = null;
        foreach ($this->resources as $resource) {
            if (empty($resource['value'])) {
                if ($this->getConfig('allowEmpty')) {
                    $output .= $this->_createElement('RESOURCES');
                }
                continue;
            }
            $attributes = $this->_createParams($resource['params'], ['ALTREP', 'LANGUAGE']);
            if (is_array($resource['value'])) {
                foreach ($resource['value'] as $rix => $resourcePart) {
                    $resource['value'][$rix] = $this->_strrep($resourcePart);
                }
                $content = implode(',', $resource['value']);
            } else {
                $content = $this->_strrep($resource['value']);
            }
            $output .= $this->_createElement('RESOURCES', $attributes, $content);
        }
        return $output;
    }

    /**
     * set calendar component property recources
     *
     * @param mixed $value
     * @param array $params , optional
     * @param integer $index , optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.5.1 - 2008-11-05
     */
    function setResources($value, $params = false, $index = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        iCalUtilityFunctions::_setMval($this->resources, $value, $params, false, $index);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: RRULE
     */
    /**
     * creates formatted output for calendar component property rrule
     *
     * @return string
     * @since 2.4.8 - 2008-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createRrule()
    {
        if (empty($this->rrule)) {
            return false;
        }
        return $this->_format_recur('RRULE', $this->rrule);
    }

    /**
     * set calendar component property rrule
     *
     * @param array $rruleset
     * @param array $params , optional
     * @param integer $index , optional
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.5.1 - 2008-11-05
     */
    function setRrule($rruleset, $params = false, $index = false)
    {
        if (empty($rruleset)) {
            if ($this->getConfig('allowEmpty')) {
                $rruleset = null;
            } else {
                return false;
            }
        }
        iCalUtilityFunctions::_setMval($this->rrule, iCalUtilityFunctions::_setRexrule($rruleset), $params, false, $index);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: SEQUENCE
     */
    /**
     * creates formatted output for calendar component property sequence
     * @return string
     * @since 2.9.3 - 2011-05-14
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createSequence()
    {
        if (!isset($this->sequence) || (empty($this->sequence) && !is_numeric($this->sequence))) {
            return false;
        }
        if ((!isset($this->sequence['value']) || (empty($this->sequence['value']) && !is_numeric($this->sequence['value']))) &&
            ('0' != $this->sequence['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('SEQUENCE') : false;
        }
        $attributes = $this->_createParams($this->sequence['params']);
        return $this->_createElement('SEQUENCE', $attributes, $this->sequence['value']);
    }

    /**
     * set calendar component property sequence
     * @param int $value optional
     * @param array $params optional
     * @return bool
     * @since 2.10.8 - 2011-09-19
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setSequence($value = false, $params = false)
    {
        if ((empty($value) && !is_numeric($value)) && ('0' != $value)) {
            $value = (isset($this->sequence['value']) && (-1 < $this->sequence['value'])) ? $this->sequence['value'] + 1 : '0';
        }
        $this->sequence = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: STATUS
     */
    /**
     * creates formatted output for calendar component property status
     *
     * @return string
     * @since 2.4.8 - 2008-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createStatus()
    {
        if (empty($this->status)) {
            return false;
        }
        if (empty($this->status['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('STATUS') : false;
        }
        $attributes = $this->_createParams($this->status['params']);
        return $this->_createElement('STATUS', $attributes, $this->status['value']);
    }

    /**
     * set calendar component property status
     *
     * @param string $value
     * @param array $params optional
     * @return bool
     * @since 2.4.8 - 2008-11-04
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setStatus($value, $params = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $this->status = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: SUMMARY
     */
    /**
     * creates formatted output for calendar component property summary
     *
     * @return string
     * @since 2.4.8 - 2008-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createSummary()
    {
        if (empty($this->summary)) {
            return false;
        }
        if (empty($this->summary['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('SUMMARY') : false;
        }
        $attributes = $this->_createParams($this->summary['params'], ['ALTREP', 'LANGUAGE']);
        $content = $this->_strrep($this->summary['value']);
        return $this->_createElement('SUMMARY', $attributes, $content);
    }

    /**
     * set calendar component property summary
     *
     * @param string $value
     * @param string $params optional
     * @return bool
     * @since 2.4.8 - 2008-11-04
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setSummary($value, $params = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $this->summary = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: TRANSP
     */
    /**
     * creates formatted output for calendar component property transp
     *
     * @return string
     * @since 2.4.8 - 2008-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createTransp()
    {
        if (empty($this->transp)) {
            return false;
        }
        if (empty($this->transp['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('TRANSP') : false;
        }
        $attributes = $this->_createParams($this->transp['params']);
        return $this->_createElement('TRANSP', $attributes, $this->transp['value']);
    }

    /**
     * set calendar component property transp
     *
     * @param string $value
     * @param string $params optional
     * @return bool
     * @since 2.4.8 - 2008-11-04
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setTransp($value, $params = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $this->transp = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: TRIGGER
     */
    /**
     * creates formatted output for calendar component property trigger
     *
     * @return string
     * @since 2.4.16 - 2008-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createTrigger()
    {
        if (empty($this->trigger)) {
            return false;
        }
        if (empty($this->trigger['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('TRIGGER') : false;
        }
        $content = $attributes = null;
        if (isset($this->trigger['value']['year']) &&
            isset($this->trigger['value']['month']) &&
            isset($this->trigger['value']['day'])) {
            $content .= iCalUtilityFunctions::_format_date_time($this->trigger['value']);
        } else {
            if (true !== $this->trigger['value']['relatedStart']) {
                $attributes .= $this->intAttrDelimiter . 'RELATED=END';
            }
            if ($this->trigger['value']['before']) {
                $content .= '-';
            }
            $content .= iCalUtilityFunctions::_format_duration($this->trigger['value']);
        }
        $attributes .= $this->_createParams($this->trigger['params']);
        return $this->_createElement('TRIGGER', $attributes, $content);
    }

    /**
     * set calendar component property trigger
     *
     * @param mixed $year
     * @param mixed $month optional
     * @param int $day optional
     * @param int $week optional
     * @param int $hour optional
     * @param int $min optional
     * @param int $sec optional
     * @param bool $relatedStart optional
     * @param bool $before optional
     * @param array $params optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.10.30 - 2012-01-16
     */
    function setTrigger($year, $month = null, $day = null, $week = false, $hour = false, $min = false, $sec = false, $relatedStart = true, $before = true, $params = false)
    {
        if (empty($year) && empty($month) && empty($day) && empty($week) && empty($hour) && empty($min) && empty($sec)) {
            if ($this->getConfig('allowEmpty')) {
                $this->trigger = ['value' => null, 'params' => iCalUtilityFunctions::_setParams($params)];
                return true;
            } else {
                return false;
            }
        }
        if (iCalUtilityFunctions::_isArrayTimestampDate($year)) { // timestamp
            $params = iCalUtilityFunctions::_setParams($month);
            $date = iCalUtilityFunctions::_timestamp2date($year, 7);
            foreach ($date as $k => $v) {
                $$k = $v;
            }
        } elseif (is_array($year) && (is_array($month) || empty($month))) {
            $params = iCalUtilityFunctions::_setParams($month);
            if (!(array_key_exists('year', $year) &&   // exclude date-time
                array_key_exists('month', $year) &&
                array_key_exists('day', $year))) {  // when this must be a duration
                if (isset($params['RELATED']) && ('END' == strtoupper($params['RELATED']))) {
                    $relatedStart = false;
                } else {
                    $relatedStart = (array_key_exists('relatedStart', $year) && (true !== $year['relatedStart'])) ? false : true;
                }
                $before = (array_key_exists('before', $year) && (true !== $year['before'])) ? false : true;
            }
            $SSYY = (array_key_exists('year', $year)) ? $year['year'] : null;
            $month = (array_key_exists('month', $year)) ? $year['month'] : null;
            $day = (array_key_exists('day', $year)) ? $year['day'] : null;
            $week = (array_key_exists('week', $year)) ? $year['week'] : null;
            $hour = (array_key_exists('hour', $year)) ? $year['hour'] : 0; //null;
            $min = (array_key_exists('min', $year)) ? $year['min'] : 0; //null;
            $sec = (array_key_exists('sec', $year)) ? $year['sec'] : 0; //null;
            $year = $SSYY;
        } elseif (is_string($year) && (is_array($month) || empty($month))) {  // duration or date in a string
            $params = iCalUtilityFunctions::_setParams($month);
            if (in_array($year[0], ['P', '+', '-'])) { // duration
                $relatedStart = (isset($params['RELATED']) && ('END' == strtoupper($params['RELATED']))) ? false : true;
                $before = ('-' == $year[0]) ? true : false;
                if ('P' != $year[0]) {
                    $year = substr($year, 1);
                }
                $date = iCalUtilityFunctions::_duration_string($year);
            } else   // date
            {
                $date = iCalUtilityFunctions::_date_time_string($year, 7);
            }
            unset($year, $month, $day, $date['unparsedtext']);
            if (empty($date)) {
                $sec = 0;
            } else {
                foreach ($date as $k => $v) {
                    $$k = $v;
                }
            }
        } else // single values in function input parameters
        {
            $params = iCalUtilityFunctions::_setParams($params);
        }
        if (!empty($year) && !empty($month) && !empty($day)) { // date
            $params['VALUE'] = 'DATE-TIME';
            $hour = ($hour) ? $hour : 0;
            $min = ($min) ? $min : 0;
            $sec = ($sec) ? $sec : 0;
            $this->trigger = ['params' => $params];
            $this->trigger['value'] = [
                'year'  => $year
                ,
                'month' => $month
                ,
                'day'   => $day
                ,
                'hour'  => $hour
                ,
                'min'   => $min
                ,
                'sec'   => $sec
                ,
                'tz'    => 'Z',
            ];
            return true;
        } elseif ((empty($year) && empty($month)) &&    // duration
            ((!empty($week) || (0 == $week)) ||
                (!empty($day) || (0 == $day)) ||
                (!empty($hour) || (0 == $hour)) ||
                (!empty($min) || (0 == $min)) ||
                (!empty($sec) || (0 == $sec)))) {
            unset($params['RELATED']); // set at output creation (END only)
            unset($params['VALUE']);   // 'DURATION' default
            $this->trigger = ['params' => $params];
            $this->trigger['value'] = [];
            if (!empty($week)) {
                $this->trigger['value']['week'] = $week;
            }
            if (!empty($day)) {
                $this->trigger['value']['day'] = $day;
            }
            if (!empty($hour)) {
                $this->trigger['value']['hour'] = $hour;
            }
            if (!empty($min)) {
                $this->trigger['value']['min'] = $min;
            }
            if (!empty($sec)) {
                $this->trigger['value']['sec'] = $sec;
            }
            if (empty($this->trigger['value'])) {
                $this->trigger['value']['sec'] = 0;
                $before = false;
            }
            $relatedStart = (false !== $relatedStart) ? true : false;
            $before = (false !== $before) ? true : false;
            $this->trigger['value']['relatedStart'] = $relatedStart;
            $this->trigger['value']['before'] = $before;
            return true;
        }
        return false;
    }
    /*********************************************************************************/
    /**
     * Property Name: TZID
     */
    /**
     * creates formatted output for calendar component property tzid
     *
     * @return string
     * @since 2.4.8 - 2008-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createTzid()
    {
        if (empty($this->tzid)) {
            return false;
        }
        if (empty($this->tzid['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('TZID') : false;
        }
        $attributes = $this->_createParams($this->tzid['params']);
        return $this->_createElement('TZID', $attributes, $this->_strrep($this->tzid['value']));
    }

    /**
     * set calendar component property tzid
     *
     * @param string $value
     * @param array $params optional
     * @return bool
     * @since 2.4.8 - 2008-11-04
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setTzid($value, $params = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $this->tzid = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * .. .
     * Property Name: TZNAME
     */
    /**
     * creates formatted output for calendar component property tzname
     *
     * @return string
     * @since 2.4.8 - 2008-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createTzname()
    {
        if (empty($this->tzname)) {
            return false;
        }
        $output = null;
        foreach ($this->tzname as $theName) {
            if (!empty($theName['value'])) {
                $attributes = $this->_createParams($theName['params'], ['LANGUAGE']);
                $output .= $this->_createElement('TZNAME', $attributes, $this->_strrep($theName['value']));
            } elseif ($this->getConfig('allowEmpty')) {
                $output .= $this->_createElement('TZNAME');
            }
        }
        return $output;
    }

    /**
     * set calendar component property tzname
     *
     * @param string $value
     * @param string $params , optional
     * @param integer $index , optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.5.1 - 2008-11-05
     */
    function setTzname($value, $params = false, $index = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        iCalUtilityFunctions::_setMval($this->tzname, $value, $params, false, $index);
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: TZOFFSETFROM
     */
    /**
     * creates formatted output for calendar component property tzoffsetfrom
     *
     * @return string
     * @since 2.4.8 - 2008-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createTzoffsetfrom()
    {
        if (empty($this->tzoffsetfrom)) {
            return false;
        }
        if (empty($this->tzoffsetfrom['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('TZOFFSETFROM') : false;
        }
        $attributes = $this->_createParams($this->tzoffsetfrom['params']);
        return $this->_createElement('TZOFFSETFROM', $attributes, $this->tzoffsetfrom['value']);
    }

    /**
     * set calendar component property tzoffsetfrom
     *
     * @param string $value
     * @param string $params optional
     * @return bool
     * @since 2.4.8 - 2008-11-04
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setTzoffsetfrom($value, $params = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $this->tzoffsetfrom = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: TZOFFSETTO
     */
    /**
     * creates formatted output for calendar component property tzoffsetto
     *
     * @return string
     * @since 2.4.8 - 2008-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createTzoffsetto()
    {
        if (empty($this->tzoffsetto)) {
            return false;
        }
        if (empty($this->tzoffsetto['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('TZOFFSETTO') : false;
        }
        $attributes = $this->_createParams($this->tzoffsetto['params']);
        return $this->_createElement('TZOFFSETTO', $attributes, $this->tzoffsetto['value']);
    }

    /**
     * set calendar component property tzoffsetto
     *
     * @param string $value
     * @param string $params optional
     * @return bool
     * @since 2.4.8 - 2008-11-04
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setTzoffsetto($value, $params = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $this->tzoffsetto = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: TZURL
     */
    /**
     * creates formatted output for calendar component property tzurl
     *
     * @return string
     * @since 2.4.8 - 2008-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createTzurl()
    {
        if (empty($this->tzurl)) {
            return false;
        }
        if (empty($this->tzurl['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('TZURL') : false;
        }
        $attributes = $this->_createParams($this->tzurl['params']);
        return $this->_createElement('TZURL', $attributes, $this->tzurl['value']);
    }

    /**
     * set calendar component property tzurl
     *
     * @param string $value
     * @param string $params optional
     * @return boll
     * @since 2.4.8 - 2008-11-04
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setTzurl($value, $params = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $this->tzurl = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: UID
     */
    /**
     * creates formatted output for calendar component property uid
     *
     * @return string
     * @since 0.9.7 - 2006-11-20
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createUid()
    {
        if (0 >= count($this->uid)) {
            $this->_makeuid();
        }
        $attributes = $this->_createParams($this->uid['params']);
        return $this->_createElement('UID', $attributes, $this->uid['value']);
    }

    /**
     * create an unique id for this calendar component object instance
     *
     * @return void
     * @since 2.2.7 - 2007-09-04
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function _makeUid()
    {
        $date = date('Ymd\THisT');
        $unique = substr(microtime(), 2, 4);
        $base = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPrRsStTuUvVxXuUvVwWzZ1234567890';
        $start = 0;
        $end = strlen($base) - 1;
        $length = 6;
        $str = null;
        for ($p = 0; $p < $length; $p++) {
            $unique .= $base{mt_rand($start, $end)};
        }
        $this->uid = ['params' => null];
        $this->uid['value'] = $date . '-' . $unique . '@' . $this->getConfig('unique_id');
    }

    /**
     * set calendar component property uid
     *
     * @param string $value
     * @param string $params optional
     * @return bool
     * @since 2.4.8 - 2008-11-04
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setUid($value, $params = false)
    {
        if (empty($value)) {
            return false;
        } // no allowEmpty check here !!!!
        $this->uid = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: URL
     */
    /**
     * creates formatted output for calendar component property url
     *
     * @return string
     * @since 2.4.8 - 2008-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createUrl()
    {
        if (empty($this->url)) {
            return false;
        }
        if (empty($this->url['value'])) {
            return ($this->getConfig('allowEmpty')) ? $this->_createElement('URL') : false;
        }
        $attributes = $this->_createParams($this->url['params']);
        return $this->_createElement('URL', $attributes, $this->url['value']);
    }

    /**
     * set calendar component property url
     *
     * @param string $value
     * @param string $params optional
     * @return bool
     * @since 2.4.8 - 2008-11-04
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function setUrl($value, $params = false)
    {
        if (empty($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $this->url = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params)];
        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: x-prop
     */
    /**
     * creates formatted output for calendar component property x-prop
     *
     * @return string
     * @since 2.9.3 - 2011-05-14
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function createXprop()
    {
        if (empty($this->xprop)) {
            return false;
        }
        $output = null;
        foreach ($this->xprop as $label => $xpropPart) {
            if (!isset($xpropPart['value']) || (empty($xpropPart['value']) && !is_numeric($xpropPart['value']))) {
                if ($this->getConfig('allowEmpty')) {
                    $output .= $this->_createElement($label);
                }
                continue;
            }
            $attributes = $this->_createParams($xpropPart['params'], ['LANGUAGE']);
            if (is_array($xpropPart['value'])) {
                foreach ($xpropPart['value'] as $pix => $theXpart) {
                    $xpropPart['value'][$pix] = $this->_strrep($theXpart);
                }
                $xpropPart['value'] = implode(',', $xpropPart['value']);
            } else {
                $xpropPart['value'] = $this->_strrep($xpropPart['value']);
            }
            $output .= $this->_createElement($label, $attributes, $xpropPart['value']);
        }
        return $output;
    }

    /**
     * set calendar component property x-prop
     *
     * @param string $label
     * @param mixed $value
     * @param array $params optional
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.9 - 2012-01-16
     */
    function setXprop($label, $value, $params = false)
    {
        if (empty($label)) {
            return false;
        }
        if ('X-' != strtoupper(substr($label, 0, 2))) {
            return false;
        }
        if (empty($value) && !is_numeric($value)) {
            if ($this->getConfig('allowEmpty')) {
                $value = null;
            } else {
                return false;
            }
        }
        $xprop = ['value' => $value];
        $xprop['params'] = iCalUtilityFunctions::_setParams($params);
        if (!is_array($this->xprop)) {
            $this->xprop = [];
        }
        $this->xprop[strtoupper($label)] = $xprop;
        return true;
    }
    /*********************************************************************************/
    /*********************************************************************************/
    /**
     * create element format parts
     *
     * @return string
     * @since 2.0.6 - 2006-06-20
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function _createFormat()
    {
        $objectname = null;
        switch ($this->format) {
            case 'xcal':
                $objectname = (isset($this->timezonetype)) ?
                    strtolower($this->timezonetype) : strtolower($this->objName);
                $this->componentStart1 = $this->elementStart1 = '<';
                $this->componentStart2 = $this->elementStart2 = '>';
                $this->componentEnd1 = $this->elementEnd1 = '</';
                $this->componentEnd2 = $this->elementEnd2 = '>' . $this->nl;
                $this->intAttrDelimiter = '<!-- -->';
                $this->attributeDelimiter = $this->nl;
                $this->valueInit = null;
                break;
            default:
                $objectname = (isset($this->timezonetype)) ?
                    strtoupper($this->timezonetype) : strtoupper($this->objName);
                $this->componentStart1 = 'BEGIN:';
                $this->componentStart2 = null;
                $this->componentEnd1 = 'END:';
                $this->componentEnd2 = $this->nl;
                $this->elementStart1 = null;
                $this->elementStart2 = null;
                $this->elementEnd1 = null;
                $this->elementEnd2 = $this->nl;
                $this->intAttrDelimiter = '<!-- -->';
                $this->attributeDelimiter = ';';
                $this->valueInit = ':';
                break;
        }
        return $objectname;
    }

    /**
     * creates formatted output for calendar component property
     *
     * @param string $label property name
     * @param string $attributes property attributes
     * @param string $content property content (optional)
     * @return string
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.10.16 - 2011-10-28
     */
    function _createElement($label, $attributes = null, $content = false)
    {
        switch ($this->format) {
            case 'xcal':
                $label = strtolower($label);
                break;
            default:
                $label = strtoupper($label);
                break;
        }
        $output = $this->elementStart1 . $label;
        $categoriesAttrLang = null;
        $attachInlineBinary = false;
        $attachfmttype = null;
        if (('xcal' == $this->format) && ('x-' == substr($label, 0, 2))) {
            $this->xcaldecl[] = [
                'xmldecl' => 'ELEMENT'
                ,
                'ref'     => $label
                ,
                'type2'   => '(#PCDATA)',
            ];
        }
        if (!empty($attributes)) {
            $attributes = trim($attributes);
            if ('xcal' == $this->format) {
                $attributes2 = explode($this->intAttrDelimiter, $attributes);
                $attributes = null;
                foreach ($attributes2 as $aix => $attribute) {
                    $attrKVarr = explode('=', $attribute);
                    if (empty($attrKVarr[0])) {
                        continue;
                    }
                    if (!isset($attrKVarr[1])) {
                        $attrValue = $attrKVarr[0];
                        $attrKey = $aix;
                    } elseif (2 == count($attrKVarr)) {
                        $attrKey = strtolower($attrKVarr[0]);
                        $attrValue = $attrKVarr[1];
                    } else {
                        $attrKey = strtolower($attrKVarr[0]);
                        unset($attrKVarr[0]);
                        $attrValue = implode('=', $attrKVarr);
                    }
                    if (('attach' == $label) && (in_array($attrKey, ['fmttype', 'encoding', 'value']))) {
                        $attachInlineBinary = true;
                        if ('fmttype' == $attrKey) {
                            $attachfmttype = $attrKey . '=' . $attrValue;
                        }
                        continue;
                    } elseif (('categories' == $label) && ('language' == $attrKey)) {
                        $categoriesAttrLang = $attrKey . '=' . $attrValue;
                    } else {
                        $attributes .= (empty($attributes)) ? ' ' : $this->attributeDelimiter . ' ';
                        $attributes .= (!empty($attrKey)) ? $attrKey . '=' : null;
                        if (('"' == substr($attrValue, 0, 1)) && ('"' == substr($attrValue, -1))) {
                            $attrValue = substr($attrValue, 1, (strlen($attrValue) - 2));
                            $attrValue = str_replace('"', '', $attrValue);
                        }
                        $attributes .= '"' . htmlspecialchars($attrValue) . '"';
                    }
                }
            } else {
                $attributes = str_replace($this->intAttrDelimiter, $this->attributeDelimiter, $attributes);
            }
        }
        if (('xcal' == $this->format) &&
            ((('attach' == $label) && !$attachInlineBinary) || (in_array($label, ['tzurl', 'url'])))) {
            $pos = strrpos($content, "/");
            $docname = ($pos !== false) ? substr($content, (1 - strlen($content) + $pos)) : $content;
            $this->xcaldecl[] = [
                'xmldecl'  => 'ENTITY'
                ,
                'uri'      => $docname
                ,
                'ref'      => 'SYSTEM'
                ,
                'external' => $content
                ,
                'type'     => 'NDATA'
                ,
                'type2'    => 'BINERY',
            ];
            $attributes .= (empty($attributes)) ? ' ' : $this->attributeDelimiter . ' ';
            $attributes .= 'uri="' . $docname . '"';
            $content = null;
            if ('attach' == $label) {
                $attributes = str_replace($this->attributeDelimiter, $this->intAttrDelimiter, $attributes);
                $content = $this->nl . $this->_createElement('extref', $attributes, null);
                $attributes = null;
            }
        } elseif (('xcal' == $this->format) && ('attach' == $label) && $attachInlineBinary) {
            $content = $this->nl . $this->_createElement('b64bin', $attachfmttype, $content); // max one attribute
        }
        $output .= $attributes;
        if (!$content && ('0' != $content)) {
            switch ($this->format) {
                case 'xcal':
                    $output .= ' /';
                    $output .= $this->elementStart2 . $this->nl;
                    return $output;
                    break;
                default:
                    $output .= $this->elementStart2 . $this->valueInit;
                    return $this->_size75($output);
                    break;
            }
        }
        $output .= $this->elementStart2;
        $output .= $this->valueInit . $content;
        switch ($this->format) {
            case 'xcal':
                return $output . $this->elementEnd1 . $label . $this->elementEnd2;
                break;
            default:
                return $this->_size75($output);
                break;
        }
    }

    /**
     * creates formatted output for calendar component property parameters
     *
     * @param array $params optional
     * @param array $ctrKeys optional
     * @return string
     * @since 2.10.27 - 2012-01-16
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function _createParams($params = [], $ctrKeys = [])
    {
        if (!is_array($params) || empty($params)) {
            $params = [];
        }
        $attrLANG = $attr1 = $attr2 = $lang = null;
        $CNattrKey = (in_array('CN', $ctrKeys)) ? true : false;
        $LANGattrKey = (in_array('LANGUAGE', $ctrKeys)) ? true : false;
        $CNattrExist = $LANGattrExist = false;
        $xparams = [];
        foreach ($params as $paramKey => $paramValue) {
            if ((false !== strpos($paramValue, ':')) ||
                (false !== strpos($paramValue, ';')) ||
                (false !== strpos($paramValue, ','))) {
                $paramValue = '"' . $paramValue . '"';
            }
            if (ctype_digit((string)$paramKey)) {
                $xparams[] = $paramValue;
                continue;
            }
            $paramKey = strtoupper($paramKey);
            if (!in_array($paramKey, ['ALTREP', 'CN', 'DIR', 'ENCODING', 'FMTTYPE', 'LANGUAGE', 'RANGE', 'RELTYPE', 'SENT-BY', 'TZID', 'VALUE'])) {
                $xparams[$paramKey] = $paramValue;
            } else {
                $params[$paramKey] = $paramValue;
            }
        }
        ksort($xparams, SORT_STRING);
        foreach ($xparams as $paramKey => $paramValue) {
            if (ctype_digit((string)$paramKey)) {
                $attr2 .= $this->intAttrDelimiter . $paramValue;
            } else {
                $attr2 .= $this->intAttrDelimiter . "$paramKey=$paramValue";
            }
        }
        if (isset($params['FMTTYPE']) && !in_array('FMTTYPE', $ctrKeys)) {
            $attr1 .= $this->intAttrDelimiter . 'FMTTYPE=' . $params['FMTTYPE'] . $attr2;
            $attr2 = null;
        }
        if (isset($params['ENCODING']) && !in_array('ENCODING', $ctrKeys)) {
            if (!empty($attr2)) {
                $attr1 .= $attr2;
                $attr2 = null;
            }
            $attr1 .= $this->intAttrDelimiter . 'ENCODING=' . $params['ENCODING'];
        }
        if (isset($params['VALUE']) && !in_array('VALUE', $ctrKeys)) {
            $attr1 .= $this->intAttrDelimiter . 'VALUE=' . $params['VALUE'];
        }
        if (isset($params['TZID']) && !in_array('TZID', $ctrKeys)) {
            $attr1 .= $this->intAttrDelimiter . 'TZID=' . $params['TZID'];
        }
        if (isset($params['RANGE']) && !in_array('RANGE', $ctrKeys)) {
            $attr1 .= $this->intAttrDelimiter . 'RANGE=' . $params['RANGE'];
        }
        if (isset($params['RELTYPE']) && !in_array('RELTYPE', $ctrKeys)) {
            $attr1 .= $this->intAttrDelimiter . 'RELTYPE=' . $params['RELTYPE'];
        }
        if (isset($params['CN']) && $CNattrKey) {
            $attr1 = $this->intAttrDelimiter . 'CN=' . $params['CN'];
            $CNattrExist = true;
        }
        if (isset($params['DIR']) && in_array('DIR', $ctrKeys)) {
            $delim = (false !== strpos($params['DIR'], '"')) ? '' : '"';
            $attr1 .= $this->intAttrDelimiter . 'DIR=' . $delim . $params['DIR'] . $delim;
        }
        if (isset($params['SENT-BY']) && in_array('SENT-BY', $ctrKeys)) {
            $attr1 .= $this->intAttrDelimiter . 'SENT-BY=' . $params['SENT-BY'];
        }
        if (isset($params['ALTREP']) && in_array('ALTREP', $ctrKeys)) {
            $delim = (false !== strpos($params['ALTREP'], '"')) ? '' : '"';
            $attr1 .= $this->intAttrDelimiter . 'ALTREP=' . $delim . $params['ALTREP'] . $delim;
        }
        if (isset($params['LANGUAGE']) && $LANGattrKey) {
            $attrLANG .= $this->intAttrDelimiter . 'LANGUAGE=' . $params['LANGUAGE'];
            $LANGattrExist = true;
        }
        if (!$LANGattrExist) {
            $lang = $this->getConfig('language');
            if (($CNattrExist || $LANGattrKey) && $lang) {
                $attrLANG .= $this->intAttrDelimiter . 'LANGUAGE=' . $lang;
            }
        }
        return $attr1 . $attrLANG . $attr2;
    }

    /**
     * creates formatted output for calendar component property data value type recur
     *
     * @param array $recurlabel
     * @param array $recurdata
     * @return string
     * @since 2.4.8 - 2008-10-22
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function _format_recur($recurlabel, $recurdata)
    {
        $output = null;
        foreach ($recurdata as $therule) {
            if (empty($therule['value'])) {
                if ($this->getConfig('allowEmpty')) {
                    $output .= $this->_createElement($recurlabel);
                }
                continue;
            }
            $attributes = (isset($therule['params'])) ? $this->_createParams($therule['params']) : null;
            $content1 = $content2 = null;
            foreach ($therule['value'] as $rulelabel => $rulevalue) {
                switch ($rulelabel) {
                    case 'FREQ':
                    {
                        $content1 .= "FREQ=$rulevalue";
                        break;
                    }
                    case 'UNTIL':
                    {
                        $content2 .= ";UNTIL=";
                        $content2 .= iCalUtilityFunctions::_format_date_time($rulevalue);
                        break;
                    }
                    case 'COUNT':
                    case 'INTERVAL':
                    case 'WKST':
                    {
                        $content2 .= ";$rulelabel=$rulevalue";
                        break;
                    }
                    case 'BYSECOND':
                    case 'BYMINUTE':
                    case 'BYHOUR':
                    case 'BYMONTHDAY':
                    case 'BYYEARDAY':
                    case 'BYWEEKNO':
                    case 'BYMONTH':
                    case 'BYSETPOS':
                    {
                        $content2 .= ";$rulelabel=";
                        if (is_array($rulevalue)) {
                            foreach ($rulevalue as $vix => $valuePart) {
                                $content2 .= ($vix) ? ',' : null;
                                $content2 .= $valuePart;
                            }
                        } else {
                            $content2 .= $rulevalue;
                        }
                        break;
                    }
                    case 'BYDAY':
                    {
                        $content2 .= ";$rulelabel=";
                        $bydaycnt = 0;
                        foreach ($rulevalue as $vix => $valuePart) {
                            $content21 = $content22 = null;
                            if (is_array($valuePart)) {
                                $content2 .= ($bydaycnt) ? ',' : null;
                                foreach ($valuePart as $vix2 => $valuePart2) {
                                    if ('DAY' != strtoupper($vix2)) {
                                        $content21 .= $valuePart2;
                                    } else {
                                        $content22 .= $valuePart2;
                                    }
                                }
                                $content2 .= $content21 . $content22;
                                $bydaycnt++;
                            } else {
                                $content2 .= ($bydaycnt) ? ',' : null;
                                if ('DAY' != strtoupper($vix)) {
                                    $content21 .= $valuePart;
                                } else {
                                    $content22 .= $valuePart;
                                    $bydaycnt++;
                                }
                                $content2 .= $content21 . $content22;
                            }
                        }
                        break;
                    }
                    default:
                    {
                        $content2 .= ";$rulelabel=$rulevalue";
                        break;
                    }
                }
            }
            $output .= $this->_createElement($recurlabel, $attributes, $content1 . $content2);
        }
        return $output;
    }

    /**
     * check if property not exists within component
     *
     * @param string $propName
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.5.1 - 2008-10-15
     */
    function _notExistProp($propName)
    {
        if (empty($propName)) {
            return false;
        } // when deleting x-prop, an empty propName may be used=allowed
        $propName = strtolower($propName);
        if ('last-modified' == $propName) {
            if (!isset($this->lastmodified)) {
                return true;
            }
        } elseif ('percent-complete' == $propName) {
            if (!isset($this->percentcomplete)) {
                return true;
            }
        } elseif ('recurrence-id' == $propName) {
            if (!isset($this->recurrenceid)) {
                return true;
            }
        } elseif ('related-to' == $propName) {
            if (!isset($this->relatedto)) {
                return true;
            }
        } elseif ('request-status' == $propName) {
            if (!isset($this->requeststatus)) {
                return true;
            }
        } elseif (('x-' != substr($propName, 0, 2)) && !isset($this->$propName)) {
            return true;
        }
        return false;
    }
    /*********************************************************************************/
    /*********************************************************************************/
    /**
     * get general component config variables or info about subcomponents
     *
     * @param mixed $config
     * @return value
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.9.6 - 2011-05-14
     */
    function getConfig($config = false)
    {
        if (!$config) {
            $return = [];
            $return['ALLOWEMPTY'] = $this->getConfig('ALLOWEMPTY');
            $return['FORMAT'] = $this->getConfig('FORMAT');
            if (false !== ($lang = $this->getConfig('LANGUAGE'))) {
                $return['LANGUAGE'] = $lang;
            }
            $return['NEWLINECHAR'] = $this->getConfig('NEWLINECHAR');
            $return['TZTD'] = $this->getConfig('TZID');
            $return['UNIQUE_ID'] = $this->getConfig('UNIQUE_ID');
            return $return;
        }
        switch (strtoupper($config)) {
            case 'ALLOWEMPTY':
                return $this->allowEmpty;
                break;
            case 'COMPSINFO':
                unset($this->compix);
                $info = [];
                if (isset($this->components)) {
                    foreach ($this->components as $cix => $component) {
                        if (empty($component)) {
                            continue;
                        }
                        $info[$cix]['ordno'] = $cix + 1;
                        $info[$cix]['type'] = $component->objName;
                        $info[$cix]['uid'] = $component->getProperty('uid');
                        $info[$cix]['props'] = $component->getConfig('propinfo');
                        $info[$cix]['sub'] = $component->getConfig('compsinfo');
                    }
                }
                return $info;
                break;
            case 'FORMAT':
                return $this->format;
                break;
            case 'LANGUAGE':
                // get language for calendar component as defined in [RFC 1766]
                return $this->language;
                break;
            case 'NL':
            case 'NEWLINECHAR':
                return $this->nl;
                break;
            case 'PROPINFO':
                $output = [];
                if (!in_array($this->objName, ['valarm', 'vtimezone', 'standard', 'daylight'])) {
                    if (empty($this->uid['value'])) {
                        $this->_makeuid();
                    }
                    $output['UID'] = 1;
                }
                if (!empty($this->dtstamp)) {
                    $output['DTSTAMP'] = 1;
                }
                if (!empty($this->summary)) {
                    $output['SUMMARY'] = 1;
                }
                if (!empty($this->description)) {
                    $output['DESCRIPTION'] = count($this->description);
                }
                if (!empty($this->dtstart)) {
                    $output['DTSTART'] = 1;
                }
                if (!empty($this->dtend)) {
                    $output['DTEND'] = 1;
                }
                if (!empty($this->due)) {
                    $output['DUE'] = 1;
                }
                if (!empty($this->duration)) {
                    $output['DURATION'] = 1;
                }
                if (!empty($this->rrule)) {
                    $output['RRULE'] = count($this->rrule);
                }
                if (!empty($this->rdate)) {
                    $output['RDATE'] = count($this->rdate);
                }
                if (!empty($this->exdate)) {
                    $output['EXDATE'] = count($this->exdate);
                }
                if (!empty($this->exrule)) {
                    $output['EXRULE'] = count($this->exrule);
                }
                if (!empty($this->action)) {
                    $output['ACTION'] = 1;
                }
                if (!empty($this->attach)) {
                    $output['ATTACH'] = count($this->attach);
                }
                if (!empty($this->attendee)) {
                    $output['ATTENDEE'] = count($this->attendee);
                }
                if (!empty($this->categories)) {
                    $output['CATEGORIES'] = count($this->categories);
                }
                if (!empty($this->class)) {
                    $output['CLASS'] = 1;
                }
                if (!empty($this->comment)) {
                    $output['COMMENT'] = count($this->comment);
                }
                if (!empty($this->completed)) {
                    $output['COMPLETED'] = 1;
                }
                if (!empty($this->contact)) {
                    $output['CONTACT'] = count($this->contact);
                }
                if (!empty($this->created)) {
                    $output['CREATED'] = 1;
                }
                if (!empty($this->freebusy)) {
                    $output['FREEBUSY'] = count($this->freebusy);
                }
                if (!empty($this->geo)) {
                    $output['GEO'] = 1;
                }
                if (!empty($this->lastmodified)) {
                    $output['LAST-MODIFIED'] = 1;
                }
                if (!empty($this->location)) {
                    $output['LOCATION'] = 1;
                }
                if (!empty($this->organizer)) {
                    $output['ORGANIZER'] = 1;
                }
                if (!empty($this->percentcomplete)) {
                    $output['PERCENT-COMPLETE'] = 1;
                }
                if (!empty($this->priority)) {
                    $output['PRIORITY'] = 1;
                }
                if (!empty($this->recurrenceid)) {
                    $output['RECURRENCE-ID'] = 1;
                }
                if (!empty($this->relatedto)) {
                    $output['RELATED-TO'] = count($this->relatedto);
                }
                if (!empty($this->repeat)) {
                    $output['REPEAT'] = 1;
                }
                if (!empty($this->requeststatus)) {
                    $output['REQUEST-STATUS'] = count($this->requeststatus);
                }
                if (!empty($this->resources)) {
                    $output['RESOURCES'] = count($this->resources);
                }
                if (!empty($this->sequence)) {
                    $output['SEQUENCE'] = 1;
                }
                if (!empty($this->sequence)) {
                    $output['SEQUENCE'] = 1;
                }
                if (!empty($this->status)) {
                    $output['STATUS'] = 1;
                }
                if (!empty($this->transp)) {
                    $output['TRANSP'] = 1;
                }
                if (!empty($this->trigger)) {
                    $output['TRIGGER'] = 1;
                }
                if (!empty($this->tzid)) {
                    $output['TZID'] = 1;
                }
                if (!empty($this->tzname)) {
                    $output['TZNAME'] = count($this->tzname);
                }
                if (!empty($this->tzoffsetfrom)) {
                    $output['TZOFFSETFROM'] = 1;
                }
                if (!empty($this->tzoffsetto)) {
                    $output['TZOFFSETTO'] = 1;
                }
                if (!empty($this->tzurl)) {
                    $output['TZURL'] = 1;
                }
                if (!empty($this->url)) {
                    $output['URL'] = 1;
                }
                if (!empty($this->xprop)) {
                    $output['X-PROP'] = count($this->xprop);
                }
                return $output;
                break;
            case 'TZID':
                return $this->dtzid;
                break;
            case 'UNIQUE_ID':
                if (empty($this->unique_id)) {
                    $this->unique_id = (isset($_SERVER['SERVER_NAME'])) ? gethostbyname($_SERVER['SERVER_NAME']) : 'localhost';
                }
                return $this->unique_id;
                break;
        }
    }

    /**
     * general component config setting
     *
     * @param mixed $config
     * @param string $value
     * @param bool $softUpdate
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.10.18 - 2011-10-28
     */
    function setConfig($config, $value = false, $softUpdate = false)
    {
        if (is_array($config)) {
            $ak = array_keys($config);
            foreach ($ak as $k) {
                if ('NEWLINECHAR' == strtoupper($k)) {
                    if (false === $this->setConfig('NEWLINECHAR', $config[$k])) {
                        return false;
                    }
                    unset($config[$k]);
                    break;
                }
            }
            foreach ($config as $cKey => $cValue) {
                if (false === $this->setConfig($cKey, $cValue, $softUpdate)) {
                    return false;
                }
            }
            return true;
        }
        $res = false;
        switch (strtoupper($config)) {
            case 'ALLOWEMPTY':
                $this->allowEmpty = $value;
                $subcfg = ['ALLOWEMPTY' => $value];
                $res = true;
                break;
            case 'FORMAT':
                $value = trim(strtolower($value));
                $this->format = $value;
                $this->_createFormat();
                $subcfg = ['FORMAT' => $value];
                $res = true;
                break;
            case 'LANGUAGE':
                // set language for calendar component as defined in [RFC 1766]
                $value = trim($value);
                if (empty($this->language) || !$softUpdate) {
                    $this->language = $value;
                }
                $subcfg = ['LANGUAGE' => $value];
                $res = true;
                break;
            case 'NL':
            case 'NEWLINECHAR':
                $this->nl = $value;
                $this->_createFormat();
                $subcfg = ['NL' => $value];
                $res = true;
                break;
            case 'TZID':
                $this->dtzid = $value;
                $subcfg = ['TZID' => $value];
                $res = true;
                break;
            case 'UNIQUE_ID':
                $value = trim($value);
                $this->unique_id = $value;
                $subcfg = ['UNIQUE_ID' => $value];
                $res = true;
                break;
            default:  // any unvalid config key.. .
                return true;
        }
        if (!$res) {
            return false;
        }
        if (isset($subcfg) && !empty($this->components)) {
            foreach ($subcfg as $cfgkey => $cfgvalue) {
                foreach ($this->components as $cix => $component) {
                    $res = $component->setConfig($cfgkey, $cfgvalue, $softUpdate);
                    if (!$res) {
                        break 2;
                    }
                    $this->components[$cix] = $component->copy(); // PHP4 compliant
                }
            }
        }
        return $res;
    }
    /*********************************************************************************/
    /**
     * delete component property value
     *
     * @param mixed $propName , bool FALSE => X-property
     * @param int $propix , optional, if specific property is wanted in case of multiply occurences
     * @return bool, if successfull delete TRUE
     * @since 2.8.8 - 2011-03-15
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function deleteProperty($propName = false, $propix = false)
    {
        if ($this->_notExistProp($propName)) {
            return false;
        }
        $propName = strtoupper($propName);
        if (in_array($propName, [
            'ATTACH',
            'ATTENDEE',
            'CATEGORIES',
            'COMMENT',
            'CONTACT',
            'DESCRIPTION',
            'EXDATE',
            'EXRULE',
            'FREEBUSY',
            'RDATE',
            'RELATED-TO',
            'RESOURCES',
            'RRULE',
            'REQUEST-STATUS',
            'TZNAME',
            'X-PROP',
        ])) {
            if (!$propix) {
                $propix = (isset($this->propdelix[$propName]) && ('X-PROP' != $propName)) ? $this->propdelix[$propName] + 2 : 1;
            }
            $this->propdelix[$propName] = --$propix;
        }
        $return = false;
        switch ($propName) {
            case 'ACTION':
                if (!empty($this->action)) {
                    $this->action = '';
                    $return = true;
                }
                break;
            case 'ATTACH':
                return $this->deletePropertyM($this->attach, $this->propdelix[$propName]);
                break;
            case 'ATTENDEE':
                return $this->deletePropertyM($this->attendee, $this->propdelix[$propName]);
                break;
            case 'CATEGORIES':
                return $this->deletePropertyM($this->categories, $this->propdelix[$propName]);
                break;
            case 'CLASS':
                if (!empty($this->class)) {
                    $this->class = '';
                    $return = true;
                }
                break;
            case 'COMMENT':
                return $this->deletePropertyM($this->comment, $this->propdelix[$propName]);
                break;
            case 'COMPLETED':
                if (!empty($this->completed)) {
                    $this->completed = '';
                    $return = true;
                }
                break;
            case 'CONTACT':
                return $this->deletePropertyM($this->contact, $this->propdelix[$propName]);
                break;
            case 'CREATED':
                if (!empty($this->created)) {
                    $this->created = '';
                    $return = true;
                }
                break;
            case 'DESCRIPTION':
                return $this->deletePropertyM($this->description, $this->propdelix[$propName]);
                break;
            case 'DTEND':
                if (!empty($this->dtend)) {
                    $this->dtend = '';
                    $return = true;
                }
                break;
            case 'DTSTAMP':
                if (in_array($this->objName, ['valarm', 'vtimezone', 'standard', 'daylight'])) {
                    return false;
                }
                if (!empty($this->dtstamp)) {
                    $this->dtstamp = '';
                    $return = true;
                }
                break;
            case 'DTSTART':
                if (!empty($this->dtstart)) {
                    $this->dtstart = '';
                    $return = true;
                }
                break;
            case 'DUE':
                if (!empty($this->due)) {
                    $this->due = '';
                    $return = true;
                }
                break;
            case 'DURATION':
                if (!empty($this->duration)) {
                    $this->duration = '';
                    $return = true;
                }
                break;
            case 'EXDATE':
                return $this->deletePropertyM($this->exdate, $this->propdelix[$propName]);
                break;
            case 'EXRULE':
                return $this->deletePropertyM($this->exrule, $this->propdelix[$propName]);
                break;
            case 'FREEBUSY':
                return $this->deletePropertyM($this->freebusy, $this->propdelix[$propName]);
                break;
            case 'GEO':
                if (!empty($this->geo)) {
                    $this->geo = '';
                    $return = true;
                }
                break;
            case 'LAST-MODIFIED':
                if (!empty($this->lastmodified)) {
                    $this->lastmodified = '';
                    $return = true;
                }
                break;
            case 'LOCATION':
                if (!empty($this->location)) {
                    $this->location = '';
                    $return = true;
                }
                break;
            case 'ORGANIZER':
                if (!empty($this->organizer)) {
                    $this->organizer = '';
                    $return = true;
                }
                break;
            case 'PERCENT-COMPLETE':
                if (!empty($this->percentcomplete)) {
                    $this->percentcomplete = '';
                    $return = true;
                }
                break;
            case 'PRIORITY':
                if (!empty($this->priority)) {
                    $this->priority = '';
                    $return = true;
                }
                break;
            case 'RDATE':
                return $this->deletePropertyM($this->rdate, $this->propdelix[$propName]);
                break;
            case 'RECURRENCE-ID':
                if (!empty($this->recurrenceid)) {
                    $this->recurrenceid = '';
                    $return = true;
                }
                break;
            case 'RELATED-TO':
                return $this->deletePropertyM($this->relatedto, $this->propdelix[$propName]);
                break;
            case 'REPEAT':
                if (!empty($this->repeat)) {
                    $this->repeat = '';
                    $return = true;
                }
                break;
            case 'REQUEST-STATUS':
                return $this->deletePropertyM($this->requeststatus, $this->propdelix[$propName]);
                break;
            case 'RESOURCES':
                return $this->deletePropertyM($this->resources, $this->propdelix[$propName]);
                break;
            case 'RRULE':
                return $this->deletePropertyM($this->rrule, $this->propdelix[$propName]);
                break;
            case 'SEQUENCE':
                if (!empty($this->sequence)) {
                    $this->sequence = '';
                    $return = true;
                }
                break;
            case 'STATUS':
                if (!empty($this->status)) {
                    $this->status = '';
                    $return = true;
                }
                break;
            case 'SUMMARY':
                if (!empty($this->summary)) {
                    $this->summary = '';
                    $return = true;
                }
                break;
            case 'TRANSP':
                if (!empty($this->transp)) {
                    $this->transp = '';
                    $return = true;
                }
                break;
            case 'TRIGGER':
                if (!empty($this->trigger)) {
                    $this->trigger = '';
                    $return = true;
                }
                break;
            case 'TZID':
                if (!empty($this->tzid)) {
                    $this->tzid = '';
                    $return = true;
                }
                break;
            case 'TZNAME':
                return $this->deletePropertyM($this->tzname, $this->propdelix[$propName]);
                break;
            case 'TZOFFSETFROM':
                if (!empty($this->tzoffsetfrom)) {
                    $this->tzoffsetfrom = '';
                    $return = true;
                }
                break;
            case 'TZOFFSETTO':
                if (!empty($this->tzoffsetto)) {
                    $this->tzoffsetto = '';
                    $return = true;
                }
                break;
            case 'TZURL':
                if (!empty($this->tzurl)) {
                    $this->tzurl = '';
                    $return = true;
                }
                break;
            case 'UID':
                if (in_array($this->objName, ['valarm', 'vtimezone', 'standard', 'daylight'])) {
                    return false;
                }
                if (!empty($this->uid)) {
                    $this->uid = '';
                    $return = true;
                }
                break;
            case 'URL':
                if (!empty($this->url)) {
                    $this->url = '';
                    $return = true;
                }
                break;
            default:
                $reduced = '';
                if ($propName != 'X-PROP') {
                    if (!isset($this->xprop[$propName])) {
                        return false;
                    }
                    foreach ($this->xprop as $k => $a) {
                        if (($k != $propName) && !empty($a)) {
                            $reduced[$k] = $a;
                        }
                    }
                } else {
                    if (count($this->xprop) <= $propix) {
                        unset($this->propdelix[$propName]);
                        return false;
                    }
                    $xpropno = 0;
                    foreach ($this->xprop as $xpropkey => $xpropvalue) {
                        if ($propix != $xpropno) {
                            $reduced[$xpropkey] = $xpropvalue;
                        }
                        $xpropno++;
                    }
                }
                $this->xprop = $reduced;
                if (empty($this->xprop)) {
                    unset($this->propdelix[$propName]);
                    return false;
                }
                return true;
        }
        return $return;
    }
    /*********************************************************************************/
    /**
     * delete component property value, fixing components with multiple occurencies
     *
     * @param array $multiprop , reference to a component property
     * @param int $propix , reference to removal counter
     * @return bool TRUE
     * @since 2.8.8 - 2011-03-15
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function deletePropertyM(&$multiprop, &$propix)
    {
        if (isset($multiprop[$propix])) {
            unset($multiprop[$propix]);
        }
        if (empty($multiprop)) {
            $multiprop = '';
            unset($propix);
            return false;
        } else {
            return true;
        }
    }

    /**
     * get component property value/params
     *
     * if property has multiply values, consequtive function calls are needed
     *
     * @param string $propName , optional
     * @param int @propix, optional, if specific property is wanted in case of multiply occurences
     * @param bool $inclParam =FALSE
     * @param bool $specform =FALSE
     * @return mixed
     * @since 2.11.3 - 2012-01-10
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function getProperty($propName = false, $propix = false, $inclParam = false, $specform = false)
    {
        if ($this->_notExistProp($propName)) {
            return false;
        }
        $propName = ($propName) ? strtoupper($propName) : 'X-PROP';
        if (in_array($propName, [
            'ATTACH',
            'ATTENDEE',
            'CATEGORIES',
            'COMMENT',
            'CONTACT',
            'DESCRIPTION',
            'EXDATE',
            'EXRULE',
            'FREEBUSY',
            'RDATE',
            'RELATED-TO',
            'RESOURCES',
            'RRULE',
            'REQUEST-STATUS',
            'TZNAME',
            'X-PROP',
        ])) {
            if (!$propix) {
                $propix = (isset($this->propix[$propName])) ? $this->propix[$propName] + 2 : 1;
            }
            $this->propix[$propName] = --$propix;
        }
        switch ($propName) {
            case 'ACTION':
                if (!empty($this->action['value'])) {
                    return ($inclParam) ? $this->action : $this->action['value'];
                }
                break;
            case 'ATTACH':
                $ak = (is_array($this->attach)) ? array_keys($this->attach) : [];
                while (is_array($this->attach) && !isset($this->attach[$propix]) && (0 < count($this->attach)) && ($propix < end($ak))) {
                    $propix++;
                }
                $this->propix[$propName] = $propix;
                if (!isset($this->attach[$propix])) {
                    unset($this->propix[$propName]);
                    return false;
                }
                return ($inclParam) ? $this->attach[$propix] : $this->attach[$propix]['value'];
                break;
            case 'ATTENDEE':
                $ak = (is_array($this->attendee)) ? array_keys($this->attendee) : [];
                while (is_array($this->attendee) && !isset($this->attendee[$propix]) && (0 < count($this->attendee)) && ($propix < end($ak))) {
                    $propix++;
                }
                $this->propix[$propName] = $propix;
                if (!isset($this->attendee[$propix])) {
                    unset($this->propix[$propName]);
                    return false;
                }
                return ($inclParam) ? $this->attendee[$propix] : $this->attendee[$propix]['value'];
                break;
            case 'CATEGORIES':
                $ak = (is_array($this->categories)) ? array_keys($this->categories) : [];
                while (is_array($this->categories) && !isset($this->categories[$propix]) && (0 < count($this->categories)) && ($propix < end($ak))) {
                    $propix++;
                }
                $this->propix[$propName] = $propix;
                if (!isset($this->categories[$propix])) {
                    unset($this->propix[$propName]);
                    return false;
                }
                return ($inclParam) ? $this->categories[$propix] : $this->categories[$propix]['value'];
                break;
            case 'CLASS':
                if (!empty($this->class['value'])) {
                    return ($inclParam) ? $this->class : $this->class['value'];
                }
                break;
            case 'COMMENT':
                $ak = (is_array($this->comment)) ? array_keys($this->comment) : [];
                while (is_array($this->comment) && !isset($this->comment[$propix]) && (0 < count($this->comment)) && ($propix < end($ak))) {
                    $propix++;
                }
                $this->propix[$propName] = $propix;
                if (!isset($this->comment[$propix])) {
                    unset($this->propix[$propName]);
                    return false;
                }
                return ($inclParam) ? $this->comment[$propix] : $this->comment[$propix]['value'];
                break;
            case 'COMPLETED':
                if (!empty($this->completed['value'])) {
                    return ($inclParam) ? $this->completed : $this->completed['value'];
                }
                break;
            case 'CONTACT':
                $ak = (is_array($this->contact)) ? array_keys($this->contact) : [];
                while (is_array($this->contact) && !isset($this->contact[$propix]) && (0 < count($this->contact)) && ($propix < end($ak))) {
                    $propix++;
                }
                $this->propix[$propName] = $propix;
                if (!isset($this->contact[$propix])) {
                    unset($this->propix[$propName]);
                    return false;
                }
                return ($inclParam) ? $this->contact[$propix] : $this->contact[$propix]['value'];
                break;
            case 'CREATED':
                if (!empty($this->created['value'])) {
                    return ($inclParam) ? $this->created : $this->created['value'];
                }
                break;
            case 'DESCRIPTION':
                $ak = (is_array($this->description)) ? array_keys($this->description) : [];
                while (is_array($this->description) && !isset($this->description[$propix]) && (0 < count($this->description)) && ($propix < end($ak))) {
                    $propix++;
                }
                $this->propix[$propName] = $propix;
                if (!isset($this->description[$propix])) {
                    unset($this->propix[$propName]);
                    return false;
                }
                return ($inclParam) ? $this->description[$propix] : $this->description[$propix]['value'];
                break;
            case 'DTEND':
                if (!empty($this->dtend['value'])) {
                    return ($inclParam) ? $this->dtend : $this->dtend['value'];
                }
                break;
            case 'DTSTAMP':
                if (in_array($this->objName, ['valarm', 'vtimezone', 'standard', 'daylight'])) {
                    return;
                }
                if (!isset($this->dtstamp['value'])) {
                    $this->_makeDtstamp();
                }
                return ($inclParam) ? $this->dtstamp : $this->dtstamp['value'];
                break;
            case 'DTSTART':
                if (!empty($this->dtstart['value'])) {
                    return ($inclParam) ? $this->dtstart : $this->dtstart['value'];
                }
                break;
            case 'DUE':
                if (!empty($this->due['value'])) {
                    return ($inclParam) ? $this->due : $this->due['value'];
                }
                break;
            case 'DURATION':
                if (!isset($this->duration['value'])) {
                    return false;
                }
                $value = ($specform && isset($this->dtstart['value']) && isset($this->duration['value'])) ? iCalUtilityFunctions::_duration2date($this->dtstart['value'],
                    $this->duration['value']) : $this->duration['value'];
                return ($inclParam) ? ['value' => $value, 'params' => $this->duration['params']] : $value;
                break;
            case 'EXDATE':
                $ak = (is_array($this->exdate)) ? array_keys($this->exdate) : [];
                while (is_array($this->exdate) && !isset($this->exdate[$propix]) && (0 < count($this->exdate)) && ($propix < end($ak))) {
                    $propix++;
                }
                $this->propix[$propName] = $propix;
                if (!isset($this->exdate[$propix])) {
                    unset($this->propix[$propName]);
                    return false;
                }
                return ($inclParam) ? $this->exdate[$propix] : $this->exdate[$propix]['value'];
                break;
            case 'EXRULE':
                $ak = (is_array($this->exrule)) ? array_keys($this->exrule) : [];
                while (is_array($this->exrule) && !isset($this->exrule[$propix]) && (0 < count($this->exrule)) && ($propix < end($ak))) {
                    $propix++;
                }
                $this->propix[$propName] = $propix;
                if (!isset($this->exrule[$propix])) {
                    unset($this->propix[$propName]);
                    return false;
                }
                return ($inclParam) ? $this->exrule[$propix] : $this->exrule[$propix]['value'];
                break;
            case 'FREEBUSY':
                $ak = (is_array($this->freebusy)) ? array_keys($this->freebusy) : [];
                while (is_array($this->freebusy) && !isset($this->freebusy[$propix]) && (0 < count($this->freebusy)) && ($propix < end($ak))) {
                    $propix++;
                }
                $this->propix[$propName] = $propix;
                if (!isset($this->freebusy[$propix])) {
                    unset($this->propix[$propName]);
                    return false;
                }
                return ($inclParam) ? $this->freebusy[$propix] : $this->freebusy[$propix]['value'];
                break;
            case 'GEO':
                if (!empty($this->geo['value'])) {
                    return ($inclParam) ? $this->geo : $this->geo['value'];
                }
                break;
            case 'LAST-MODIFIED':
                if (!empty($this->lastmodified['value'])) {
                    return ($inclParam) ? $this->lastmodified : $this->lastmodified['value'];
                }
                break;
            case 'LOCATION':
                if (!empty($this->location['value'])) {
                    return ($inclParam) ? $this->location : $this->location['value'];
                }
                break;
            case 'ORGANIZER':
                if (!empty($this->organizer['value'])) {
                    return ($inclParam) ? $this->organizer : $this->organizer['value'];
                }
                break;
            case 'PERCENT-COMPLETE':
                if (!empty($this->percentcomplete['value']) || (isset($this->percentcomplete['value']) && ('0' == $this->percentcomplete['value']))) {
                    return ($inclParam) ? $this->percentcomplete : $this->percentcomplete['value'];
                }
                break;
            case 'PRIORITY':
                if (!empty($this->priority['value']) || (isset($this->priority['value']) && ('0' == $this->priority['value']))) {
                    return ($inclParam) ? $this->priority : $this->priority['value'];
                }
                break;
            case 'RDATE':
                $ak = (is_array($this->rdate)) ? array_keys($this->rdate) : [];
                while (is_array($this->rdate) && !isset($this->rdate[$propix]) && (0 < count($this->rdate)) && ($propix < end($ak))) {
                    $propix++;
                }
                $this->propix[$propName] = $propix;
                if (!isset($this->rdate[$propix])) {
                    unset($this->propix[$propName]);
                    return false;
                }
                return ($inclParam) ? $this->rdate[$propix] : $this->rdate[$propix]['value'];
                break;
            case 'RECURRENCE-ID':
                if (!empty($this->recurrenceid['value'])) {
                    return ($inclParam) ? $this->recurrenceid : $this->recurrenceid['value'];
                }
                break;
            case 'RELATED-TO':
                $ak = (is_array($this->relatedto)) ? array_keys($this->relatedto) : [];
                while (is_array($this->relatedto) && !isset($this->relatedto[$propix]) && (0 < count($this->relatedto)) && ($propix < end($ak))) {
                    $propix++;
                }
                $this->propix[$propName] = $propix;
                if (!isset($this->relatedto[$propix])) {
                    unset($this->propix[$propName]);
                    return false;
                }
                return ($inclParam) ? $this->relatedto[$propix] : $this->relatedto[$propix]['value'];
                break;
            case 'REPEAT':
                if (!empty($this->repeat['value']) || (isset($this->repeat['value']) && ('0' == $this->repeat['value']))) {
                    return ($inclParam) ? $this->repeat : $this->repeat['value'];
                }
                break;
            case 'REQUEST-STATUS':
                $ak = (is_array($this->requeststatus)) ? array_keys($this->requeststatus) : [];
                while (is_array($this->requeststatus) && !isset($this->requeststatus[$propix]) && (0 < count($this->requeststatus)) && ($propix < end($ak))) {
                    $propix++;
                }
                $this->propix[$propName] = $propix;
                if (!isset($this->requeststatus[$propix])) {
                    unset($this->propix[$propName]);
                    return false;
                }
                return ($inclParam) ? $this->requeststatus[$propix] : $this->requeststatus[$propix]['value'];
                break;
            case 'RESOURCES':
                $ak = (is_array($this->resources)) ? array_keys($this->resources) : [];
                while (is_array($this->resources) && !isset($this->resources[$propix]) && (0 < count($this->resources)) && ($propix < end($ak))) {
                    $propix++;
                }
                $this->propix[$propName] = $propix;
                if (!isset($this->resources[$propix])) {
                    unset($this->propix[$propName]);
                    return false;
                }
                return ($inclParam) ? $this->resources[$propix] : $this->resources[$propix]['value'];
                break;
            case 'RRULE':
                $ak = (is_array($this->rrule)) ? array_keys($this->rrule) : [];
                while (is_array($this->rrule) && !isset($this->rrule[$propix]) && (0 < count($this->rrule)) && ($propix < end($ak))) {
                    $propix++;
                }
                $this->propix[$propName] = $propix;
                if (!isset($this->rrule[$propix])) {
                    unset($this->propix[$propName]);
                    return false;
                }
                return ($inclParam) ? $this->rrule[$propix] : $this->rrule[$propix]['value'];
                break;
            case 'SEQUENCE':
                if (isset($this->sequence['value']) && (isset($this->sequence['value']) && ('0' <= $this->sequence['value']))) {
                    return ($inclParam) ? $this->sequence : $this->sequence['value'];
                }
                break;
            case 'STATUS':
                if (!empty($this->status['value'])) {
                    return ($inclParam) ? $this->status : $this->status['value'];
                }
                break;
            case 'SUMMARY':
                if (!empty($this->summary['value'])) {
                    return ($inclParam) ? $this->summary : $this->summary['value'];
                }
                break;
            case 'TRANSP':
                if (!empty($this->transp['value'])) {
                    return ($inclParam) ? $this->transp : $this->transp['value'];
                }
                break;
            case 'TRIGGER':
                if (!empty($this->trigger['value'])) {
                    return ($inclParam) ? $this->trigger : $this->trigger['value'];
                }
                break;
            case 'TZID':
                if (!empty($this->tzid['value'])) {
                    return ($inclParam) ? $this->tzid : $this->tzid['value'];
                }
                break;
            case 'TZNAME':
                $ak = (is_array($this->tzname)) ? array_keys($this->tzname) : [];
                while (is_array($this->tzname) && !isset($this->tzname[$propix]) && (0 < count($this->tzname)) && ($propix < end($ak))) {
                    $propix++;
                }
                $this->propix[$propName] = $propix;
                if (!isset($this->tzname[$propix])) {
                    unset($this->propix[$propName]);
                    return false;
                }
                return ($inclParam) ? $this->tzname[$propix] : $this->tzname[$propix]['value'];
                break;
            case 'TZOFFSETFROM':
                if (!empty($this->tzoffsetfrom['value'])) {
                    return ($inclParam) ? $this->tzoffsetfrom : $this->tzoffsetfrom['value'];
                }
                break;
            case 'TZOFFSETTO':
                if (!empty($this->tzoffsetto['value'])) {
                    return ($inclParam) ? $this->tzoffsetto : $this->tzoffsetto['value'];
                }
                break;
            case 'TZURL':
                if (!empty($this->tzurl['value'])) {
                    return ($inclParam) ? $this->tzurl : $this->tzurl['value'];
                }
                break;
            case 'UID':
                if (in_array($this->objName, ['valarm', 'vtimezone', 'standard', 'daylight'])) {
                    return false;
                }
                if (empty($this->uid['value'])) {
                    $this->_makeuid();
                }
                return ($inclParam) ? $this->uid : $this->uid['value'];
                break;
            case 'URL':
                if (!empty($this->url['value'])) {
                    return ($inclParam) ? $this->url : $this->url['value'];
                }
                break;
            default:
                if ($propName != 'X-PROP') {
                    if (!isset($this->xprop[$propName])) {
                        return false;
                    }
                    return ($inclParam) ? [$propName, $this->xprop[$propName]]
                        : [$propName, $this->xprop[$propName]['value']];
                } else {
                    if (empty($this->xprop)) {
                        return false;
                    }
                    $xpropno = 0;
                    foreach ($this->xprop as $xpropkey => $xpropvalue) {
                        if ($propix == $xpropno) {
                            return ($inclParam) ? [$xpropkey, $this->xprop[$xpropkey]]
                                : [$xpropkey, $this->xprop[$xpropkey]['value']];
                        } else {
                            $xpropno++;
                        }
                    }
                    return false; // not found ??
                }
        }
        return false;
    }

    /**
     * returns calendar property unique values for 'CATEGORIES', 'RESOURCES' or 'ATTENDEE' and each number of ocurrence
     *
     * @param string $propName
     * @param array $output , incremented result array
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.8.8 - 2011-04-13
     */
    function _getProperties($propName, &$output)
    {
        if (!in_array(strtoupper($propName), ['ATTENDEE', 'CATEGORIES', 'RESOURCES'])) {
            return output;
        }
        while (false !== ($content = $this->getProperty($propName))) {
            if (is_array($content)) {
                foreach ($content as $part) {
                    if (false !== strpos($part, ',')) {
                        $part = explode(',', $part);
                        foreach ($part as $thePart) {
                            $thePart = trim($thePart);
                            if (!empty($thePart)) {
                                if (!isset($output[$thePart])) {
                                    $output[$thePart] = 1;
                                } else {
                                    $output[$thePart] += 1;
                                }
                            }
                        }
                    } else {
                        $part = trim($part);
                        if (!isset($output[$part])) {
                            $output[$part] = 1;
                        } else {
                            $output[$part] += 1;
                        }
                    }
                }
            } elseif (false !== strpos($content, ',')) {
                $content = explode(',', $content);
                foreach ($content as $thePart) {
                    $thePart = trim($thePart);
                    if (!empty($thePart)) {
                        if (!isset($output[$thePart])) {
                            $output[$thePart] = 1;
                        } else {
                            $output[$thePart] += 1;
                        }
                    }
                }
            } else {
                $content = trim($content);
                if (!empty($content)) {
                    if (!isset($output[$content])) {
                        $output[$content] = 1;
                    } else {
                        $output[$content] += 1;
                    }
                }
            }
        }
        ksort($output);
        return $output;
    }

    /**
     * general component property setting
     *
     * @param mixed $args variable number of function arguments,
     *                    first argument is ALWAYS component name,
     *                    second ALWAYS component value!
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.5.1 - 2008-11-05
     */
    function setProperty()
    {
        $numargs = func_num_args();
        if (1 > $numargs) {
            return false;
        }
        $arglist = func_get_args();
        if ($this->_notExistProp($arglist[0])) {
            return false;
        }
        if (!$this->getConfig('allowEmpty') && (!isset($arglist[1]) || empty($arglist[1]))) {
            return false;
        }
        $arglist[0] = strtoupper($arglist[0]);
        for ($argix = $numargs; $argix < 12; $argix++) {
            if (!isset($arglist[$argix])) {
                $arglist[$argix] = null;
            }
        }
        switch ($arglist[0]) {
            case 'ACTION':
                return $this->setAction($arglist[1], $arglist[2]);
            case 'ATTACH':
                return $this->setAttach($arglist[1], $arglist[2], $arglist[3]);
            case 'ATTENDEE':
                return $this->setAttendee($arglist[1], $arglist[2], $arglist[3]);
            case 'CATEGORIES':
                return $this->setCategories($arglist[1], $arglist[2], $arglist[3]);
            case 'CLASS':
                return $this->setClass($arglist[1], $arglist[2]);
            case 'COMMENT':
                return $this->setComment($arglist[1], $arglist[2], $arglist[3]);
            case 'COMPLETED':
                return $this->setCompleted($arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7]);
            case 'CONTACT':
                return $this->setContact($arglist[1], $arglist[2], $arglist[3]);
            case 'CREATED':
                return $this->setCreated($arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7]);
            case 'DESCRIPTION':
                return $this->setDescription($arglist[1], $arglist[2], $arglist[3]);
            case 'DTEND':
                return $this->setDtend($arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7], $arglist[8]);
            case 'DTSTAMP':
                return $this->setDtstamp($arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7]);
            case 'DTSTART':
                return $this->setDtstart($arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7], $arglist[8]);
            case 'DUE':
                return $this->setDue($arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7], $arglist[8]);
            case 'DURATION':
                return $this->setDuration($arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6]);
            case 'EXDATE':
                return $this->setExdate($arglist[1], $arglist[2], $arglist[3]);
            case 'EXRULE':
                return $this->setExrule($arglist[1], $arglist[2], $arglist[3]);
            case 'FREEBUSY':
                return $this->setFreebusy($arglist[1], $arglist[2], $arglist[3], $arglist[4]);
            case 'GEO':
                return $this->setGeo($arglist[1], $arglist[2], $arglist[3]);
            case 'LAST-MODIFIED':
                return $this->setLastModified($arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7]);
            case 'LOCATION':
                return $this->setLocation($arglist[1], $arglist[2]);
            case 'ORGANIZER':
                return $this->setOrganizer($arglist[1], $arglist[2]);
            case 'PERCENT-COMPLETE':
                return $this->setPercentComplete($arglist[1], $arglist[2]);
            case 'PRIORITY':
                return $this->setPriority($arglist[1], $arglist[2]);
            case 'RDATE':
                return $this->setRdate($arglist[1], $arglist[2], $arglist[3]);
            case 'RECURRENCE-ID':
                return $this->setRecurrenceid($arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7], $arglist[8]);
            case 'RELATED-TO':
                return $this->setRelatedTo($arglist[1], $arglist[2], $arglist[3]);
            case 'REPEAT':
                return $this->setRepeat($arglist[1], $arglist[2]);
            case 'REQUEST-STATUS':
                return $this->setRequestStatus($arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5]);
            case 'RESOURCES':
                return $this->setResources($arglist[1], $arglist[2], $arglist[3]);
            case 'RRULE':
                return $this->setRrule($arglist[1], $arglist[2], $arglist[3]);
            case 'SEQUENCE':
                return $this->setSequence($arglist[1], $arglist[2]);
            case 'STATUS':
                return $this->setStatus($arglist[1], $arglist[2]);
            case 'SUMMARY':
                return $this->setSummary($arglist[1], $arglist[2]);
            case 'TRANSP':
                return $this->setTransp($arglist[1], $arglist[2]);
            case 'TRIGGER':
                return $this->setTrigger($arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7], $arglist[8], $arglist[9], $arglist[10], $arglist[11]);
            case 'TZID':
                return $this->setTzid($arglist[1], $arglist[2]);
            case 'TZNAME':
                return $this->setTzname($arglist[1], $arglist[2], $arglist[3]);
            case 'TZOFFSETFROM':
                return $this->setTzoffsetfrom($arglist[1], $arglist[2]);
            case 'TZOFFSETTO':
                return $this->setTzoffsetto($arglist[1], $arglist[2]);
            case 'TZURL':
                return $this->setTzurl($arglist[1], $arglist[2]);
            case 'UID':
                return $this->setUid($arglist[1], $arglist[2]);
            case 'URL':
                return $this->setUrl($arglist[1], $arglist[2]);
            default:
                return $this->setXprop($arglist[0], $arglist[1], $arglist[2]);
        }
        return false;
    }
    /*********************************************************************************/
    /**
     * parse component unparsed data into properties
     *
     * @param mixed $unparsedtext , optional, strict rfc2445 formatted, single property string or array of strings
     * @return bool FALSE if error occurs during parsing
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.17 - 2012-02-03
     */
    function parse($unparsedtext = null)
    {
        if (!empty($unparsedtext)) {
            $nl = $this->getConfig('nl');
            if (is_array($unparsedtext)) {
                $unparsedtext = implode('\n' . $nl, $unparsedtext);
            }
            /* fix line folding */
            $eolchars = ["\r\n", "\n\r", "\n", "\r"]; // check all line endings
            $EOLmark = false;
            foreach ($eolchars as $eolchar) {
                if (!$EOLmark && (false !== strpos($unparsedtext, $eolchar))) {
                    $unparsedtext = str_replace($eolchar . " ", '', $unparsedtext);
                    $unparsedtext = str_replace($eolchar . "\t", '', $unparsedtext);
                    if ($eolchar != $nl) {
                        $unparsedtext = str_replace($eolchar, $nl, $unparsedtext);
                    }
                    $EOLmark = true;
                }
            }
            $tmp = explode($nl, $unparsedtext);
            $unparsedtext = [];
            foreach ($tmp as $tmpr) {
                if (!empty($tmpr)) {
                    $unparsedtext[] = $tmpr;
                }
            }
        } elseif (!isset($this->unparsed)) {
            $unparsedtext = [];
        } else {
            $unparsedtext = $this->unparsed;
        }
        $this->unparsed = [];
        $comp = &$this;
        $config = $this->getConfig();
        foreach ($unparsedtext as $line) {
            if (in_array(strtoupper(substr($line, 0, 6)), ['END:VA', 'END:DA'])) {
                $this->components[] = $comp->copy();
            } elseif ('END:ST' == strtoupper(substr($line, 0, 6))) {
                array_unshift($this->components, $comp->copy());
            } elseif ('END:' == strtoupper(substr($line, 0, 4))) {
                break;
            } elseif ('BEGIN:VALARM' == strtoupper(substr($line, 0, 12))) {
                $comp = new valarm($config);
            } elseif ('BEGIN:STANDARD' == strtoupper(substr($line, 0, 14))) {
                $comp = new vtimezone('standard', $config);
            } elseif ('BEGIN:DAYLIGHT' == strtoupper(substr($line, 0, 14))) {
                $comp = new vtimezone('daylight', $config);
            } elseif ('BEGIN:' == strtoupper(substr($line, 0, 6))) {
                continue;
            } else {
                $comp->unparsed[] = $line;
            }
        }
        unset($config);
        /* concatenate property values spread over several lines */
        $lastix = -1;
        $propnames = [
            'action',
            'attach',
            'attendee',
            'categories',
            'comment',
            'completed'
            ,
            'contact',
            'class',
            'created',
            'description',
            'dtend',
            'dtstart'
            ,
            'dtstamp',
            'due',
            'duration',
            'exdate',
            'exrule',
            'freebusy',
            'geo'
            ,
            'last-modified',
            'location',
            'organizer',
            'percent-complete'
            ,
            'priority',
            'rdate',
            'recurrence-id',
            'related-to',
            'repeat'
            ,
            'request-status',
            'resources',
            'rrule',
            'sequence',
            'status'
            ,
            'summary',
            'transp',
            'trigger',
            'tzid',
            'tzname',
            'tzoffsetfrom'
            ,
            'tzoffsetto',
            'tzurl',
            'uid',
            'url',
            'x-',
        ];
        $proprows = [];
        foreach ($this->unparsed as $line) {
            $newProp = false;
            foreach ($propnames as $propname) {
                if ($propname == strtolower(substr($line, 0, strlen($propname)))) {
                    $newProp = true;
                    break;
                }
            }
            if ($newProp) {
                $newProp = false;
                $lastix++;
                $proprows[$lastix] = $line;
            } else {
                $proprows[$lastix] .= '!"#¤%&/()=?' . $line;
            }
        }
        /* parse each property 'line' */
        $paramMStz = ['utc-', 'utc+', 'gmt-', 'gmt+'];
        $paramProto3 = ['fax:', 'cid:', 'sms:', 'tel:', 'urn:'];
        $paramProto4 = ['crid:', 'news:', 'pres:'];
        foreach ($proprows as $line) {
            $line = str_replace('!"#¤%&/()=? ', '', $line);
            $line = str_replace('!"#¤%&/()=?', '', $line);
            if ('\n' == substr($line, -2)) {
                $line = substr($line, 0, strlen($line) - 2);
            }
            /* get propname, (problem with x-properties, otherwise in previous loop) */
            $cix = $propname = null;
            for ($cix = 0, $clen = strlen($line); $cix < $clen; $cix++) {
                if (in_array($line[$cix], [':', ';'])) {
                    break;
                } else {
                    $propname .= $line[$cix];
                }
            }
            if (('x-' == substr($propname, 0, 2)) || ('X-' == substr($propname, 0, 2))) {
                $propname2 = $propname;
                $propname = 'X-';
            }
            /* rest of the line is opt.params and value */
            $line = substr($line, $cix);
            /* separate attributes from value */
            $attr = [];
            $attrix = -1;
            $clen = strlen($line);
            $WithinQuotes = false;
            for ($cix = 0; $cix < $clen; $cix++) {
                if ((':' == $line[$cix]) &&
                    (substr($line, $cix, 3) != '://') &&
                    (!in_array(strtolower(substr($line, $cix - 6, 4)), $paramMStz)) &&
                    (!in_array(strtolower(substr($line, $cix - 3, 4)), $paramProto3)) &&
                    (!in_array(strtolower(substr($line, $cix - 4, 5)), $paramProto4)) &&
                    (strtolower(substr($line, $cix - 6, 7)) != 'mailto:') &&
                    !$WithinQuotes) {
                    $attrEnd = true;
                    if (($cix < ($clen - 4)) &&
                        ctype_digit(substr($line, $cix + 1, 4))) { // an URI with a (4pos) portnr??
                        for ($c2ix = $cix; 3 < $c2ix; $c2ix--) {
                            if ('://' == substr($line, $c2ix - 2, 3)) {
                                $attrEnd = false;
                                break; // an URI with a portnr!!
                            }
                        }
                    }
                    if ($attrEnd) {
                        $line = substr($line, ($cix + 1));
                        break;
                    }
                }
                if ('"' == $line[$cix]) {
                    $WithinQuotes = (false === $WithinQuotes) ? true : false;
                }
                if (';' == $line[$cix]) {
                    $attr[++$attrix] = null;
                } else {
                    $attr[$attrix] .= $line[$cix];
                }
            }
            /* make attributes in array format */
            $propattr = [];
            foreach ($attr as $attribute) {
                $attrsplit = explode('=', $attribute, 2);
                if (1 < count($attrsplit)) {
                    $propattr[$attrsplit[0]] = $attrsplit[1];
                } else {
                    $propattr[] = $attribute;
                }
            }
            /* call setProperty( $propname.. . */
            switch (strtoupper($propname)) {
                case 'ATTENDEE':
                    foreach ($propattr as $pix => $attr) {
                        if (!in_array(strtoupper($pix), ['MEMBER', 'DELEGATED-TO', 'DELEGATED-FROM'])) {
                            continue;
                        }
                        $attr2 = explode(',', $attr);
                        if (1 < count($attr2)) {
                            $propattr[$pix] = $attr2;
                        }
                    }
                    $this->setProperty($propname, $line, $propattr);
                    break;
                case 'X-':
                    $propname = (isset($propname2)) ? $propname2 : $propname;
                    unset($propname2);
                case 'CATEGORIES':
                case 'RESOURCES':
                    if (false !== strpos($line, ',')) {
                        $llen = strlen($line);
                        $content = [0 => ''];
                        $cix = 0;
                        for ($lix = 0; $lix < $llen; $lix++) {
                            if ((',' == $line[$lix]) && ("\\" != $line[($lix - 1)])) {
                                $cix++;
                                $content[$cix] = '';
                            } else {
                                $content[$cix] .= $line[$lix];
                            }
                        }
                        if (1 < count($content)) {
                            $content = array_values($content);
                            foreach ($content as $cix => $contentPart) {
                                $content[$cix] = calendarComponent::_strunrep($contentPart);
                            }
                            $this->setProperty($propname, $content, $propattr);
                            break;
                        } else {
                            $line = reset($content);
                        }
                    }
                case 'COMMENT':
                case 'CONTACT':
                case 'DESCRIPTION':
                case 'LOCATION':
                case 'SUMMARY':
                    if (empty($line)) {
                        $propattr = null;
                    }
                    $this->setProperty($propname, calendarComponent::_strunrep($line), $propattr);
                    break;
                case 'REQUEST-STATUS':
                    $values = explode(';', $line, 3);
                    $values[1] = (!isset($values[1])) ? null : calendarComponent::_strunrep($values[1]);
                    $values[2] = (!isset($values[2])) ? null : calendarComponent::_strunrep($values[2]);
                    $this->setProperty($propname
                        , $values[0]  // statcode
                        , $values[1]  // statdesc
                        , $values[2]  // extdata
                        , $propattr);
                    break;
                case 'FREEBUSY':
                    $fbtype = (isset($propattr['FBTYPE'])) ? $propattr['FBTYPE'] : ''; // force setting default, if missing
                    unset($propattr['FBTYPE']);
                    $values = explode(',', $line);
                    foreach ($values as $vix => $value) {
                        $value2 = explode('/', $value);
                        if (1 < count($value2)) {
                            $values[$vix] = $value2;
                        }
                    }
                    $this->setProperty($propname, $fbtype, $values, $propattr);
                    break;
                case 'GEO':
                    $value = explode(';', $line, 2);
                    if (2 > count($value)) {
                        $value[1] = null;
                    }
                    $this->setProperty($propname, $value[0], $value[1], $propattr);
                    break;
                case 'EXDATE':
                    $values = (!empty($line)) ? explode(',', $line) : null;
                    $this->setProperty($propname, $values, $propattr);
                    break;
                case 'RDATE':
                    if (empty($line)) {
                        $this->setProperty($propname, $line, $propattr);
                        break;
                    }
                    $values = explode(',', $line);
                    foreach ($values as $vix => $value) {
                        $value2 = explode('/', $value);
                        if (1 < count($value2)) {
                            $values[$vix] = $value2;
                        }
                    }
                    $this->setProperty($propname, $values, $propattr);
                    break;
                case 'EXRULE':
                case 'RRULE':
                    $values = explode(';', $line);
                    $recur = [];
                    foreach ($values as $value2) {
                        if (empty($value2)) {
                            continue;
                        } // ;-char in ending position ???
                        $value3 = explode('=', $value2, 2);
                        $rulelabel = strtoupper($value3[0]);
                        switch ($rulelabel) {
                            case 'BYDAY':
                            {
                                $value4 = explode(',', $value3[1]);
                                if (1 < count($value4)) {
                                    foreach ($value4 as $v5ix => $value5) {
                                        $value6 = [];
                                        $dayno = $dayname = null;
                                        $value5 = trim((string)$value5);
                                        if ((ctype_alpha(substr($value5, -1))) &&
                                            (ctype_alpha(substr($value5, -2, 1)))) {
                                            $dayname = substr($value5, -2, 2);
                                            if (2 < strlen($value5)) {
                                                $dayno = substr($value5, 0, (strlen($value5) - 2));
                                            }
                                        }
                                        if ($dayno) {
                                            $value6[] = $dayno;
                                        }
                                        if ($dayname) {
                                            $value6['DAY'] = $dayname;
                                        }
                                        $value4[$v5ix] = $value6;
                                    }
                                } else {
                                    $value4 = [];
                                    $dayno = $dayname = null;
                                    $value5 = trim((string)$value3[1]);
                                    if ((ctype_alpha(substr($value5, -1))) &&
                                        (ctype_alpha(substr($value5, -2, 1)))) {
                                        $dayname = substr($value5, -2, 2);
                                        if (2 < strlen($value5)) {
                                            $dayno = substr($value5, 0, (strlen($value5) - 2));
                                        }
                                    }
                                    if ($dayno) {
                                        $value4[] = $dayno;
                                    }
                                    if ($dayname) {
                                        $value4['DAY'] = $dayname;
                                    }
                                }
                                $recur[$rulelabel] = $value4;
                                break;
                            }
                            default:
                            {
                                $value4 = explode(',', $value3[1]);
                                if (1 < count($value4)) {
                                    $value3[1] = $value4;
                                }
                                $recur[$rulelabel] = $value3[1];
                                break;
                            }
                        } // end - switch $rulelabel
                    } // end - foreach( $values.. .
                    $this->setProperty($propname, $recur, $propattr);
                    break;
                case 'ACTION':
                case 'CLASSIFICATION':
                case 'STATUS':
                case 'TRANSP':
                case 'UID':
                case 'TZID':
                case 'RELATED-TO':
                case 'TZNAME':
                    $line = calendarComponent::_strunrep($line);
                default:
                    $this->setProperty($propname, $line, $propattr);
                    break;
            } // end  switch( $propname.. .
        } // end - foreach( $proprows.. .
        unset($unparsedtext, $this->unparsed, $proprows);
        if (isset($this->components) && is_array($this->components) && (0 < count($this->components))) {
            $ckeys = array_keys($this->components);
            foreach ($ckeys as $ckey) {
                if (!empty($this->components[$ckey]) && !empty($this->components[$ckey]->unparsed)) {
                    $this->components[$ckey]->parse();
                }
            }
        }
        return true;
    }
    /*********************************************************************************/
    /*********************************************************************************/
    /**
     * return a copy of this component
     *
     * @return object
     * @since 2.8.8 - 2011-03-15
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function copy()
    {
        $serialized_contents = serialize($this);
        $copy = unserialize($serialized_contents);
        return $copy;
    }
    /*********************************************************************************/
    /*********************************************************************************/
    /**
     * delete calendar subcomponent from component container
     *
     * @param mixed $arg1 ordno / component type / component uid
     * @param mixed $arg2 optional, ordno if arg1 = component type
     * @return void
     * @since 2.8.8 - 2011-03-15
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function deleteComponent($arg1, $arg2 = false)
    {
        if (!isset($this->components)) {
            return false;
        }
        $argType = $index = null;
        if (ctype_digit((string)$arg1)) {
            $argType = 'INDEX';
            $index = (int)$arg1 - 1;
        } elseif ((strlen($arg1) <= strlen('vfreebusy')) && (false === strpos($arg1, '@'))) {
            $argType = strtolower($arg1);
            $index = (!empty($arg2) && ctype_digit((string)$arg2)) ? (( int )$arg2 - 1) : 0;
        }
        $cix2dC = 0;
        foreach ($this->components as $cix => $component) {
            if (empty($component)) {
                continue;
            }
            if (('INDEX' == $argType) && ($index == $cix)) {
                unset($this->components[$cix]);
                return true;
            } elseif ($argType == $component->objName) {
                if ($index == $cix2dC) {
                    unset($this->components[$cix]);
                    return true;
                }
                $cix2dC++;
            } elseif (!$argType && ($arg1 == $component->getProperty('uid'))) {
                unset($this->components[$cix]);
                return true;
            }
        }
        return false;
    }

    /**
     * get calendar component subcomponent from component container
     *
     * @param mixed $arg1 optional, ordno/component type/ component uid
     * @param mixed $arg2 optional, ordno if arg1 = component type
     * @return object
     * @since 2.8.8 - 2011-03-15
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function getComponent($arg1 = false, $arg2 = false)
    {
        if (!isset($this->components)) {
            return false;
        }
        $index = $argType = null;
        if (!$arg1) {
            $argType = 'INDEX';
            $index = $this->compix['INDEX'] =
                (isset($this->compix['INDEX'])) ? $this->compix['INDEX'] + 1 : 1;
        } elseif (ctype_digit((string)$arg1)) {
            $argType = 'INDEX';
            $index = (int)$arg1;
            unset($this->compix);
        } elseif ((strlen($arg1) <= strlen('vfreebusy')) && (false === strpos($arg1, '@'))) {
            unset($this->compix['INDEX']);
            $argType = strtolower($arg1);
            if (!$arg2) {
                $index = $this->compix[$argType] = (isset($this->compix[$argType])) ? $this->compix[$argType] + 1 : 1;
            } else {
                $index = (int)$arg2;
            }
        }
        $index -= 1;
        $ckeys = array_keys($this->components);
        if (!empty($index) && ($index > end($ckeys))) {
            return false;
        }
        $cix2gC = 0;
        foreach ($this->components as $cix => $component) {
            if (empty($component)) {
                continue;
            }
            if (('INDEX' == $argType) && ($index == $cix)) {
                return $component->copy();
            } elseif ($argType == $component->objName) {
                if ($index == $cix2gC) {
                    return $component->copy();
                }
                $cix2gC++;
            } elseif (!$argType && ($arg1 == $component->getProperty('uid'))) {
                return $component->copy();
            }
        }
        /* not found.. . */
        unset($this->compix);
        return false;
    }

    /**
     * add calendar component as subcomponent to container for subcomponents
     *
     * @param object $component calendar component
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 1.x.x - 2007-04-24
     */
    function addSubComponent($component)
    {
        $this->setComponent($component);
    }

    /**
     * create new calendar component subcomponent, already included within component
     *
     * @param string $compType subcomponent type
     * @return object (reference)
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.6.33 - 2011-01-03
     */
    function & newComponent($compType)
    {
        $config = $this->getConfig();
        $keys = array_keys($this->components);
        $ix = end($keys) + 1;
        switch (strtoupper($compType)) {
            case 'ALARM':
            case 'VALARM':
                $this->components[$ix] = new valarm($config);
                break;
            case 'STANDARD':
                array_unshift($this->components, new vtimezone('STANDARD', $config));
                $ix = 0;
                break;
            case 'DAYLIGHT':
                $this->components[$ix] = new vtimezone('DAYLIGHT', $config);
                break;
            default:
                return false;
        }
        return $this->components[$ix];
    }

    /**
     * add calendar component as subcomponent to container for subcomponents
     *
     * @param object $component calendar component
     * @param mixed $arg1 optional, ordno/component type/ component uid
     * @param mixed $arg2 optional, ordno if arg1 = component type
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.8.8 - 2011-03-15
     */
    function setComponent($component, $arg1 = false, $arg2 = false)
    {
        if (!isset($this->components)) {
            return false;
        }
        $component->setConfig($this->getConfig(), false, true);
        if (!in_array($component->objName, ['valarm', 'vtimezone', 'standard', 'daylight'])) {
            /* make sure dtstamp and uid is set */
            $dummy = $component->getProperty('dtstamp');
            $dummy = $component->getProperty('uid');
        }
        if (!$arg1) { // plain insert, last in chain
            $this->components[] = $component->copy();
            return true;
        }
        $argType = $index = null;
        if (ctype_digit((string)$arg1)) { // index insert/replace
            $argType = 'INDEX';
            $index = (int)$arg1 - 1;
        } elseif (in_array(strtolower($arg1), ['vevent', 'vtodo', 'vjournal', 'vfreebusy', 'valarm', 'vtimezone'])) {
            $argType = strtolower($arg1);
            $index = (ctype_digit((string)$arg2)) ? ((int)$arg2) - 1 : 0;
        }
        // else if arg1 is set, arg1 must be an UID
        $cix2sC = 0;
        foreach ($this->components as $cix => $component2) {
            if (empty($component2)) {
                continue;
            }
            if (('INDEX' == $argType) && ($index == $cix)) { // index insert/replace
                $this->components[$cix] = $component->copy();
                return true;
            } elseif ($argType == $component2->objName) { // component Type index insert/replace
                if ($index == $cix2sC) {
                    $this->components[$cix] = $component->copy();
                    return true;
                }
                $cix2sC++;
            } elseif (!$argType && ($arg1 == $component2->getProperty('uid'))) { // UID insert/replace
                $this->components[$cix] = $component->copy();
                return true;
            }
        }
        /* arg1=index and not found.. . insert at index .. .*/
        if ('INDEX' == $argType) {
            $this->components[$index] = $component->copy();
            ksort($this->components, SORT_NUMERIC);
        } else    /* not found.. . insert last in chain anyway .. .*/ {
            $this->components[] = $component->copy();
        }
        return true;
    }

    /**
     * creates formatted output for subcomponents
     *
     * @param array $xcaldecl
     * @return string
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.20 - 2012-02-06
     */
    function createSubComponent()
    {
        $output = null;
        if ('vtimezone' == $this->objName) { // sort subComponents, first standard, then daylight, in dtstart order
            $stdarr = $dlarr = [];
            foreach ($this->components as $component) {
                if (empty($component)) {
                    continue;
                }
                $dt = $component->getProperty('dtstart');
                $key = sprintf('%04d%02d%02d%02d%02d%02d000', $dt['year'], $dt['month'], $dt['day'], $dt['hour'], $dt['min'], $dt['sec']);
                if ('standard' == $component->objName) {
                    while (isset($stdarr[$key])) {
                        $key += 1;
                    }
                    $stdarr[$key] = $component->copy();
                } elseif ('daylight' == $component->objName) {
                    while (isset($dlarr[$key])) {
                        $key += 1;
                    }
                    $dlarr[$key] = $component->copy();
                }
            } // end foreach( $this->components as $component )
            $this->components = [];
            ksort($stdarr, SORT_NUMERIC);
            foreach ($stdarr as $std) {
                $this->components[] = $std->copy();
            }
            unset($stdarr);
            ksort($dlarr, SORT_NUMERIC);
            foreach ($dlarr as $dl) {
                $this->components[] = $dl->copy();
            }
            unset($dlarr);
        } // end if( 'vtimezone' == $this->objName )
        foreach ($this->components as $component) {
            $component->setConfig($this->getConfig(), false, true);
            $output .= $component->createComponent($this->xcaldecl);
        }
        return $output;
    }
    /********************************************************************************/
    /**
     * break lines at pos 75
     *
     * Lines of text SHOULD NOT be longer than 75 octets, excluding the line
     * break. Long content lines SHOULD be split into a multiple line
     * representations using a line "folding" technique. That is, a long
     * line can be split between any two characters by inserting a CRLF
     * immediately followed by a single linear white space character (i.e.,
     * SPACE, US-ASCII decimal 32 or HTAB, US-ASCII decimal 9). Any sequence
     * of CRLF followed immediately by a single linear white space character
     * is ignored (i.e., removed) when processing the content type.
     *
     * Edited 2007-08-26 by Anders Litzell, anders@litzell.se to fix bug where
     * the reserved expression "\n" in the arg $string could be broken up by the
     * folding of lines, causing ambiguity in the return string.
     * Fix uses var $breakAtChar=75 and breaks the line at $breakAtChar-1 if need be.
     *
     * @param string $value
     * @return string
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.13 - 2012-02-14
     */
    function _size75($string)
    {
        $tmp = $string;
        $string = '';
        $eolcharlen = strlen('\n');
        /* if PHP is config with  mb_string and conf overload.. . */
        if (defined('MB_OVERLOAD_STRING') && (1 < ini_get('mbstring.func_overload'))) {
            $strlen = mb_strlen($tmp);
            while ($strlen > 75) {
                if ('\n' == mb_substr($tmp, 75, $eolcharlen)) {
                    $breakAtChar = 74;
                } else {
                    $breakAtChar = 75;
                }
                $string .= mb_substr($tmp, 0, $breakAtChar);
                if ($this->nl != mb_substr($string, (0 - mb_strlen($this->nl)))) {
                    $string .= $this->nl;
                }
                $tmp = mb_substr($tmp, $breakAtChar);
                if (!empty($tmp)) {
                    $tmp = ' ' . $tmp;
                }
                $strlen = mb_strlen($tmp);
            } // end while
            if (0 < $strlen) {
                $string .= $tmp; // the rest
                if ($this->nl != mb_substr($string, (0 - mb_strlen($this->nl)))) {
                    $string .= $this->nl;
                }
            }
            return $string;
        }
        /* if PHP is not config with  mb_string.. . */
        while (true) {
            $bytecnt = strlen($tmp);
            $charCnt = $ix = 0;
            for ($ix = 0; $ix < $bytecnt; $ix++) {
                if ((73 < $charCnt) && ('\n' == substr($tmp, $ix, $eolcharlen))) {
                    break;
                }                                    // break before '\n'
                elseif (74 < $charCnt) {
                    if ('\n' == substr($tmp, $ix, $eolcharlen)) {
                        $ix -= 1;
                    }                               // don't break inside '\n'
                    break;                                    // always break while-loop here
                } else {
                    $byte = ord($tmp[$ix]);
                    if ($byte <= 127) {                       // add a one byte character
                        $string .= substr($tmp, $ix, 1);
                        $charCnt += 1;
                    } else {
                        if ($byte >= 194 && $byte <= 223) {  // start byte in two byte character
                            $string .= substr($tmp, $ix, 2);      // add a two bytes character
                            $charCnt += 1;
                        } else {
                            if ($byte >= 224 && $byte <= 239) {  // start byte in three bytes character
                                $string .= substr($tmp, $ix, 3);      // add a three bytes character
                                $charCnt += 1;
                            } else {
                                if ($byte >= 240 && $byte <= 244) {  // start byte in four bytes character
                                    $string .= substr($tmp, $ix, 4);      // add a four bytes character
                                    $charCnt += 1;
                                }
                            }
                        }
                    }
                }
            } // end for
            if ($this->nl != substr($string, (0 - strlen($this->nl)))) {
                $string .= $this->nl;
            }
            if (false === ($tmp = substr($tmp, $ix))) {
                break;
            } // while-loop breakes here
            else {
                $tmp = ' ' . $tmp;
            }
        } // end while
        if ('\n' . $this->nl == substr($string, (0 - strlen('\n' . $this->nl)))) {
            $string = substr($string, 0, (strlen($string) - strlen('\n' . $this->nl))) . $this->nl;
        }
        return $string;
    }

    /**
     * special characters management output
     *
     * @param string $string
     * @return string
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.6.15 - 2010-09-24
     */
    function _strrep($string)
    {
        switch ($this->format) {
            case 'xcal':
                $string = str_replace('\n', $this->nl, $string);
                $string = htmlspecialchars(strip_tags(stripslashes(urldecode($string))));
                break;
            default:
                $pos = 0;
                $specChars = ['n', 'N', 'r', ',', ';'];
                while ($pos <= strlen($string)) {
                    $pos = strpos($string, "\\", $pos);
                    if (false === $pos) {
                        break;
                    }
                    if (!in_array(substr($string, $pos, 1), $specChars)) {
                        $string = substr($string, 0, $pos) . "\\" . substr($string, ($pos + 1));
                        $pos += 1;
                    }
                    $pos += 1;
                }
                if (false !== strpos($string, '"')) {
                    $string = str_replace('"', "'", $string);
                }
                if (false !== strpos($string, ',')) {
                    $string = str_replace(',', '\,', $string);
                }
                if (false !== strpos($string, ';')) {
                    $string = str_replace(';', '\;', $string);
                }

                if (false !== strpos($string, "\r\n")) {
                    $string = str_replace("\r\n", '\n', $string);
                } elseif (false !== strpos($string, "\r")) {
                    $string = str_replace("\r", '\n', $string);
                } elseif (false !== strpos($string, "\n")) {
                    $string = str_replace("\n", '\n', $string);
                }

                if (false !== strpos($string, '\N')) {
                    $string = str_replace('\N', '\n', $string);
                }
                //        if( FALSE !== strpos( $string, $this->nl ))
                $string = str_replace($this->nl, '\n', $string);
                break;
        }
        return $string;
    }

    /**
     * special characters management input (from iCal file)
     *
     * @param string $string
     * @return string
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.6.22 - 2010-10-17
     */
    static function _strunrep($string)
    {
        $string = str_replace('\\\\', '\\', $string);
        $string = str_replace('\,', ',', $string);
        $string = str_replace('\;', ';', $string);
        //    $string = str_replace( '\n',  $this->nl, $string); // ??
        return $string;
    }
}

/*********************************************************************************/
/*********************************************************************************/

/**
 * class for calendar component VEVENT
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.5.1 - 2008-10-12
 */
class vevent extends calendarComponent
{
    var $attach;
    var $attendee;
    var $categories;
    var $comment;
    var $contact;
    var $class;
    var $created;
    var $description;
    var $dtend;
    var $dtstart;
    var $duration;
    var $exdate;
    var $exrule;
    var $geo;
    var $lastmodified;
    var $location;
    var $organizer;
    var $priority;
    var $rdate;
    var $recurrenceid;
    var $relatedto;
    var $requeststatus;
    var $resources;
    var $rrule;
    var $sequence;
    var $status;
    var $summary;
    var $transp;
    var $url;
    var $xprop;
    //  component subcomponents container
    var $components;

    /**
     * constructor for calendar component VEVENT object
     *
     * @param array $config
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.8.2 - 2011-05-01
     */
    function vevent($config = [])
    {
        $this->calendarComponent();

        $this->attach = '';
        $this->attendee = '';
        $this->categories = '';
        $this->class = '';
        $this->comment = '';
        $this->contact = '';
        $this->created = '';
        $this->description = '';
        $this->dtstart = '';
        $this->dtend = '';
        $this->duration = '';
        $this->exdate = '';
        $this->exrule = '';
        $this->geo = '';
        $this->lastmodified = '';
        $this->location = '';
        $this->organizer = '';
        $this->priority = '';
        $this->rdate = '';
        $this->recurrenceid = '';
        $this->relatedto = '';
        $this->requeststatus = '';
        $this->resources = '';
        $this->rrule = '';
        $this->sequence = '';
        $this->status = '';
        $this->summary = '';
        $this->transp = '';
        $this->url = '';
        $this->xprop = '';

        $this->components = [];

        if (defined('ICAL_LANG') && !isset($config['language'])) {
            $config['language'] = ICAL_LANG;
        }
        if (!isset($config['allowEmpty'])) {
            $config['allowEmpty'] = true;
        }
        if (!isset($config['nl'])) {
            $config['nl'] = "\r\n";
        }
        if (!isset($config['format'])) {
            $config['format'] = 'iCal';
        }
        if (!isset($config['delimiter'])) {
            $config['delimiter'] = DIRECTORY_SEPARATOR;
        }
        $this->setConfig($config);

    }

    /**
     * create formatted output for calendar component VEVENT object instance
     *
     * @param array $xcaldecl
     * @return string
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.10.16 - 2011-10-28
     */
    function createComponent(&$xcaldecl)
    {
        $objectname = $this->_createFormat();
        $component = $this->componentStart1 . $objectname . $this->componentStart2 . $this->nl;
        $component .= $this->createUid();
        $component .= $this->createDtstamp();
        $component .= $this->createAttach();
        $component .= $this->createAttendee();
        $component .= $this->createCategories();
        $component .= $this->createComment();
        $component .= $this->createContact();
        $component .= $this->createClass();
        $component .= $this->createCreated();
        $component .= $this->createDescription();
        $component .= $this->createDtstart();
        $component .= $this->createDtend();
        $component .= $this->createDuration();
        $component .= $this->createExdate();
        $component .= $this->createExrule();
        $component .= $this->createGeo();
        $component .= $this->createLastModified();
        $component .= $this->createLocation();
        $component .= $this->createOrganizer();
        $component .= $this->createPriority();
        $component .= $this->createRdate();
        $component .= $this->createRrule();
        $component .= $this->createRelatedTo();
        $component .= $this->createRequestStatus();
        $component .= $this->createRecurrenceid();
        $component .= $this->createResources();
        $component .= $this->createSequence();
        $component .= $this->createStatus();
        $component .= $this->createSummary();
        $component .= $this->createTransp();
        $component .= $this->createUrl();
        $component .= $this->createXprop();
        $component .= $this->createSubComponent();
        $component .= $this->componentEnd1 . $objectname . $this->componentEnd2;
        if (is_array($this->xcaldecl) && (0 < count($this->xcaldecl))) {
            foreach ($this->xcaldecl as $localxcaldecl) {
                $xcaldecl[] = $localxcaldecl;
            }
        }
        return $component;
    }
}

/*********************************************************************************/
/*********************************************************************************/

/**
 * class for calendar component VTODO
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.5.1 - 2008-10-12
 */
class vtodo extends calendarComponent
{
    var $attach;
    var $attendee;
    var $categories;
    var $comment;
    var $completed;
    var $contact;
    var $class;
    var $created;
    var $description;
    var $dtstart;
    var $due;
    var $duration;
    var $exdate;
    var $exrule;
    var $geo;
    var $lastmodified;
    var $location;
    var $organizer;
    var $percentcomplete;
    var $priority;
    var $rdate;
    var $recurrenceid;
    var $relatedto;
    var $requeststatus;
    var $resources;
    var $rrule;
    var $sequence;
    var $status;
    var $summary;
    var $url;
    var $xprop;
    //  component subcomponents container
    var $components;

    /**
     * constructor for calendar component VTODO object
     *
     * @param array $config
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.8.2 - 2011-05-01
     */
    function vtodo($config = [])
    {
        $this->calendarComponent();

        $this->attach = '';
        $this->attendee = '';
        $this->categories = '';
        $this->class = '';
        $this->comment = '';
        $this->completed = '';
        $this->contact = '';
        $this->created = '';
        $this->description = '';
        $this->dtstart = '';
        $this->due = '';
        $this->duration = '';
        $this->exdate = '';
        $this->exrule = '';
        $this->geo = '';
        $this->lastmodified = '';
        $this->location = '';
        $this->organizer = '';
        $this->percentcomplete = '';
        $this->priority = '';
        $this->rdate = '';
        $this->recurrenceid = '';
        $this->relatedto = '';
        $this->requeststatus = '';
        $this->resources = '';
        $this->rrule = '';
        $this->sequence = '';
        $this->status = '';
        $this->summary = '';
        $this->url = '';
        $this->xprop = '';

        $this->components = [];

        if (defined('ICAL_LANG') && !isset($config['language'])) {
            $config['language'] = ICAL_LANG;
        }
        if (!isset($config['allowEmpty'])) {
            $config['allowEmpty'] = true;
        }
        if (!isset($config['nl'])) {
            $config['nl'] = "\r\n";
        }
        if (!isset($config['format'])) {
            $config['format'] = 'iCal';
        }
        if (!isset($config['delimiter'])) {
            $config['delimiter'] = DIRECTORY_SEPARATOR;
        }
        $this->setConfig($config);

    }

    /**
     * create formatted output for calendar component VTODO object instance
     *
     * @param array $xcaldecl
     * @return string
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.5.1 - 2008-11-07
     */
    function createComponent(&$xcaldecl)
    {
        $objectname = $this->_createFormat();
        $component = $this->componentStart1 . $objectname . $this->componentStart2 . $this->nl;
        $component .= $this->createUid();
        $component .= $this->createDtstamp();
        $component .= $this->createAttach();
        $component .= $this->createAttendee();
        $component .= $this->createCategories();
        $component .= $this->createClass();
        $component .= $this->createComment();
        $component .= $this->createCompleted();
        $component .= $this->createContact();
        $component .= $this->createCreated();
        $component .= $this->createDescription();
        $component .= $this->createDtstart();
        $component .= $this->createDue();
        $component .= $this->createDuration();
        $component .= $this->createExdate();
        $component .= $this->createExrule();
        $component .= $this->createGeo();
        $component .= $this->createLastModified();
        $component .= $this->createLocation();
        $component .= $this->createOrganizer();
        $component .= $this->createPercentComplete();
        $component .= $this->createPriority();
        $component .= $this->createRdate();
        $component .= $this->createRelatedTo();
        $component .= $this->createRequestStatus();
        $component .= $this->createRecurrenceid();
        $component .= $this->createResources();
        $component .= $this->createRrule();
        $component .= $this->createSequence();
        $component .= $this->createStatus();
        $component .= $this->createSummary();
        $component .= $this->createUrl();
        $component .= $this->createXprop();
        $component .= $this->createSubComponent();
        $component .= $this->componentEnd1 . $objectname . $this->componentEnd2;
        if (is_array($this->xcaldecl) && (0 < count($this->xcaldecl))) {
            foreach ($this->xcaldecl as $localxcaldecl) {
                $xcaldecl[] = $localxcaldecl;
            }
        }
        return $component;
    }
}

/*********************************************************************************/
/*********************************************************************************/

/**
 * class for calendar component VJOURNAL
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.5.1 - 2008-10-12
 */
class vjournal extends calendarComponent
{
    var $attach;
    var $attendee;
    var $categories;
    var $comment;
    var $contact;
    var $class;
    var $created;
    var $description;
    var $dtstart;
    var $exdate;
    var $exrule;
    var $lastmodified;
    var $organizer;
    var $rdate;
    var $recurrenceid;
    var $relatedto;
    var $requeststatus;
    var $rrule;
    var $sequence;
    var $status;
    var $summary;
    var $url;
    var $xprop;

    /**
     * constructor for calendar component VJOURNAL object
     *
     * @param array $config
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.8.2 - 2011-05-01
     */
    function vjournal($config = [])
    {
        $this->calendarComponent();

        $this->attach = '';
        $this->attendee = '';
        $this->categories = '';
        $this->class = '';
        $this->comment = '';
        $this->contact = '';
        $this->created = '';
        $this->description = '';
        $this->dtstart = '';
        $this->exdate = '';
        $this->exrule = '';
        $this->lastmodified = '';
        $this->organizer = '';
        $this->rdate = '';
        $this->recurrenceid = '';
        $this->relatedto = '';
        $this->requeststatus = '';
        $this->rrule = '';
        $this->sequence = '';
        $this->status = '';
        $this->summary = '';
        $this->url = '';
        $this->xprop = '';

        if (defined('ICAL_LANG') && !isset($config['language'])) {
            $config['language'] = ICAL_LANG;
        }
        if (!isset($config['allowEmpty'])) {
            $config['allowEmpty'] = true;
        }
        if (!isset($config['nl'])) {
            $config['nl'] = "\r\n";
        }
        if (!isset($config['format'])) {
            $config['format'] = 'iCal';
        }
        if (!isset($config['delimiter'])) {
            $config['delimiter'] = DIRECTORY_SEPARATOR;
        }
        $this->setConfig($config);

    }

    /**
     * create formatted output for calendar component VJOURNAL object instance
     *
     * @param array $xcaldecl
     * @return string
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.5.1 - 2008-10-12
     */
    function createComponent(&$xcaldecl)
    {
        $objectname = $this->_createFormat();
        $component = $this->componentStart1 . $objectname . $this->componentStart2 . $this->nl;
        $component .= $this->createUid();
        $component .= $this->createDtstamp();
        $component .= $this->createAttach();
        $component .= $this->createAttendee();
        $component .= $this->createCategories();
        $component .= $this->createClass();
        $component .= $this->createComment();
        $component .= $this->createContact();
        $component .= $this->createCreated();
        $component .= $this->createDescription();
        $component .= $this->createDtstart();
        $component .= $this->createExdate();
        $component .= $this->createExrule();
        $component .= $this->createLastModified();
        $component .= $this->createOrganizer();
        $component .= $this->createRdate();
        $component .= $this->createRequestStatus();
        $component .= $this->createRecurrenceid();
        $component .= $this->createRelatedTo();
        $component .= $this->createRrule();
        $component .= $this->createSequence();
        $component .= $this->createStatus();
        $component .= $this->createSummary();
        $component .= $this->createUrl();
        $component .= $this->createXprop();
        $component .= $this->componentEnd1 . $objectname . $this->componentEnd2;
        if (is_array($this->xcaldecl) && (0 < count($this->xcaldecl))) {
            foreach ($this->xcaldecl as $localxcaldecl) {
                $xcaldecl[] = $localxcaldecl;
            }
        }
        return $component;
    }
}

/*********************************************************************************/
/*********************************************************************************/

/**
 * class for calendar component VFREEBUSY
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.5.1 - 2008-10-12
 */
class vfreebusy extends calendarComponent
{
    var $attendee;
    var $comment;
    var $contact;
    var $dtend;
    var $dtstart;
    var $duration;
    var $freebusy;
    var $organizer;
    var $requeststatus;
    var $url;
    var $xprop;
    //  component subcomponents container
    var $components;

    /**
     * constructor for calendar component VFREEBUSY object
     *
     * @param array $config
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.8.2 - 2011-05-01
     */
    function vfreebusy($config = [])
    {
        $this->calendarComponent();

        $this->attendee = '';
        $this->comment = '';
        $this->contact = '';
        $this->dtend = '';
        $this->dtstart = '';
        $this->duration = '';
        $this->freebusy = '';
        $this->organizer = '';
        $this->requeststatus = '';
        $this->url = '';
        $this->xprop = '';

        if (defined('ICAL_LANG') && !isset($config['language'])) {
            $config['language'] = ICAL_LANG;
        }
        if (!isset($config['allowEmpty'])) {
            $config['allowEmpty'] = true;
        }
        if (!isset($config['nl'])) {
            $config['nl'] = "\r\n";
        }
        if (!isset($config['format'])) {
            $config['format'] = 'iCal';
        }
        if (!isset($config['delimiter'])) {
            $config['delimiter'] = DIRECTORY_SEPARATOR;
        }
        $this->setConfig($config);

    }

    /**
     * create formatted output for calendar component VFREEBUSY object instance
     *
     * @param array $xcaldecl
     * @return string
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.3.1 - 2007-11-19
     */
    function createComponent(&$xcaldecl)
    {
        $objectname = $this->_createFormat();
        $component = $this->componentStart1 . $objectname . $this->componentStart2 . $this->nl;
        $component .= $this->createUid();
        $component .= $this->createDtstamp();
        $component .= $this->createAttendee();
        $component .= $this->createComment();
        $component .= $this->createContact();
        $component .= $this->createDtstart();
        $component .= $this->createDtend();
        $component .= $this->createDuration();
        $component .= $this->createFreebusy();
        $component .= $this->createOrganizer();
        $component .= $this->createRequestStatus();
        $component .= $this->createUrl();
        $component .= $this->createXprop();
        $component .= $this->componentEnd1 . $objectname . $this->componentEnd2;
        if (is_array($this->xcaldecl) && (0 < count($this->xcaldecl))) {
            foreach ($this->xcaldecl as $localxcaldecl) {
                $xcaldecl[] = $localxcaldecl;
            }
        }
        return $component;
    }
}

/*********************************************************************************/
/*********************************************************************************/

/**
 * class for calendar component VALARM
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.5.1 - 2008-10-12
 */
class valarm extends calendarComponent
{
    var $action;
    var $attach;
    var $attendee;
    var $description;
    var $duration;
    var $repeat;
    var $summary;
    var $trigger;
    var $xprop;

    /**
     * constructor for calendar component VALARM object
     *
     * @param array $config
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.8.2 - 2011-05-01
     */
    function valarm($config = [])
    {
        $this->calendarComponent();

        $this->action = '';
        $this->attach = '';
        $this->attendee = '';
        $this->description = '';
        $this->duration = '';
        $this->repeat = '';
        $this->summary = '';
        $this->trigger = '';
        $this->xprop = '';

        if (defined('ICAL_LANG') && !isset($config['language'])) {
            $config['language'] = ICAL_LANG;
        }
        if (!isset($config['allowEmpty'])) {
            $config['allowEmpty'] = true;
        }
        if (!isset($config['nl'])) {
            $config['nl'] = "\r\n";
        }
        if (!isset($config['format'])) {
            $config['format'] = 'iCal';
        }
        if (!isset($config['delimiter'])) {
            $config['delimiter'] = DIRECTORY_SEPARATOR;
        }
        $this->setConfig($config);

    }

    /**
     * create formatted output for calendar component VALARM object instance
     *
     * @param array $xcaldecl
     * @return string
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.5.1 - 2008-10-22
     */
    function createComponent(&$xcaldecl)
    {
        $objectname = $this->_createFormat();
        $component = $this->componentStart1 . $objectname . $this->componentStart2 . $this->nl;
        $component .= $this->createAction();
        $component .= $this->createAttach();
        $component .= $this->createAttendee();
        $component .= $this->createDescription();
        $component .= $this->createDuration();
        $component .= $this->createRepeat();
        $component .= $this->createSummary();
        $component .= $this->createTrigger();
        $component .= $this->createXprop();
        $component .= $this->componentEnd1 . $objectname . $this->componentEnd2;
        if (is_array($this->xcaldecl) && (0 < count($this->xcaldecl))) {
            foreach ($this->xcaldecl as $localxcaldecl) {
                $xcaldecl[] = $localxcaldecl;
            }
        }
        return $component;
    }
}

/**********************************************************************************
 * /*********************************************************************************/

/**
 * class for calendar component VTIMEZONE
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.5.1 - 2008-10-12
 */
class vtimezone extends calendarComponent
{
    var $timezonetype;

    var $comment;
    var $dtstart;
    var $lastmodified;
    var $rdate;
    var $rrule;
    var $tzid;
    var $tzname;
    var $tzoffsetfrom;
    var $tzoffsetto;
    var $tzurl;
    var $xprop;
    //  component subcomponents container
    var $components;

    /**
     * constructor for calendar component VTIMEZONE object
     *
     * @param mixed $timezonetype optional, default FALSE ( STANDARD / DAYLIGHT )
     * @param array $config
     * @return void
     * @since 2.8.2 - 2011-05-01
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    function vtimezone($timezonetype = false, $config = [])
    {
        if (is_array($timezonetype)) {
            $config = $timezonetype;
            $timezonetype = false;
        }
        if (!$timezonetype) {
            $this->timezonetype = 'VTIMEZONE';
        } else {
            $this->timezonetype = strtoupper($timezonetype);
        }
        $this->calendarComponent();

        $this->comment = '';
        $this->dtstart = '';
        $this->lastmodified = '';
        $this->rdate = '';
        $this->rrule = '';
        $this->tzid = '';
        $this->tzname = '';
        $this->tzoffsetfrom = '';
        $this->tzoffsetto = '';
        $this->tzurl = '';
        $this->xprop = '';

        $this->components = [];

        if (defined('ICAL_LANG') && !isset($config['language'])) {
            $config['language'] = ICAL_LANG;
        }
        if (!isset($config['allowEmpty'])) {
            $config['allowEmpty'] = true;
        }
        if (!isset($config['nl'])) {
            $config['nl'] = "\r\n";
        }
        if (!isset($config['format'])) {
            $config['format'] = 'iCal';
        }
        if (!isset($config['delimiter'])) {
            $config['delimiter'] = DIRECTORY_SEPARATOR;
        }
        $this->setConfig($config);

    }

    /**
     * create formatted output for calendar component VTIMEZONE object instance
     *
     * @param array $xcaldecl
     * @return string
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.5.1 - 2008-10-25
     */
    function createComponent(&$xcaldecl)
    {
        $objectname = $this->_createFormat();
        $component = $this->componentStart1 . $objectname . $this->componentStart2 . $this->nl;
        $component .= $this->createTzid();
        $component .= $this->createLastModified();
        $component .= $this->createTzurl();
        $component .= $this->createDtstart();
        $component .= $this->createTzoffsetfrom();
        $component .= $this->createTzoffsetto();
        $component .= $this->createComment();
        $component .= $this->createRdate();
        $component .= $this->createRrule();
        $component .= $this->createTzname();
        $component .= $this->createXprop();
        $component .= $this->createSubComponent();
        $component .= $this->componentEnd1 . $objectname . $this->componentEnd2;
        if (is_array($this->xcaldecl) && (0 < count($this->xcaldecl))) {
            foreach ($this->xcaldecl as $localxcaldecl) {
                $xcaldecl[] = $localxcaldecl;
            }
        }
        return $component;
    }
}

/*********************************************************************************/
/*********************************************************************************/

/**
 * moving all utility (static) functions to a utility class
 * 20111223 - move iCalUtilityFunctions class to the end of the iCalcreator class file
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.10.1 - 2011-07-16
 *
 */
class iCalUtilityFunctions
{
    // Store the single instance of iCalUtilityFunctions
    private static $m_pInstance;

    // Private constructor to limit object instantiation to within the class
    private function __construct()
    {
        $m_pInstance = false;
    }

    // Getter method for creating/returning the single instance of this class
    public static function getInstance()
    {
        if (!self::$m_pInstance) {
            self::$m_pInstance = new iCalUtilityFunctions();
        }

        return self::$m_pInstance;
    }

    /**
     * check a date(-time) for an opt. timezone and if it is a DATE-TIME or DATE
     *
     * @param array $date , date to check
     * @param int $parno , no of date parts (i.e. year, month.. .)
     * @return array $params, property parameters
     * @since 2.10.30 - 2012-01-16
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    public static function _chkdatecfg($theDate, &$parno, &$params)
    {
        if (isset($params['TZID'])) {
            $parno = 6;
        } elseif (isset($params['VALUE']) && ('DATE' == $params['VALUE'])) {
            $parno = 3;
        } else {
            if (isset($params['VALUE']) && ('PERIOD' == $params['VALUE'])) {
                $parno = 7;
            }
            if (is_array($theDate)) {
                if (isset($theDate['timestamp'])) {
                    $tzid = (isset($theDate['tz'])) ? $theDate['tz'] : null;
                } else {
                    $tzid = (isset($theDate['tz'])) ? $theDate['tz'] : (7 == count($theDate)) ? end($theDate) : null;
                }
                if (!empty($tzid)) {
                    $parno = 7;
                    if (!iCalUtilityFunctions::_isOffset($tzid)) {
                        $params['TZID'] = $tzid;
                    } // save only timezone
                } elseif (!$parno && (3 == count($theDate)) &&
                    (isset($params['VALUE']) && ('DATE' == $params['VALUE']))) {
                    $parno = 3;
                } else {
                    $parno = 6;
                }
            } else { // string
                $date = trim($theDate);
                if ('Z' == substr($date, -1)) {
                    $parno = 7;
                } // UTC DATE-TIME
                elseif (((8 == strlen($date) && ctype_digit($date)) || (11 >= strlen($date))) &&
                    (!isset($params['VALUE']) || !in_array($params['VALUE'], ['DATE-TIME', 'PERIOD']))) {
                    $parno = 3;
                } // DATE
                $date = iCalUtilityFunctions::_date_time_string($date, $parno);
                unset($date['unparsedtext']);
                if (!empty($date['tz'])) {
                    $parno = 7;
                    if (!iCalUtilityFunctions::_isOffset($date['tz'])) {
                        $params['TZID'] = $date['tz'];
                    } // save only timezone
                } elseif (empty($parno)) {
                    $parno = 6;
                }
            }
            if (isset($params['TZID'])) {
                $parno = 6;
            }
        }
    }

    /**
     * create timezone and standard/daylight components
     *
     * Result when 'Europe/Stockholm' and no from/to arguments is used as timezone:
     *
     * BEGIN:VTIMEZONE
     * TZID:Europe/Stockholm
     * BEGIN:STANDARD
     * DTSTART:20101031T020000
     * TZOFFSETFROM:+0200
     * TZOFFSETTO:+0100
     * TZNAME:CET
     * END:STANDARD
     * BEGIN:DAYLIGHT
     * DTSTART:20100328T030000
     * TZOFFSETFROM:+0100
     * TZOFFSETTO:+0200
     * TZNAME:CEST
     * END:DAYLIGHT
     * END:VTIMEZONE
     *
     * @param object $calendar , reference to an iCalcreator calendar instance
     * @param string $timezone , a PHP5 (DateTimeZone) valid timezone
     * @param array $xProp ,    *[x-propName => x-propValue], optional
     * @param int $from an unix timestamp
     * @param int $to an unix timestamp
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.8 - 2012-02-06
     * Generates components for all transitions in a date range, based on contribution by Yitzchok Lavi <icalcreator@onebigsystem.com>
     */
    public static function createTimezone(&$calendar, $timezone, $xProp = [], $from = null, $to = null)
    {
        if (!class_exists('DateTimeZone')) {
            return false;
        }
        if (empty($timezone)) {
            return false;
        }
        try {
            $dtz = new DateTimeZone($timezone);
            $transitions = $dtz->getTransitions();
            unset($dtz);
            $utcTz = new DateTimeZone('UTC');
        } catch (Exception $e) {
            return false;
        }
        if (empty($to)) {
            $dates = array_keys($calendar->getProperty('dtstart'));
        }
        $transCnt = 2; // number of transitions in output if empty input $from/$to and an empty dates-array
        $dateFrom = new DateTime('now');
        $dateTo = new DateTime('now');
        if (!empty($from)) {
            $dateFrom->setTimestamp($from);
        } else {
            if (!empty($dates)) {
                $dateFrom = new DateTime(reset($dates));
            }              // set lowest date to the lowest dtstart date
            $dateFrom->modify('-1 month');                           // set $dateFrom to one month before the lowest date
        }
        $dateFrom->setTimezone($utcTz);                            // convert local date to UTC
        if (!empty($to)) {
            $dateTo->setTimestamp($to);
        } else {
            if (!empty($dates)) {
                $dateTo = new DateTime(end($dates));         // set highest date to the highest dtstart date
                $to = $dateTo->getTimestamp();              // set mark that a highest date is found
            }
            $dateTo->modify('+1 year');                              // set $dateTo to one year after the highest date
        }
        $dateTo->setTimezone($utcTz);                              // convert local date to UTC
        $transTemp = [];
        $prevOffsetfrom = $stdCnt = $dlghtCnt = 0;
        $stdIx = $dlghtIx = null;
        $date = new DateTime('now', $utcTz);
        foreach ($transitions as $tix => $trans) {                  // all transitions in date-time order!!
            $date->setTimestamp($trans['ts']);                       // set transition date (UTC)
            if ($date < $dateFrom) {
                $prevOffsetfrom = $trans['offset'];                     // previous trans offset will be 'next' trans offsetFrom
                continue;
            }
            if ($date > $dateTo) {
                break;
            }                                                   // loop always (?) breaks here
            if (!empty($prevOffsetfrom) || (0 == $prevOffsetfrom)) {
                $trans['offsetfrom'] = $prevOffsetfrom;                  // i.e. set previous offsetto as offsetFrom
                $date->modify($trans['offsetfrom'] . 'seconds');         // convert utc date to local date
                $trans['time'] = [
                    'year'  => $date->format('Y')  // set dtstart to array to ease up dtstart and (opt) rdate setting
                    ,
                    'month' => $date->format('n')
                    ,
                    'day'   => $date->format('j')
                    ,
                    'hour'  => $date->format('G')
                    ,
                    'min'   => $date->format('i')
                    ,
                    'sec'   => $date->format('s'),
                ];
            }
            $prevOffsetfrom = $trans['offset'];
            $trans['prevYear'] = $trans['time']['year'];
            if (true !== $trans['isdst']) {                           // standard timezone
                if (!empty($stdIx) && isset($transTemp[$stdIx]['offsetfrom']) && // check for any rdate's (in strict year order)
                    ($transTemp[$stdIx]['abbr'] == $trans['abbr']) &&
                    ($transTemp[$stdIx]['offsetfrom'] == $trans['offsetfrom']) &&
                    ($transTemp[$stdIx]['offset'] == $trans['offset']) &&
                    (($transTemp[$stdIx]['prevYear'] + 1) == $trans['time']['year'])) {
                    $transTemp[$stdIx]['prevYear'] = $trans['time']['year'];
                    $transTemp[$stdIx]['rdate'][] = $trans['time'];
                    continue;
                }
                $stdIx = $tix;
                $stdCnt += 1;
            } // end standard timezone
            else {                                                     // daylight timezone
                if (!empty($dlghtIx) && isset($transTemp[$dlghtIx]['offsetfrom']) && // check for any rdate's (in strict year order)
                    ($transTemp[$dlghtIx]['abbr'] == $trans['abbr']) &&
                    ($transTemp[$dlghtIx]['offsetfrom'] == $trans['offsetfrom']) &&
                    ($transTemp[$dlghtIx]['offset'] == $trans['offset']) &&
                    (($transTemp[$dlghtIx]['prevYear'] + 1) == $trans['time']['year'])) {
                    $transTemp[$dlghtIx]['prevYear'] = $trans['time']['year'];
                    $transTemp[$dlghtIx]['rdate'][] = $trans['time'];
                    continue;
                }
                $dlghtIx = $tix;
                $dlghtCnt += 1;
            } // end daylight timezone
            if (empty($to) && ($transCnt == count($transTemp))) { // store only $transCnt transitions
                if (true !== $transTemp[0]['isdst']) {
                    $stdCnt -= 1;
                } else {
                    $dlghtCnt -= 1;
                }
                array_shift($transTemp);
            } // end if( empty( $to ) && ( $transCnt == count( $transTemp )))
            $transTemp[$tix] = $trans;
        } // end foreach( $transitions as $tix => $trans )
        unset($transitions);
        if (empty($transTemp)) {
            return false;
        }
        $tz = &$calendar->newComponent('vtimezone');
        $tz->setproperty('tzid', $timezone);
        if (!empty($xProp)) {
            foreach ($xProp as $xPropName => $xPropValue) {
                if ('x-' == strtolower(substr($xPropName, 0, 2))) {
                    $tz->setproperty($xPropName, $xPropValue);
                }
            }
        }
        foreach ($transTemp as $trans) {
            $type = (true !== $trans['isdst']) ? 'standard' : 'daylight';
            $scomp = &$tz->newComponent($type);
            $scomp->setProperty('dtstart', $trans['time']);
            //      $scomp->setProperty( 'x-utc-timestamp', $trans['ts'] );   // test ###
            if (!empty($trans['abbr'])) {
                $scomp->setProperty('tzname', $trans['abbr']);
            }
            $scomp->setProperty('tzoffsetfrom', iCalUtilityFunctions::offsetSec2His($trans['offsetfrom']));
            $scomp->setProperty('tzoffsetto', iCalUtilityFunctions::offsetSec2His($trans['offset']));
            if (isset($trans['rdate'])) {
                $scomp->setProperty('RDATE', $trans['rdate']);
            }
        }
        return true;
    }

    /**
     * convert a date/datetime (array) to timestamp
     *
     * @param array $datetime datetime/(date)
     * @param string $tz timezone
     * @return timestamp
     * @since 2.4.8 - 2008-10-30
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    public static function _date2timestamp($datetime, $tz = null)
    {
        $output = null;
        if (!isset($datetime['hour'])) {
            $datetime['hour'] = '0';
        }
        if (!isset($datetime['min'])) {
            $datetime['min'] = '0';
        }
        if (!isset($datetime['sec'])) {
            $datetime['sec'] = '0';
        }
        foreach ($datetime as $dkey => $dvalue) {
            if ('tz' != $dkey) {
                $datetime[$dkey] = (integer)$dvalue;
            }
        }
        if ($tz) {
            $datetime['tz'] = $tz;
        }
        $offset = (isset($datetime['tz']) && ('' < trim($datetime['tz']))) ? iCalUtilityFunctions::_tz2offset($datetime['tz']) : 0;
        $output = mktime($datetime['hour'], $datetime['min'], ($datetime['sec'] + $offset), $datetime['month'], $datetime['day'], $datetime['year']);
        return $output;
    }

    /**
     * ensures internal date-time/date format for input date-time/date in array format
     *
     * @param array $datetime
     * @param int $parno optional, default FALSE
     * @return array
     * @since 2.11.4 - 2012-03-18
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    public static function _date_time_array($datetime, $parno = false)
    {
        $output = [];
        foreach ($datetime as $dateKey => $datePart) {
            switch ($dateKey) {
                case '0':
                case 'year':
                    $output['year'] = $datePart;
                    break;
                case '1':
                case 'month':
                    $output['month'] = $datePart;
                    break;
                case '2':
                case 'day':
                    $output['day'] = $datePart;
                    break;
            }
            if (3 != $parno) {
                switch ($dateKey) {
                    case '0':
                    case '1':
                    case '2':
                        break;
                    case '3':
                    case 'hour':
                        $output['hour'] = $datePart;
                        break;
                    case '4':
                    case 'min' :
                        $output['min'] = $datePart;
                        break;
                    case '5':
                    case 'sec' :
                        $output['sec'] = $datePart;
                        break;
                    case '6':
                    case 'tz'  :
                        $output['tz'] = $datePart;
                        break;
                }
            }
        }
        if (3 != $parno) {
            if (!isset($output['hour'])) {
                $output['hour'] = 0;
            }
            if (!isset($output['min'])) {
                $output['min'] = 0;
            }
            if (!isset($output['sec'])) {
                $output['sec'] = 0;
            }
            if (isset($output['tz']) && ('Z' != $output['tz']) &&
                (('+0000' == $output['tz']) || ('-0000' == $output['tz']) || ('+000000' == $output['tz']) || ('-000000' == $output['tz']))) {
                $output['tz'] = 'Z';
            }
        }
        return $output;
    }

    /**
     * ensures internal date-time/date format for input date-time/date in string fromat
     *
     * @param array $datetime
     * @param int $parno optional, default FALSE
     * @return array
     * @since 2.10.30 - 2012-01-06
     * Modified to also return original string value by Yitzchok Lavi <icalcreator@onebigsystem.com>
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    public static function _date_time_string($datetime, $parno = false)
    {
        // save original input string to return it later
        $unparseddatetime = $datetime;
        $datetime = (string)trim($datetime);
        $tz = null;
        $len = strlen($datetime) - 1;
        if ('Z' == substr($datetime, -1)) {
            $tz = 'Z';
            $datetime = trim(substr($datetime, 0, $len));
        } elseif ((ctype_digit(substr($datetime, -2, 2))) && // time or date
            ('-' == substr($datetime, -3, 1)) ||
            (':' == substr($datetime, -3, 1)) ||
            ('.' == substr($datetime, -3, 1))) {
            $continue = true;
        } elseif ((ctype_digit(substr($datetime, -4, 4))) && // 4 pos offset
            (' +' == substr($datetime, -6, 2)) ||
            (' -' == substr($datetime, -6, 2))) {
            $tz = substr($datetime, -5, 5);
            $datetime = substr($datetime, 0, ($len - 5));
        } elseif ((ctype_digit(substr($datetime, -6, 6))) && // 6 pos offset
            (' +' == substr($datetime, -8, 2)) ||
            (' -' == substr($datetime, -8, 2))) {
            $tz = substr($datetime, -7, 7);
            $datetime = substr($datetime, 0, ($len - 7));
        } elseif ((6 < $len) && (ctype_digit(substr($datetime, -6, 6)))) {
            $continue = true;
        } elseif ('T' == substr($datetime, -7, 1)) {
            $continue = true;
        } else {
            $cx = $tx = 0;    //  19970415T133000 US-Eastern
            for ($cx = -1; $cx > (9 - $len); $cx--) {
                $char = substr($datetime, $cx, 1);
                if ((' ' == $char) || ctype_digit($char)) {
                    break;
                } // if exists, tz ends here.. . ?
                else {
                    $tx--;
                } // tz length counter
            }
            if (0 > $tx) {
                $tz = substr($datetime, $tx);
                $datetime = trim(substr($datetime, 0, $len + $tx + 1));
            }
        }
        if (0 < substr_count($datetime, '-')) {
            $datetime = str_replace('-', '/', $datetime);
        } elseif (ctype_digit(substr($datetime, 0, 8)) &&
            ('T' == substr($datetime, 8, 1)) &&
            ctype_digit(substr($datetime, 9, 6))) {
        }
        $datestring = date('Y-m-d H:i:s', strtotime($datetime));
        $tz = trim($tz);
        $output = [];
        $output['year'] = substr($datestring, 0, 4);
        $output['month'] = substr($datestring, 5, 2);
        $output['day'] = substr($datestring, 8, 2);
        if ((6 == $parno) || (7 == $parno) || (!$parno && ('Z' == $tz))) {
            $output['hour'] = substr($datestring, 11, 2);
            $output['min'] = substr($datestring, 14, 2);
            $output['sec'] = substr($datestring, 17, 2);
            if (!empty($tz)) {
                $output['tz'] = $tz;
            }
        } elseif (3 != $parno) {
            if (('00' < substr($datestring, 11, 2)) ||
                ('00' < substr($datestring, 14, 2)) ||
                ('00' < substr($datestring, 17, 2))) {
                $output['hour'] = substr($datestring, 11, 2);
                $output['min'] = substr($datestring, 14, 2);
                $output['sec'] = substr($datestring, 17, 2);
            }
            if (!empty($tz)) {
                $output['tz'] = $tz;
            }
        }
        // return original string in the array in case strtotime failed to make sense of it
        $output['unparsedtext'] = $unparseddatetime;
        return $output;
    }

    /**
     * convert local startdate/enddate (Ymd[His]) to duration array
     *
     * uses this component dates if missing input dates
     *
     * @param array $startdate
     * @param array $duration
     * @return array duration
     * @since 2.6.11 - 2010-10-21
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    public static function _date2duration($startdate, $enddate)
    {
        $startWdate = mktime(0, 0, 0, $startdate['month'], $startdate['day'], $startdate['year']);
        $endWdate = mktime(0, 0, 0, $enddate['month'], $enddate['day'], $enddate['year']);
        $wduration = $endWdate - $startWdate;
        $dur = [];
        $dur['week'] = (int)floor($wduration / (7 * 24 * 60 * 60));
        $wduration = $wduration % (7 * 24 * 60 * 60);
        $dur['day'] = (int)floor($wduration / (24 * 60 * 60));
        $wduration = $wduration % (24 * 60 * 60);
        $dur['hour'] = (int)floor($wduration / (60 * 60));
        $wduration = $wduration % (60 * 60);
        $dur['min'] = (int)floor($wduration / (60));
        $dur['sec'] = (int)$wduration % (60);
        return $dur;
    }

    /**
     * ensures internal duration format for input in array format
     *
     * @param array $duration
     * @return array
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.1.1 - 2007-06-24
     */
    public static function _duration_array($duration)
    {
        $output = [];
        if (is_array($duration) &&
            (1 == count($duration)) &&
            isset($duration['sec']) &&
            (60 < $duration['sec'])) {
            $durseconds = $duration['sec'];
            $output['week'] = floor($durseconds / (60 * 60 * 24 * 7));
            $durseconds = $durseconds % (60 * 60 * 24 * 7);
            $output['day'] = floor($durseconds / (60 * 60 * 24));
            $durseconds = $durseconds % (60 * 60 * 24);
            $output['hour'] = floor($durseconds / (60 * 60));
            $durseconds = $durseconds % (60 * 60);
            $output['min'] = floor($durseconds / (60));
            $output['sec'] = ($durseconds % (60));
        } else {
            foreach ($duration as $durKey => $durValue) {
                if (empty($durValue)) {
                    continue;
                }
                switch ($durKey) {
                    case '0':
                    case 'week':
                        $output['week'] = $durValue;
                        break;
                    case '1':
                    case 'day':
                        $output['day'] = $durValue;
                        break;
                    case '2':
                    case 'hour':
                        $output['hour'] = $durValue;
                        break;
                    case '3':
                    case 'min':
                        $output['min'] = $durValue;
                        break;
                    case '4':
                    case 'sec':
                        $output['sec'] = $durValue;
                        break;
                }
            }
        }
        if (isset($output['week']) && (0 < $output['week'])) {
            unset($output['day'], $output['hour'], $output['min'], $output['sec']);
            return $output;
        }
        unset($output['week']);
        if (empty($output['day'])) {
            unset($output['day']);
        }
        if (isset($output['hour']) || isset($output['min']) || isset($output['sec'])) {
            if (!isset($output['hour'])) {
                $output['hour'] = 0;
            }
            if (!isset($output['min'])) {
                $output['min'] = 0;
            }
            if (!isset($output['sec'])) {
                $output['sec'] = 0;
            }
            if ((0 == $output['hour']) && (0 == $output['min']) && (0 == $output['sec'])) {
                unset($output['hour'], $output['min'], $output['sec']);
            }
        }
        return $output;
    }

    /**
     * ensures internal duration format for input in string format
     *
     * @param string $duration
     * @return array
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.0.5 - 2007-03-14
     */
    public static function _duration_string($duration)
    {
        $duration = (string)trim($duration);
        while ('P' != strtoupper(substr($duration, 0, 1))) {
            if (0 < strlen($duration)) {
                $duration = substr($duration, 1);
            } else {
                return false;
            } // no leading P !?!?
        }
        $duration = substr($duration, 1); // skip P
        $duration = str_replace('t', 'T', $duration);
        $duration = str_replace('T', '', $duration);
        $output = [];
        $val = null;
        for ($ix = 0; $ix < strlen($duration); $ix++) {
            switch (strtoupper(substr($duration, $ix, 1))) {
                case 'W':
                    $output['week'] = $val;
                    $val = null;
                    break;
                case 'D':
                    $output['day'] = $val;
                    $val = null;
                    break;
                case 'H':
                    $output['hour'] = $val;
                    $val = null;
                    break;
                case 'M':
                    $output['min'] = $val;
                    $val = null;
                    break;
                case 'S':
                    $output['sec'] = $val;
                    $val = null;
                    break;
                default:
                    if (!ctype_digit(substr($duration, $ix, 1))) {
                        return false;
                    } // unknown duration control character  !?!?
                    else {
                        $val .= substr($duration, $ix, 1);
                    }
            }
        }
        return iCalUtilityFunctions::_duration_array($output);
    }

    /**
     * convert duration to date in array format
     *
     * @param array $startdate
     * @param array $duration
     * @return array, date format
     * @since 2.8.7 - 2011-03-03
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    public static function _duration2date($startdate = null, $duration = null)
    {
        if (empty($startdate)) {
            return false;
        }
        if (empty($duration)) {
            return false;
        }
        $dateOnly = (isset($startdate['hour']) || isset($startdate['min']) || isset($startdate['sec'])) ? false : true;
        $startdate['hour'] = (isset($startdate['hour'])) ? $startdate['hour'] : 0;
        $startdate['min'] = (isset($startdate['min'])) ? $startdate['min'] : 0;
        $startdate['sec'] = (isset($startdate['sec'])) ? $startdate['sec'] : 0;
        $dtend = 0;
        if (isset($duration['week'])) {
            $dtend += ($duration['week'] * 7 * 24 * 60 * 60);
        }
        if (isset($duration['day'])) {
            $dtend += ($duration['day'] * 24 * 60 * 60);
        }
        if (isset($duration['hour'])) {
            $dtend += ($duration['hour'] * 60 * 60);
        }
        if (isset($duration['min'])) {
            $dtend += ($duration['min'] * 60);
        }
        if (isset($duration['sec'])) {
            $dtend += $duration['sec'];
        }
        $dtend = mktime($startdate['hour'], $startdate['min'], ($startdate['sec'] + $dtend), $startdate['month'], $startdate['day'], $startdate['year']);
        $dtend2 = [];
        $dtend2['year'] = date('Y', $dtend);
        $dtend2['month'] = date('m', $dtend);
        $dtend2['day'] = date('d', $dtend);
        $dtend2['hour'] = date('H', $dtend);
        $dtend2['min'] = date('i', $dtend);
        $dtend2['sec'] = date('s', $dtend);
        if (isset($startdate['tz'])) {
            $dtend2['tz'] = $startdate['tz'];
        }
        if ($dateOnly && ((0 == $dtend2['hour']) && (0 == $dtend2['min']) && (0 == $dtend2['sec']))) {
            unset($dtend2['hour'], $dtend2['min'], $dtend2['sec']);
        }
        return $dtend2;
    }

    /**
     * if not preSet, if exist, remove key with expected value from array and return hit value else return elseValue
     *
     * @param array $array
     * @param string $expkey , expected key
     * @param string $expval , expected value
     * @param int $hitVal optional, return value if found
     * @param int $elseVal optional, return value if not found
     * @param int $preSet optional, return value if already preset
     * @return int
     * @since 2.4.16 - 2008-11-08
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    public static function _existRem(&$array, $expkey, $expval = false, $hitVal = null, $elseVal = null, $preSet = null)
    {
        if ($preSet) {
            return $preSet;
        }
        if (!is_array($array) || (0 == count($array))) {
            return $elseVal;
        }
        foreach ($array as $key => $value) {
            if (strtoupper($expkey) == strtoupper($key)) {
                if (!$expval || (strtoupper($expval) == strtoupper($array[$key]))) {
                    unset($array[$key]);
                    return $hitVal;
                }
            }
        }
        return $elseVal;
    }

    /**
     * creates formatted output for calendar component property data value type date/date-time
     *
     * @param array $datetime
     * @param int $parno , optional, default 6
     * @return string
     * @since 2.11.8 - 2012-03-17
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    public static function _format_date_time($datetime, $parno = 6)
    {
        if (!isset($datetime['year']) &&
            !isset($datetime['month']) &&
            !isset($datetime['day']) &&
            !isset($datetime['hour']) &&
            !isset($datetime['min']) &&
            !isset($datetime['sec'])) {
            return;
        }
        $output = null;
        foreach ($datetime as $dkey => & $dvalue) {
            if ('tz' != $dkey) {
                $dvalue = (integer)$dvalue;
            }
        }
        $output = sprintf('%04d%02d%02d', $datetime['year'], $datetime['month'], $datetime['day']);
        if (isset($datetime['hour']) ||
            isset($datetime['min']) ||
            isset($datetime['sec']) ||
            isset($datetime['tz'])) {
            if (isset($datetime['tz']) &&
                !isset($datetime['hour'])) {
                $datetime['hour'] = 0;
            }
            if (isset($datetime['hour']) &&
                !isset($datetime['min'])) {
                $datetime['min'] = 0;
            }
            if (isset($datetime['hour']) &&
                isset($datetime['min']) &&
                !isset($datetime['sec'])) {
                $datetime['sec'] = 0;
            }
            $output .= sprintf('T%02d%02d%02d', $datetime['hour'], $datetime['min'], $datetime['sec']);
            if (isset($datetime['tz']) && ('' < trim($datetime['tz']))) {
                $datetime['tz'] = trim($datetime['tz']);
                if ('Z' == $datetime['tz']) {
                    $output .= 'Z';
                }
                $offset = iCalUtilityFunctions::_tz2offset($datetime['tz']);
                if (0 != $offset) {
                    $date = mktime($datetime['hour'], $datetime['min'], ($datetime['sec'] - $offset), $datetime['month'], $datetime['day'], $datetime['year']);
                    $output = date('Ymd\THis\Z', $date);
                }
            } elseif (7 == $parno) {
                $output .= 'Z';
            }
        }
        return $output;
    }

    /**
     * creates formatted output for calendar component property data value type duration
     *
     * @param array $duration ( week, day, hour, min, sec )
     * @return string
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.9.9 - 2011-06-17
     */
    public static function _format_duration($duration)
    {
        if (isset($duration['week']) ||
            isset($duration['day']) ||
            isset($duration['hour']) ||
            isset($duration['min']) ||
            isset($duration['sec'])) {
            $ok = true;
        } else {
            return;
        }
        if (isset($duration['week']) && (0 < $duration['week'])) {
            return 'P' . $duration['week'] . 'W';
        }
        $output = 'P';
        if (isset($duration['day']) && (0 < $duration['day'])) {
            $output .= $duration['day'] . 'D';
        }
        if ((isset($duration['hour']) && (0 < $duration['hour'])) ||
            (isset($duration['min']) && (0 < $duration['min'])) ||
            (isset($duration['sec']) && (0 < $duration['sec']))) {
            $output .= 'T';
        }
        $output .= (isset($duration['hour']) && (0 < $duration['hour'])) ? $duration['hour'] . 'H' : '';
        $output .= (isset($duration['min']) && (0 < $duration['min'])) ? $duration['min'] . 'M' : '';
        $output .= (isset($duration['sec']) && (0 < $duration['sec'])) ? $duration['sec'] . 'S' : '';
        if ('P' == $output) {
            $output = 'PT0S';
        }
        return $output;
    }

    /**
     * checks if input array contains a date
     *
     * @param array $input
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.8 - 2012-01-20
     */
    public static function _isArrayDate($input)
    {
        if (!is_array($input)) {
            return false;
        }
        if (isset($input['week']) || (!in_array(count($input), [3, 6, 7]))) {
            return false;
        }
        if (7 == count($input)) {
            return true;
        }
        if (isset($input['year']) && isset($input['month']) && isset($input['day'])) {
            return checkdate((int)$input['month'], (int)$input['day'], (int)$input['year']);
        }
        if (isset($input['day']) || isset($input['hour']) || isset($input['min']) || isset($input['sec'])) {
            return false;
        }
        if (in_array(0, $input)) {
            return false;
        }
        if ((1970 > $input[0]) || (12 < $input[1]) || (31 < $input[2])) {
            return false;
        }
        if ((isset($input[0]) && isset($input[1]) && isset($input[2])) &&
            checkdate((int)$input[1], (int)$input[2], (int)$input[0])) {
            return true;
        }
        $input = iCalUtilityFunctions::_date_time_string($input[1] . '/' . $input[2] . '/' . $input[0], 3); //  m - d - Y
        if (isset($input['year']) && isset($input['month']) && isset($input['day'])) {
            return checkdate((int)$input['month'], (int)$input['day'], (int)$input['year']);
        }
        return false;
    }

    /**
     * checks if input array contains a timestamp date
     *
     * @param array $input
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.4.16 - 2008-10-18
     */
    public static function _isArrayTimestampDate($input)
    {
        return (is_array($input) && isset($input['timestamp'])) ? true : false;
    }

    /**
     * controll if input string contains trailing UTC offset
     *
     * @param string $input
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.4.16 - 2008-10-19
     */
    public static function _isOffset($input)
    {
        $input = trim((string)$input);
        if ('Z' == substr($input, -1)) {
            return true;
        } elseif ((5 <= strlen($input)) &&
            (in_array(substr($input, -5, 1), ['+', '-'])) &&
            ('0000' < substr($input, -4)) && ('9999' >= substr($input, -4))) {
            return true;
        } elseif ((7 <= strlen($input)) &&
            (in_array(substr($input, -7, 1), ['+', '-'])) &&
            ('000000' < substr($input, -6)) && ('999999' >= substr($input, -6))) {
            return true;
        }
        return false;
    }

    /**
     * (very simple) conversion of a MS timezone to a PHP5 valid (Date-)timezone
     * matching (MS) UCT offset and time zone descriptors
     *
     * @param string $timezone , input/output variable reference
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.10.29 - 2012-01-11
     */
    public static function ms2phpTZ(&$timezone)
    {
        if (!class_exists('DateTimeZone')) {
            return false;
        }
        if (empty($timezone)) {
            return false;
        }
        $search = str_replace('"', '', $timezone);
        $search = str_replace(['GMT', 'gmt', 'utc'], 'UTC', $search);
        if ('(UTC' != substr($search, 0, 4)) {
            return false;
        }
        if (false === ($pos = strpos($search, ')'))) {
            return false;
        }
        $pos = strpos($search, ')');
        $searchOffset = substr($search, 4, ($pos - 4));
        $searchOffset = iCalUtilityFunctions::_tz2offset(str_replace(':', '', $searchOffset));
        while (' ' == substr($search, ($pos + 1))) {
            $pos += 1;
        }
        $searchText = trim(str_replace(['(', ')', '&', ',', '  '], ' ', substr($search, ($pos + 1))));
        $searchWords = explode(' ', $searchText);
        $timezone_abbreviations = DateTimeZone::listAbbreviations();
        $hits = [];
        foreach ($timezone_abbreviations as $name => $transitions) {
            foreach ($transitions as $cnt => $transition) {
                if (empty($transition['offset']) ||
                    empty($transition['timezone_id']) ||
                    ($transition['offset'] != $searchOffset)) {
                    continue;
                }
                $cWords = explode('/', $transition['timezone_id']);
                $cPrio = $hitCnt = $rank = 0;
                foreach ($cWords as $cWord) {
                    if (empty($cWord)) {
                        continue;
                    }
                    $cPrio += 1;
                    $sPrio = 0;
                    foreach ($searchWords as $sWord) {
                        if (empty($sWord) || ('time' == strtolower($sWord))) {
                            continue;
                        }
                        $sPrio += 1;
                        if (strtolower($cWord) == strtolower($sWord)) {
                            $hitCnt += 1;
                            $rank += ($cPrio + $sPrio);
                        } else {
                            $rank += 10;
                        }
                    }
                }
                if (0 < $hitCnt) {
                    $hits[$rank][] = $transition['timezone_id'];
                }
            }
        }
        unset($timezone_abbreviations);
        if (empty($hits)) {
            return false;
        }
        ksort($hits);
        foreach ($hits as $rank => $tzs) {
            if (!empty($tzs)) {
                $timezone = reset($tzs);
                return true;
            }
        }
        return false;
    }

    /**
     * transform offset in seconds to [-/+]hhmm[ss]
     *
     * @param string $seconds
     * @return string
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2011-05-02
     */
    public static function offsetSec2His($seconds)
    {
        if ('-' == substr($seconds, 0, 1)) {
            $prefix = '-';
            $seconds = substr($seconds, 1);
        } elseif ('+' == substr($seconds, 0, 1)) {
            $prefix = '+';
            $seconds = substr($seconds, 1);
        } else {
            $prefix = '+';
        }
        $output = '';
        $hour = (int)floor($seconds / 3600);
        if (10 > $hour) {
            $hour = '0' . $hour;
        }
        $seconds = $seconds % 3600;
        $min = (int)floor($seconds / 60);
        if (10 > $min) {
            $min = '0' . $min;
        }
        $output = $hour . $min;
        $seconds = $seconds % 60;
        if (0 < $seconds) {
            if (9 < $seconds) {
                $output .= $seconds;
            } else {
                $output .= '0' . $seconds;
            }
        }
        return $prefix . $output;
    }

    /**
     * remakes a recur pattern to an array of dates
     *
     * if missing, UNTIL is set 1 year from startdate (emergency break)
     *
     * @param array $result , array to update, array([timestamp] => timestamp)
     * @param array $recur , pattern for recurrency (only value part, params ignored)
     * @param array $wdate , component start date
     * @param array $startdate , start date
     * @param array $enddate , optional
     * @return array of recurrence (start-)dates as index
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.10.19 - 2011-10-31
     * @todo BYHOUR, BYMINUTE, BYSECOND, WEEKLY at year end/start
     */
    public static function _recur2date(&$result, $recur, $wdate, $startdate, $enddate = false)
    {
        foreach ($wdate as $k => $v) {
            if (ctype_digit($v)) {
                $wdate[$k] = (int)$v;
            }
        }
        $wdateStart = $wdate;
        $wdatets = iCalUtilityFunctions::_date2timestamp($wdate);
        $startdatets = iCalUtilityFunctions::_date2timestamp($startdate);
        if (!$enddate) {
            $enddate = $startdate;
            $enddate['year'] += 1;
        }
        // echo "recur __in_ comp start ".implode('-',$wdate)." period start ".implode('-',$startdate)." period end ".implode('-',$enddate)."<br />\n";print_r($recur);echo "<br />\n";//test###
        $endDatets = iCalUtilityFunctions::_date2timestamp($enddate); // fix break
        if (!isset($recur['COUNT']) && !isset($recur['UNTIL'])) {
            $recur['UNTIL'] = $enddate;
        } // create break
        if (isset($recur['UNTIL'])) {
            $tdatets = iCalUtilityFunctions::_date2timestamp($recur['UNTIL']);
            if ($endDatets > $tdatets) {
                $endDatets = $tdatets; // emergency break
                $enddate = iCalUtilityFunctions::_timestamp2date($endDatets, 6);
            } else {
                $recur['UNTIL'] = iCalUtilityFunctions::_timestamp2date($endDatets, 6);
            }
        }
        if ($wdatets > $endDatets) {
            // echo "recur out of date ".date('Y-m-d H:i:s',$wdatets)."<br />\n";//test
            return []; // nothing to do.. .
        }
        if (!isset($recur['FREQ'])) // "MUST be specified.. ."
        {
            $recur['FREQ'] = 'DAILY';
        } // ??
        $wkst = (isset($recur['WKST']) && ('SU' == $recur['WKST'])) ? 24 * 60 * 60 : 0; // ??
        $weekStart = (int)date('W', ($wdatets + $wkst));
        if (!isset($recur['INTERVAL'])) {
            $recur['INTERVAL'] = 1;
        }
        $countcnt = (!isset($recur['BYSETPOS'])) ? 1 : 0; // DTSTART counts as the first occurrence
        /* find out how to step up dates and set index for interval count */
        $step = [];
        if ('YEARLY' == $recur['FREQ']) {
            $step['year'] = 1;
        } elseif ('MONTHLY' == $recur['FREQ']) {
            $step['month'] = 1;
        } elseif ('WEEKLY' == $recur['FREQ']) {
            $step['day'] = 7;
        } else {
            $step['day'] = 1;
        }
        if (isset($step['year']) && isset($recur['BYMONTH'])) {
            $step = ['month' => 1];
        }
        if (empty($step) && isset($recur['BYWEEKNO'])) // ??
        {
            $step = ['day' => 7];
        }
        if (isset($recur['BYYEARDAY']) || isset($recur['BYMONTHDAY']) || isset($recur['BYDAY'])) {
            $step = ['day' => 1];
        }
        $intervalarr = [];
        if (1 < $recur['INTERVAL']) {
            $intervalix = iCalUtilityFunctions::_recurIntervalIx($recur['FREQ'], $wdate, $wkst);
            $intervalarr = [$intervalix => 0];
        }
        if (isset($recur['BYSETPOS'])) { // save start date + weekno
            $bysetposymd1 = $bysetposymd2 = $bysetposw1 = $bysetposw2 = [];
            // echo "bysetposXold_start=$bysetposYold $bysetposMold $bysetposDold<br />\n"; // test ###
            if (is_array($recur['BYSETPOS'])) {
                foreach ($recur['BYSETPOS'] as $bix => $bval) {
                    $recur['BYSETPOS'][$bix] = (int)$bval;
                }
            } else {
                $recur['BYSETPOS'] = [(int)$recur['BYSETPOS']];
            }
            if ('YEARLY' == $recur['FREQ']) {
                $wdate['month'] = $wdate['day'] = 1; // start from beginning of year
                $wdatets = iCalUtilityFunctions::_date2timestamp($wdate);
                iCalUtilityFunctions::_stepdate($enddate, $endDatets, ['year' => 1]); // make sure to count whole last year
            } elseif ('MONTHLY' == $recur['FREQ']) {
                $wdate['day'] = 1; // start from beginning of month
                $wdatets = iCalUtilityFunctions::_date2timestamp($wdate);
                iCalUtilityFunctions::_stepdate($enddate, $endDatets, ['month' => 1]); // make sure to count whole last month
            } else {
                iCalUtilityFunctions::_stepdate($enddate, $endDatets, $step);
            } // make sure to count whole last period
            // echo "BYSETPOS endDat++ =".implode('-',$enddate).' step='.var_export($step,TRUE)."<br />\n";//test###
            $bysetposWold = (int)date('W', ($wdatets + $wkst));
            $bysetposYold = $wdate['year'];
            $bysetposMold = $wdate['month'];
            $bysetposDold = $wdate['day'];
        } else {
            iCalUtilityFunctions::_stepdate($wdate, $wdatets, $step);
        }
        $year_old = null;
        $daynames = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];
        /* MAIN LOOP */
        // echo "recur start ".implode('-',$wdate)." end ".implode('-',$enddate)."<br />\n";//test
        while (true) {
            if (isset($endDatets) && ($wdatets > $endDatets)) {
                break;
            }
            if (isset($recur['COUNT']) && ($countcnt >= $recur['COUNT'])) {
                break;
            }
            if ($year_old != $wdate['year']) {
                $year_old = $wdate['year'];
                $daycnts = [];
                $yeardays = $weekno = 0;
                $yeardaycnt = [];
                foreach ($daynames as $dn) {
                    $yeardaycnt[$dn] = 0;
                }
                for ($m = 1; $m <= 12; $m++) { // count up and update up-counters
                    $daycnts[$m] = [];
                    $weekdaycnt = [];
                    foreach ($daynames as $dn) {
                        $weekdaycnt[$dn] = 0;
                    }
                    $mcnt = date('t', mktime(0, 0, 0, $m, 1, $wdate['year']));
                    for ($d = 1; $d <= $mcnt; $d++) {
                        $daycnts[$m][$d] = [];
                        if (isset($recur['BYYEARDAY'])) {
                            $yeardays++;
                            $daycnts[$m][$d]['yearcnt_up'] = $yeardays;
                        }
                        if (isset($recur['BYDAY'])) {
                            $day = date('w', mktime(0, 0, 0, $m, $d, $wdate['year']));
                            $day = $daynames[$day];
                            $daycnts[$m][$d]['DAY'] = $day;
                            $weekdaycnt[$day]++;
                            $daycnts[$m][$d]['monthdayno_up'] = $weekdaycnt[$day];
                            $yeardaycnt[$day]++;
                            $daycnts[$m][$d]['yeardayno_up'] = $yeardaycnt[$day];
                        }
                        if (isset($recur['BYWEEKNO']) || ($recur['FREQ'] == 'WEEKLY')) {
                            $daycnts[$m][$d]['weekno_up'] = (int)date('W', mktime(0, 0, $wkst, $m, $d, $wdate['year']));
                        }
                    }
                }
                $daycnt = 0;
                $yeardaycnt = [];
                if (isset($recur['BYWEEKNO']) || ($recur['FREQ'] == 'WEEKLY')) {
                    $weekno = null;
                    for ($d = 31; $d > 25; $d--) { // get last weekno for year
                        if (!$weekno) {
                            $weekno = $daycnts[12][$d]['weekno_up'];
                        } elseif ($weekno < $daycnts[12][$d]['weekno_up']) {
                            $weekno = $daycnts[12][$d]['weekno_up'];
                            break;
                        }
                    }
                }
                for ($m = 12; $m > 0; $m--) { // count down and update down-counters
                    $weekdaycnt = [];
                    foreach ($daynames as $dn) {
                        $yeardaycnt[$dn] = $weekdaycnt[$dn] = 0;
                    }
                    $monthcnt = 0;
                    $mcnt = date('t', mktime(0, 0, 0, $m, 1, $wdate['year']));
                    for ($d = $mcnt; $d > 0; $d--) {
                        if (isset($recur['BYYEARDAY'])) {
                            $daycnt -= 1;
                            $daycnts[$m][$d]['yearcnt_down'] = $daycnt;
                        }
                        if (isset($recur['BYMONTHDAY'])) {
                            $monthcnt -= 1;
                            $daycnts[$m][$d]['monthcnt_down'] = $monthcnt;
                        }
                        if (isset($recur['BYDAY'])) {
                            $day = $daycnts[$m][$d]['DAY'];
                            $weekdaycnt[$day] -= 1;
                            $daycnts[$m][$d]['monthdayno_down'] = $weekdaycnt[$day];
                            $yeardaycnt[$day] -= 1;
                            $daycnts[$m][$d]['yeardayno_down'] = $yeardaycnt[$day];
                        }
                        if (isset($recur['BYWEEKNO']) || ($recur['FREQ'] == 'WEEKLY')) {
                            $daycnts[$m][$d]['weekno_down'] = ($daycnts[$m][$d]['weekno_up'] - $weekno - 1);
                        }
                    }
                }
            }
            /* check interval */
            if (1 < $recur['INTERVAL']) {
                /* create interval index */
                $intervalix = iCalUtilityFunctions::_recurIntervalIx($recur['FREQ'], $wdate, $wkst);
                /* check interval */
                $currentKey = array_keys($intervalarr);
                $currentKey = end($currentKey); // get last index
                if ($currentKey != $intervalix) {
                    $intervalarr = [$intervalix => ($intervalarr[$currentKey] + 1)];
                }
                if (($recur['INTERVAL'] != $intervalarr[$intervalix]) &&
                    (0 != $intervalarr[$intervalix])) {
                    /* step up date */
                    // echo "skip: ".implode('-',$wdate)." ix=$intervalix old=$currentKey interval=".$intervalarr[$intervalix]."<br />\n";//test
                    iCalUtilityFunctions::_stepdate($wdate, $wdatets, $step);
                    continue;
                } else // continue within the selected interval
                {
                    $intervalarr[$intervalix] = 0;
                }
                // echo "cont: ".implode('-',$wdate)." ix=$intervalix old=$currentKey interval=".$intervalarr[$intervalix]."<br />\n";//test
            }
            $updateOK = true;
            if ($updateOK && isset($recur['BYMONTH'])) {
                $updateOK = iCalUtilityFunctions::_recurBYcntcheck($recur['BYMONTH']
                    , $wdate['month']
                    , ($wdate['month'] - 13));
            }
            if ($updateOK && isset($recur['BYWEEKNO'])) {
                $updateOK = iCalUtilityFunctions::_recurBYcntcheck($recur['BYWEEKNO']
                    , $daycnts[$wdate['month']][$wdate['day']]['weekno_up']
                    , $daycnts[$wdate['month']][$wdate['day']]['weekno_down']);
            }
            if ($updateOK && isset($recur['BYYEARDAY'])) {
                $updateOK = iCalUtilityFunctions::_recurBYcntcheck($recur['BYYEARDAY']
                    , $daycnts[$wdate['month']][$wdate['day']]['yearcnt_up']
                    , $daycnts[$wdate['month']][$wdate['day']]['yearcnt_down']);
            }
            if ($updateOK && isset($recur['BYMONTHDAY'])) {
                $updateOK = iCalUtilityFunctions::_recurBYcntcheck($recur['BYMONTHDAY']
                    , $wdate['day']
                    , $daycnts[$wdate['month']][$wdate['day']]['monthcnt_down']);
            }
            // echo "efter BYMONTHDAY: ".implode('-',$wdate).' status: '; echo ($updateOK) ? 'TRUE' : 'FALSE'; echo "<br />\n";//test###
            if ($updateOK && isset($recur['BYDAY'])) {
                $updateOK = false;
                $m = $wdate['month'];
                $d = $wdate['day'];
                if (isset($recur['BYDAY']['DAY'])) { // single day, opt with year/month day order no
                    $daynoexists = $daynosw = $daynamesw = false;
                    if ($recur['BYDAY']['DAY'] == $daycnts[$m][$d]['DAY']) {
                        $daynamesw = true;
                    }
                    if (isset($recur['BYDAY'][0])) {
                        $daynoexists = true;
                        if ((isset($recur['FREQ']) && ($recur['FREQ'] == 'MONTHLY')) || isset($recur['BYMONTH'])) {
                            $daynosw = iCalUtilityFunctions::_recurBYcntcheck($recur['BYDAY'][0]
                                , $daycnts[$m][$d]['monthdayno_up']
                                , $daycnts[$m][$d]['monthdayno_down']);
                        } elseif (isset($recur['FREQ']) && ($recur['FREQ'] == 'YEARLY')) {
                            $daynosw = iCalUtilityFunctions::_recurBYcntcheck($recur['BYDAY'][0]
                                , $daycnts[$m][$d]['yeardayno_up']
                                , $daycnts[$m][$d]['yeardayno_down']);
                        }
                    }
                    if (($daynoexists && $daynosw && $daynamesw) ||
                        (!$daynoexists && !$daynosw && $daynamesw)) {
                        $updateOK = true;
                        // echo "m=$m d=$d day=".$daycnts[$m][$d]['DAY']." yeardayno_up=".$daycnts[$m][$d]['yeardayno_up']." daynoexists:$daynoexists daynosw:$daynosw daynamesw:$daynamesw updateOK:$updateOK<br />\n"; // test ###
                    }
                    //echo "m=$m d=$d day=".$daycnts[$m][$d]['DAY']." yeardayno_up=".$daycnts[$m][$d]['yeardayno_up']." daynoexists:$daynoexists daynosw:$daynosw daynamesw:$daynamesw updateOK:$updateOK<br />\n"; // test ###
                } else {
                    foreach ($recur['BYDAY'] as $bydayvalue) {
                        $daynoexists = $daynosw = $daynamesw = false;
                        if (isset($bydayvalue['DAY']) &&
                            ($bydayvalue['DAY'] == $daycnts[$m][$d]['DAY'])) {
                            $daynamesw = true;
                        }
                        if (isset($bydayvalue[0])) {
                            $daynoexists = true;
                            if ((isset($recur['FREQ']) && ($recur['FREQ'] == 'MONTHLY')) ||
                                isset($recur['BYMONTH'])) {
                                $daynosw = iCalUtilityFunctions::_recurBYcntcheck($bydayvalue['0']
                                    , $daycnts[$m][$d]['monthdayno_up']
                                    , $daycnts[$m][$d]['monthdayno_down']);
                            } elseif (isset($recur['FREQ']) && ($recur['FREQ'] == 'YEARLY')) {
                                $daynosw = iCalUtilityFunctions::_recurBYcntcheck($bydayvalue['0']
                                    , $daycnts[$m][$d]['yeardayno_up']
                                    , $daycnts[$m][$d]['yeardayno_down']);
                            }
                        }
                        // echo "daynoexists:$daynoexists daynosw:$daynosw daynamesw:$daynamesw<br />\n"; // test ###
                        if (($daynoexists && $daynosw && $daynamesw) ||
                            (!$daynoexists && !$daynosw && $daynamesw)) {
                            $updateOK = true;
                            break;
                        }
                    }
                }
            }
            // echo "efter BYDAY: ".implode('-',$wdate).' status: '; echo ($updateOK) ? 'TRUE' : 'FALSE'; echo "<br />\n"; // test ###
            /* check BYSETPOS */
            if ($updateOK) {
                if (isset($recur['BYSETPOS']) &&
                    (in_array($recur['FREQ'], ['YEARLY', 'MONTHLY', 'WEEKLY', 'DAILY']))) {
                    if (isset($recur['WEEKLY'])) {
                        if ($bysetposWold == $daycnts[$wdate['month']][$wdate['day']]['weekno_up']) {
                            $bysetposw1[] = $wdatets;
                        } else {
                            $bysetposw2[] = $wdatets;
                        }
                    } else {
                        if ((isset($recur['FREQ']) && ('YEARLY' == $recur['FREQ']) &&
                                ($bysetposYold == $wdate['year'])) ||
                            (isset($recur['FREQ']) && ('MONTHLY' == $recur['FREQ']) &&
                                (($bysetposYold == $wdate['year']) &&
                                    ($bysetposMold == $wdate['month']))) ||
                            (isset($recur['FREQ']) && ('DAILY' == $recur['FREQ']) &&
                                (($bysetposYold == $wdate['year']) &&
                                    ($bysetposMold == $wdate['month']) &&
                                    ($bysetposDold == $wdate['day'])))) {
                            // echo "bysetposymd1[]=".date('Y-m-d H:i:s',$wdatets)."<br />\n";//test
                            $bysetposymd1[] = $wdatets;
                        } else {
                            // echo "bysetposymd2[]=".date('Y-m-d H:i:s',$wdatets)."<br />\n";//test
                            $bysetposymd2[] = $wdatets;
                        }
                    }
                } else {
                    /* update result array if BYSETPOS is set */
                    $countcnt++;
                    if ($startdatets <= $wdatets) { // only output within period
                        $result[$wdatets] = true;
                        // echo "recur ".date('Y-m-d H:i:s',$wdatets)."<br />\n";//test
                    }
                    // echo "recur undate ".date('Y-m-d H:i:s',$wdatets)." okdatstart ".date('Y-m-d H:i:s',$startdatets)."<br />\n";//test
                    $updateOK = false;
                }
            }
            /* step up date */
            iCalUtilityFunctions::_stepdate($wdate, $wdatets, $step);
            /* check if BYSETPOS is set for updating result array */
            if ($updateOK && isset($recur['BYSETPOS'])) {
                $bysetpos = false;
                if (isset($recur['FREQ']) && ('YEARLY' == $recur['FREQ']) &&
                    ($bysetposYold != $wdate['year'])) {
                    $bysetpos = true;
                    $bysetposYold = $wdate['year'];
                } elseif (isset($recur['FREQ']) && ('MONTHLY' == $recur['FREQ'] &&
                        (($bysetposYold != $wdate['year']) || ($bysetposMold != $wdate['month'])))) {
                    $bysetpos = true;
                    $bysetposYold = $wdate['year'];
                    $bysetposMold = $wdate['month'];
                } elseif (isset($recur['FREQ']) && ('WEEKLY' == $recur['FREQ'])) {
                    $weekno = (int)date('W', mktime(0, 0, $wkst, $wdate['month'], $wdate['day'], $wdate['year']));
                    if ($bysetposWold != $weekno) {
                        $bysetposWold = $weekno;
                        $bysetpos = true;
                    }
                } elseif (isset($recur['FREQ']) && ('DAILY' == $recur['FREQ']) &&
                    (($bysetposYold != $wdate['year']) ||
                        ($bysetposMold != $wdate['month']) ||
                        ($bysetposDold != $wdate['day']))) {
                    $bysetpos = true;
                    $bysetposYold = $wdate['year'];
                    $bysetposMold = $wdate['month'];
                    $bysetposDold = $wdate['day'];
                }
                if ($bysetpos) {
                    if (isset($recur['BYWEEKNO'])) {
                        $bysetposarr1 = &$bysetposw1;
                        $bysetposarr2 = &$bysetposw2;
                    } else {
                        $bysetposarr1 = &$bysetposymd1;
                        $bysetposarr2 = &$bysetposymd2;
                    }
                    // echo 'test före out startYMD (weekno)='.$wdateStart['year'].':'.$wdateStart['month'].':'.$wdateStart['day']." ($weekStart) "; // test ###
                    foreach ($recur['BYSETPOS'] as $ix) {
                        if (0 > $ix) // both positive and negative BYSETPOS allowed
                        {
                            $ix = (count($bysetposarr1) + $ix + 1);
                        }
                        $ix--;
                        if (isset($bysetposarr1[$ix])) {
                            if ($startdatets <= $bysetposarr1[$ix]) { // only output within period
                                //                $testdate   = iCalUtilityFunctions::_timestamp2date( $bysetposarr1[$ix], 6 );                // test ###
                                //                $testweekno = (int) date( 'W', mktime( 0, 0, $wkst, $testdate['month'], $testdate['day'], $testdate['year'] )); // test ###
                                // echo " testYMD (weekno)=".$testdate['year'].':'.$testdate['month'].':'.$testdate['day']." ($testweekno)";   // test ###
                                $result[$bysetposarr1[$ix]] = true;
                                // echo " recur ".date('Y-m-d H:i:s',$bysetposarr1[$ix]); // test ###
                            }
                            $countcnt++;
                        }
                        if (isset($recur['COUNT']) && ($countcnt >= $recur['COUNT'])) {
                            break;
                        }
                    }
                    // echo "<br />\n"; // test ###
                    $bysetposarr1 = $bysetposarr2;
                    $bysetposarr2 = [];
                }
            }
        }
    }

    public static function _recurBYcntcheck($BYvalue, $upValue, $downValue)
    {
        if (is_array($BYvalue) &&
            (in_array($upValue, $BYvalue) || in_array($downValue, $BYvalue))) {
            return true;
        } elseif (($BYvalue == $upValue) || ($BYvalue == $downValue)) {
            return true;
        } else {
            return false;
        }
    }

    public static function _recurIntervalIx($freq, $date, $wkst)
    {
        /* create interval index */
        switch ($freq) {
            case 'YEARLY':
                $intervalix = $date['year'];
                break;
            case 'MONTHLY':
                $intervalix = $date['year'] . '-' . $date['month'];
                break;
            case 'WEEKLY':
                $wdatets = iCalUtilityFunctions::_date2timestamp($date);
                $intervalix = (int)date('W', ($wdatets + $wkst));
                break;
            case 'DAILY':
            default:
                $intervalix = $date['year'] . '-' . $date['month'] . '-' . $date['day'];
                break;
        }
        return $intervalix;
    }

    /**
     * convert input format for exrule and rrule to internal format
     *
     * @param array $rexrule
     * @return array
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.15 - 2012-01-31
     */
    public static function _setRexrule($rexrule)
    {
        $input = [];
        if (empty($rexrule)) {
            return $input;
        }
        foreach ($rexrule as $rexrulelabel => $rexrulevalue) {
            $rexrulelabel = strtoupper($rexrulelabel);
            if ('UNTIL' != $rexrulelabel) {
                $input[$rexrulelabel] = $rexrulevalue;
            } else {
                iCalUtilityFunctions::_strDate2arr($rexrulevalue);
                if (iCalUtilityFunctions::_isArrayTimestampDate($rexrulevalue)) // timestamp, always date-time
                {
                    $input[$rexrulelabel] = iCalUtilityFunctions::_timestamp2date($rexrulevalue, 6);
                } elseif (iCalUtilityFunctions::_isArrayDate($rexrulevalue)) { // date or date-time
                    $parno = (isset($rexrulevalue['hour']) || isset($rexrulevalue[4])) ? 6 : 3;
                    $input[$rexrulelabel] = iCalUtilityFunctions::_date_time_array($rexrulevalue, $parno);
                } elseif (8 <= strlen(trim($rexrulevalue))) { // ex. textual datetime/date 2006-08-03 10:12:18
                    $input[$rexrulelabel] = iCalUtilityFunctions::_date_time_string($rexrulevalue);
                    unset($input['$rexrulelabel']['unparsedtext']);
                }
                if ((3 < count($input[$rexrulelabel])) && !isset($input[$rexrulelabel]['tz'])) {
                    $input[$rexrulelabel]['tz'] = 'Z';
                }
            }
        }
        /* set recurrence rule specification in rfc2445 order */
        $input2 = [];
        if (isset($input['FREQ'])) {
            $input2['FREQ'] = $input['FREQ'];
        }
        if (isset($input['UNTIL'])) {
            $input2['UNTIL'] = $input['UNTIL'];
        } elseif (isset($input['COUNT'])) {
            $input2['COUNT'] = $input['COUNT'];
        }
        if (isset($input['INTERVAL'])) {
            $input2['INTERVAL'] = $input['INTERVAL'];
        }
        if (isset($input['BYSECOND'])) {
            $input2['BYSECOND'] = $input['BYSECOND'];
        }
        if (isset($input['BYMINUTE'])) {
            $input2['BYMINUTE'] = $input['BYMINUTE'];
        }
        if (isset($input['BYHOUR'])) {
            $input2['BYHOUR'] = $input['BYHOUR'];
        }
        if (isset($input['BYDAY'])) {
            if (!is_array($input['BYDAY'])) // ensure upper case.. .
            {
                $input2['BYDAY'] = strtoupper($input['BYDAY']);
            } else {
                foreach ($input['BYDAY'] as $BYDAYx => $BYDAYv) {
                    if ('DAY' == strtoupper($BYDAYx)) {
                        $input2['BYDAY']['DAY'] = strtoupper($BYDAYv);
                    } elseif (!is_array($BYDAYv)) {
                        $input2['BYDAY'][$BYDAYx] = $BYDAYv;
                    } else {
                        foreach ($BYDAYv as $BYDAYx2 => $BYDAYv2) {
                            if ('DAY' == strtoupper($BYDAYx2)) {
                                $input2['BYDAY'][$BYDAYx]['DAY'] = strtoupper($BYDAYv2);
                            } else {
                                $input2['BYDAY'][$BYDAYx][$BYDAYx2] = $BYDAYv2;
                            }
                        }
                    }
                }
            }
        }
        if (isset($input['BYMONTHDAY'])) {
            $input2['BYMONTHDAY'] = $input['BYMONTHDAY'];
        }
        if (isset($input['BYYEARDAY'])) {
            $input2['BYYEARDAY'] = $input['BYYEARDAY'];
        }
        if (isset($input['BYWEEKNO'])) {
            $input2['BYWEEKNO'] = $input['BYWEEKNO'];
        }
        if (isset($input['BYMONTH'])) {
            $input2['BYMONTH'] = $input['BYMONTH'];
        }
        if (isset($input['BYSETPOS'])) {
            $input2['BYSETPOS'] = $input['BYSETPOS'];
        }
        if (isset($input['WKST'])) {
            $input2['WKST'] = $input['WKST'];
        }
        return $input2;
    }

    /**
     * convert format for input date to internal date with parameters
     *
     * @param mixed $year
     * @param mixed $month optional
     * @param int $day optional
     * @param int $hour optional
     * @param int $min optional
     * @param int $sec optional
     * @param string $tz optional
     * @param array $params optional
     * @param string $caller optional
     * @param string $objName optional
     * @param string $tzid optional
     * @return array
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.8 - 2012-03-18
     */
    public static function _setDate($year, $month = false, $day = false, $hour = false, $min = false, $sec = false, $tz = false, $params = false, $caller = null, $objName = null, $tzid = false)
    {
        $input = $parno = null;
        $localtime = (('dtstart' == $caller) && in_array($objName, ['vtimezone', 'standard', 'daylight'])) ? true : false;
        iCalUtilityFunctions::_strDate2arr($year);
        if (iCalUtilityFunctions::_isArrayDate($year)) {
            if ($localtime) {
                unset ($month['VALUE'], $month['TZID']);
            }
            $input['params'] = iCalUtilityFunctions::_setParams($month, ['VALUE' => 'DATE-TIME']);
            if (isset($input['params']['TZID'])) {
                $input['params']['VALUE'] = 'DATE-TIME';
                unset($year['tz']);
            }
            $hitval = (isset($year['tz']) || isset($year[6])) ? 7 : 6;
            $parno = iCalUtilityFunctions::_existRem($input['params'], 'VALUE', 'DATE-TIME', $hitval);
            $parno = iCalUtilityFunctions::_existRem($input['params'], 'VALUE', 'DATE', 3, count($year), $parno);
            $input['value'] = iCalUtilityFunctions::_date_time_array($year, $parno);
        } elseif (iCalUtilityFunctions::_isArrayTimestampDate($year)) {
            if ($localtime) {
                unset ($month['VALUE'], $month['TZID']);
            }
            $input['params'] = iCalUtilityFunctions::_setParams($month, ['VALUE' => 'DATE-TIME']);
            if (isset($input['params']['TZID'])) {
                $input['params']['VALUE'] = 'DATE-TIME';
                unset($year['tz']);
            }
            $parno = iCalUtilityFunctions::_existRem($input['params'], 'VALUE', 'DATE', 3);
            $hitval = (isset($year['tz'])) ? 7 : 6;
            $parno = iCalUtilityFunctions::_existRem($input['params'], 'VALUE', 'DATE-TIME', $hitval, $parno);
            $input['value'] = iCalUtilityFunctions::_timestamp2date($year, $parno);
        } elseif (8 <= strlen(trim($year))) { // ex. 2006-08-03 10:12:18
            if ($localtime) {
                unset ($month['VALUE'], $month['TZID']);
            }
            $input['params'] = iCalUtilityFunctions::_setParams($month, ['VALUE' => 'DATE-TIME']);
            if (isset($input['params']['TZID'])) {
                $input['params']['VALUE'] = 'DATE-TIME';
                $parno = 6;
            } elseif ($tzid && iCalUtilityFunctions::_isOffset(substr($year, -7))) {
                if ((in_array(substr($year, -5, 1), ['+', '-'])) &&
                    ('0000' < substr($year, -4)) && ('9999' >= substr($year, -4))) {
                    $year = substr($year, 0, (strlen($year) - 5));
                } elseif ((in_array(substr($input, -7, 1), ['+', '-'])) &&
                    ('000000' < substr($input, -6)) && ('999999' >= substr($input, -6))) {
                    $year = substr($year, 0, (strlen($year) - 7));
                }
                $parno = 6;
            }
            $parno = iCalUtilityFunctions::_existRem($input['params'], 'VALUE', 'DATE-TIME', 7, $parno);
            $parno = iCalUtilityFunctions::_existRem($input['params'], 'VALUE', 'DATE', 3, $parno, $parno);
            $input['value'] = iCalUtilityFunctions::_date_time_string($year, $parno);
            unset($input['value']['unparsedtext']);
        } else {
            if (is_array($params)) {
                if ($localtime) {
                    unset ($params['VALUE'], $params['TZID']);
                }
                $input['params'] = iCalUtilityFunctions::_setParams($params, ['VALUE' => 'DATE-TIME']);
            } elseif (is_array($tz)) {
                $input['params'] = iCalUtilityFunctions::_setParams($tz, ['VALUE' => 'DATE-TIME']);
                $tz = false;
            } elseif (is_array($hour)) {
                $input['params'] = iCalUtilityFunctions::_setParams($hour, ['VALUE' => 'DATE-TIME']);
                $hour = $min = $sec = $tz = false;
            }
            if (isset($input['params']['TZID'])) {
                $tz = null;
                $input['params']['VALUE'] = 'DATE-TIME';
            }
            $parno = iCalUtilityFunctions::_existRem($input['params'], 'VALUE', 'DATE', 3);
            $hitval = (!empty($tz)) ? 7 : 6;
            $parno = iCalUtilityFunctions::_existRem($input['params'], 'VALUE', 'DATE-TIME', $hitval, $parno, $parno);
            $input['value'] = ['year' => $year, 'month' => $month, 'day' => $day];
            if (3 != $parno) {
                $input['value']['hour'] = ($hour) ? $hour : '0';
                $input['value']['min'] = ($min) ? $min : '0';
                $input['value']['sec'] = ($sec) ? $sec : '0';
                if (!empty($tz)) {
                    $input['value']['tz'] = $tz;
                }
            }
        }
        if (3 == $parno) {
            $input['params']['VALUE'] = 'DATE';
            unset($input['value']['tz']);
            unset($input['params']['TZID']);
        } elseif (isset($input['params']['TZID'])) {
            unset($input['value']['tz']);
        }
        if ($localtime) {
            unset($input['value']['tz'], $input['params']['TZID']);
        } elseif ((!isset($input['params']['VALUE']) || ($input['params']['VALUE'] != 'DATE')) && !isset($input['params']['TZID']) && $tzid) {
            $input['params']['TZID'] = $tzid;
        }
        if (isset($input['value']['tz'])) {
            $input['value']['tz'] = (string)$input['value']['tz'];
        }
        if (!empty($input['value']['tz']) && ('Z' != $input['value']['tz']) && // real time zone in tz to TZID
            (!iCalUtilityFunctions::_isOffset($input['value']['tz']))) {
            $input['params']['TZID'] = $input['value']['tz'];
            unset($input['value']['tz']);
        }
        if (isset($input['params']['TZID']) && !empty($input['params']['TZID'])) {
            if (('Z' != $input['params']['TZID']) && iCalUtilityFunctions::_isOffset($input['params']['TZID'])) {  // utc offset in TZID to tz
                $input['value']['tz'] = $input['params']['TZID'];
                unset($input['params']['TZID']);
            } elseif (in_array(strtoupper($input['params']['TZID']), ['GMT', 'UTC', 'Z'])) { // time zone Z
                $input['value']['tz'] = 'Z';
                unset($input['params']['TZID']);
            }
        }
        return $input;
    }

    /**
     * convert format for input date (UTC) to internal date with parameters
     *
     * @param mixed $year
     * @param mixed $month optional
     * @param int $day optional
     * @param int $hour optional
     * @param int $min optional
     * @param int $sec optional
     * @param array $params optional
     * @return array
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.8 - 2012-01-19
     */
    public static function _setDate2($year, $month = false, $day = false, $hour = false, $min = false, $sec = false, $params = false)
    {
        $input = null;
        iCalUtilityFunctions::_strDate2arr($year);
        if (iCalUtilityFunctions::_isArrayDate($year)) {
            $input['value'] = iCalUtilityFunctions::_date_time_array($year, 7);
            $input['params'] = iCalUtilityFunctions::_setParams($month, ['VALUE' => 'DATE-TIME']);
        } elseif (iCalUtilityFunctions::_isArrayTimestampDate($year)) {
            $input['value'] = iCalUtilityFunctions::_timestamp2date($year, 7);
            $input['params'] = iCalUtilityFunctions::_setParams($month, ['VALUE' => 'DATE-TIME']);
        } elseif (8 <= strlen(trim($year))) { // ex. 2006-08-03 10:12:18
            $input['value'] = iCalUtilityFunctions::_date_time_string($year, 7);
            unset($input['value']['unparsedtext']);
            $input['params'] = iCalUtilityFunctions::_setParams($month, ['VALUE' => 'DATE-TIME']);
        } else {
            $input['value'] = [
                'year'  => $year
                ,
                'month' => $month
                ,
                'day'   => $day
                ,
                'hour'  => $hour
                ,
                'min'   => $min
                ,
                'sec'   => $sec,
            ];
            $input['params'] = iCalUtilityFunctions::_setParams($params, ['VALUE' => 'DATE-TIME']);
        }
        $parno = iCalUtilityFunctions::_existRem($input['params'], 'VALUE', 'DATE-TIME', 7); // remove default
        if (!isset($input['value']['hour'])) {
            $input['value']['hour'] = 0;
        }
        if (!isset($input['value']['min'])) {
            $input['value']['min'] = 0;
        }
        if (!isset($input['value']['sec'])) {
            $input['value']['sec'] = 0;
        }
        if (isset($input['params']['TZID']) && !empty($input['params']['TZID'])) {
            if (('Z' != $input['params']['TZID']) && iCalUtilityFunctions::_isOffset($input['params']['TZID'])) {  // utc offset in TZID to tz
                $input['value']['tz'] = $input['params']['TZID'];
                unset($input['params']['TZID']);
            } elseif (in_array(strtoupper($input['params']['TZID']), ['GMT', 'UTC', 'Z'])) { // time zone Z
                $input['value']['tz'] = 'Z';
                unset($input['params']['TZID']);
            }
        }
        if (!isset($input['value']['tz']) || !iCalUtilityFunctions::_isOffset($input['value']['tz'])) {
            $input['value']['tz'] = 'Z';
        }
        return $input;
    }

    /**
     * check index and set (an indexed) content in multiple value array
     *
     * @param array $valArr
     * @param mixed $value
     * @param array $params
     * @param array $defaults
     * @param int $index
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.6.12 - 2011-01-03
     */
    public static function _setMval(&$valArr, $value, $params = false, $defaults = false, $index = false)
    {
        if (!is_array($valArr)) {
            $valArr = [];
        }
        if ($index) {
            $index = $index - 1;
        } elseif (0 < count($valArr)) {
            $keys = array_keys($valArr);
            $index = end($keys) + 1;
        } else {
            $index = 0;
        }
        $valArr[$index] = ['value' => $value, 'params' => iCalUtilityFunctions::_setParams($params, $defaults)];
        ksort($valArr);
    }

    /**
     * set input (formatted) parameters- component property attributes
     *
     * default parameters can be set, if missing
     *
     * @param array $params
     * @param array $defaults
     * @return array
     * @since 1.x.x - 2007-05-01
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    public static function _setParams($params, $defaults = false)
    {
        if (!is_array($params)) {
            $params = [];
        }
        $input = [];
        foreach ($params as $paramKey => $paramValue) {
            if (is_array($paramValue)) {
                foreach ($paramValue as $pkey => $pValue) {
                    if (('"' == substr($pValue, 0, 1)) && ('"' == substr($pValue, -1))) {
                        $paramValue[$pkey] = substr($pValue, 1, (strlen($pValue) - 2));
                    }
                }
            } elseif (('"' == substr($paramValue, 0, 1)) && ('"' == substr($paramValue, -1))) {
                $paramValue = substr($paramValue, 1, (strlen($paramValue) - 2));
            }
            if ('VALUE' == strtoupper($paramKey)) {
                $input['VALUE'] = strtoupper($paramValue);
            } else {
                $input[strtoupper($paramKey)] = $paramValue;
            }
        }
        if (is_array($defaults)) {
            foreach ($defaults as $paramKey => $paramValue) {
                if (!isset($input[$paramKey])) {
                    $input[$paramKey] = $paramValue;
                }
            }
        }
        return (0 < count($input)) ? $input : null;
    }

    /**
     * step date, return updated date, array and timpstamp
     *
     * @param array $date , date to step
     * @param int $timestamp
     * @param array $step , default array( 'day' => 1 )
     * @return void
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.4.16 - 2008-10-18
     */
    public static function _stepdate(&$date, &$timestamp, $step = ['day' => 1])
    {
        foreach ($step as $stepix => $stepvalue) {
            $date[$stepix] += $stepvalue;
        }
        $timestamp = iCalUtilityFunctions::_date2timestamp($date);
        $date = iCalUtilityFunctions::_timestamp2date($timestamp, 6);
        foreach ($date as $k => $v) {
            if (ctype_digit($v)) {
                $date[$k] = (int)$v;
            }
        }
    }

    /**
     * convert a date from specific string to array format
     *
     * @param mixed $input
     * @return bool, TRUE on success
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.8 - 2012-01-27
     */
    public static function _strDate2arr(&$input)
    {
        if (is_array($input)) {
            return false;
        }
        if (5 > strlen((string)$input)) {
            return false;
        }
        $work = $input;
        if (2 == substr_count($work, '-')) {
            $work = str_replace('-', '', $work);
        }
        if (2 == substr_count($work, '/')) {
            $work = str_replace('/', '', $work);
        }
        if (!ctype_digit(substr($work, 0, 8))) {
            return false;
        }
        if (!checkdate((int)substr($work, 4, 2), (int)substr($work, 6, 2), (int)substr($work, 0, 4))) {
            return false;
        }
        $temp = [
            'year'  => substr($work, 0, 4)
            ,
            'month' => substr($work, 4, 2)
            ,
            'day'   => substr($work, 6, 2),
        ];
        if (8 == strlen($work)) {
            $input = $temp;
            return true;
        }
        if ((' ' == substr($work, 8, 1)) || ('T' == substr($work, 8, 1)) || ('t' == substr($work, 8, 1))) {
            $work = substr($work, 9);
        } elseif (ctype_digit(substr($work, 8, 1))) {
            $work = substr($work, 8);
        } else {
            return false;
        }
        if (2 == substr_count($work, ':')) {
            $work = str_replace(':', '', $work);
        }
        if (!ctype_digit(substr($work, 0, 4))) {
            return false;
        }
        $temp['hour'] = substr($work, 0, 2);
        $temp['min'] = substr($work, 2, 2);
        if (((0 > $temp['hour']) || ($temp['hour'] > 23)) ||
            ((0 > $temp['min']) || ($temp['min'] > 59))) {
            return false;
        }
        if (ctype_digit(substr($work, 4, 2))) {
            $temp['sec'] = substr($work, 4, 2);
            if ((0 > $temp['sec']) || ($temp['sec'] > 59)) {
                return false;
            }
            $len = 6;
        } else {
            $temp['sec'] = 0;
            $len = 4;
        }
        if ($len < strlen($work)) {
            $temp['tz'] = trim(substr($work, 6));
        }
        $input = $temp;
        return true;
    }

    /**
     * convert timestamp to date array
     *
     * @param mixed $timestamp
     * @param int $parno
     * @return array
     * @since 2.4.16 - 2008-11-01
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    public static function _timestamp2date($timestamp, $parno = 6)
    {
        if (is_array($timestamp)) {
            if ((7 == $parno) && !empty($timestamp['tz'])) {
                $tz = $timestamp['tz'];
            }
            $timestamp = $timestamp['timestamp'];
        }
        $output = [
            'year'  => date('Y', $timestamp)
            ,
            'month' => date('m', $timestamp)
            ,
            'day'   => date('d', $timestamp),
        ];
        if (3 != $parno) {
            $output['hour'] = date('H', $timestamp);
            $output['min'] = date('i', $timestamp);
            $output['sec'] = date('s', $timestamp);
            if (isset($tz)) {
                $output['tz'] = $tz;
            }
        }
        return $output;
    }

    /**
     * convert timestamp to duration in array format
     *
     * @param int $timestamp
     * @return array, duration format
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.6.23 - 2010-10-23
     */
    public static function _timestamp2duration($timestamp)
    {
        $dur = [];
        $dur['week'] = (int)floor($timestamp / (7 * 24 * 60 * 60));
        $timestamp = $timestamp % (7 * 24 * 60 * 60);
        $dur['day'] = (int)floor($timestamp / (24 * 60 * 60));
        $timestamp = $timestamp % (24 * 60 * 60);
        $dur['hour'] = (int)floor($timestamp / (60 * 60));
        $timestamp = $timestamp % (60 * 60);
        $dur['min'] = (int)floor($timestamp / (60));
        $dur['sec'] = (int)$timestamp % (60);
        return $dur;
    }

    /**
     * transforms a dateTime from a timezone to another using PHP DateTime and DateTimeZone class (PHP >= PHP 5.2.0)
     *
     * @param mixed $date ,   date to alter
     * @param string $tzFrom , PHP valid old timezone
     * @param string $tzTo ,   PHP valid new timezone, default 'UTC'
     * @param string $format , date output format, default 'Ymd\THis'
     * @return bool
     * @since 2.11.14 - 2012-01-24
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     */
    public static function transformDateTime(&$date, $tzFrom, $tzTo = 'UTC', $format = 'Ymd\THis')
    {
        if (!class_exists('DateTime') || !class_exists('DateTimeZone')) {
            return false;
        }
        if (is_array($date) && isset($date['timestamp'])) {
            $timestamp = $date['timestamp'];
        } elseif (iCalUtilityFunctions::_isArrayDate($date)) {
            if (isset($date['tz'])) {
                unset($date['tz']);
            }
            $date = iCalUtilityFunctions::_format_date_time(iCalUtilityFunctions::_date_time_array($date));
            if ('Z' == substr($date, -1)) {
                $date = substr($date, 0, (strlen($date) - 2));
            }
            if (false === ($timestamp = strtotime($date))) {
                return false;
            }
        } elseif (false === ($timestamp = @strtotime($date))) {
            return false;
        }
        try {
            $d = new DateTime(date('Y-m-d H:i:s', $timestamp), new DateTimeZone($tzFrom));
            $d->setTimezone(new DateTimeZone($tzTo));
        } catch (Exception $e) {
            return false;
        }
        $date = $d->format($format);
        return true;
    }

    /**
     * convert (numeric) local time offset, ("+" / "-")HHmm[ss], to seconds correcting localtime to GMT
     *
     * @param string $offset
     * @return integer
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since 2.11.4 - 2012-01-11
     */
    public static function _tz2offset($tz)
    {
        $tz = trim((string)$tz);
        $offset = 0;
        if (((5 != strlen($tz)) && (7 != strlen($tz))) ||
            (('+' != substr($tz, 0, 1)) && ('-' != substr($tz, 0, 1))) ||
            (('0000' >= substr($tz, 1, 4)) && ('9999' < substr($tz, 1, 4))) ||
            ((7 == strlen($tz)) && ('00' > substr($tz, 5, 2)) && ('99' < substr($tz, 5, 2)))) {
            return $offset;
        }
        $hours2sec = (int)substr($tz, 1, 2) * 3600;
        $min2sec = (int)substr($tz, 3, 2) * 60;
        $sec = (7 == strlen($tz)) ? (int)substr($tz, -2) : '00';
        $offset = $hours2sec + $min2sec + $sec;
        $offset = ('-' == substr($tz, 0, 1)) ? $offset * -1 : $offset;
        return $offset;
    }
}

/*********************************************************************************/
/*          iCalcreator XML (rfc6321) helper functions                           */
/*********************************************************************************/
/**
 * format iCal XML output, rfc6321, using PHP SimpleXMLElement
 *
 * @param object $calendar , iCalcreator vcalendar instance reference
 * @return string
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.11.1 - 2012-02-22
 */
function iCal2XML(&$calendar)
{
    /** fix an SimpleXMLElement instance and create root element */
    $xmlstr = '<?xml version="1.0" encoding="utf-8"?><icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">';
    $xmlstr .= '<!-- created utilizing kigkonsult.se ' . ICALCREATOR_VERSION . ' iCal2XMl (rfc6321) -->';
    $xmlstr .= '</icalendar>';
    $xml = new SimpleXMLElement($xmlstr);
    $vcalendar = $xml->addChild('vcalendar');
    /** fix calendar properties */
    $properties = $vcalendar->addChild('properties');
    $calProps = ['prodid', 'version', 'calscale', 'method'];
    foreach ($calProps as $calProp) {
        if (false !== ($content = $calendar->getProperty($calProp))) {
            _addXMLchild($properties, $calProp, 'text', $content);
        }
    }
    while (false !== ($content = $calendar->getProperty(false, false, true))) {
        _addXMLchild($properties, $content[0], 'unknown', $content[1]['value'], $content[1]['params']);
    }
    $langCal = $calendar->getConfig('language');
    /** prepare to fix components with properties */
    $components = $vcalendar->addChild('components');
    $comps = ['vtimezone', 'vevent', 'vtodo', 'vjournal', 'vfreebusy'];
    $eventProps = [
        'dtstamp',
        'dtstart',
        'uid',
        'class',
        'created',
        'description',
        'geo',
        'last-modified',
        'location',
        'organizer',
        'priority',
        'sequence',
        'status',
        'summary',
        'transp',
        'url',
        'recurrence-id',
        'rrule',
        'dtend',
        'duration',
        'attach',
        'attendee',
        'categories',
        'comment',
        'contact',
        'exdate',
        'request-status',
        'related-to',
        'resources',
        'rdate',
        'x-prop',
    ];
    $todoProps = [
        'dtstamp',
        'uid',
        'class',
        'completed',
        'created',
        'description',
        'geo',
        'last-modified',
        'location',
        'organizer',
        'percent-complete',
        'priority',
        'recurrence-id',
        'sequence',
        'status',
        'summary',
        'url',
        'rrule',
        'dtstart',
        'due',
        'duration',
        'attach',
        'attendee',
        'categories',
        'comment',
        'contact',
        'exdate',
        'request-status',
        'related-to',
        'resources',
        'rdate',
        'x-prop',
    ];
    $journalProps = [
        'dtstamp',
        'uid',
        'class',
        'created',
        'dtstart',
        'last-modified',
        'organizer',
        'recurrence-id',
        'sequence',
        'status',
        'summary',
        'url',
        'rrule',
        'attach',
        'attendee',
        'categories',
        'comment',
        'contact',
        'description',
        'exdate',
        'related-to',
        'rdate',
        'request-status',
        'x-prop',
    ];
    $freebusyProps = [
        'dtstamp',
        'uid',
        'contact',
        'dtstart',
        'dtend',
        'duration',
        'organizer',
        'url',
        'attendee',
        'comment',
        'freebusy',
        'request-status',
        'x-prop',
    ];
    $timezoneProps = [
        'tzid',
        'last-modified',
        'tzurl',
        'x-prop',
    ];
    $alarmProps = [
        'action',
        'description',
        'trigger',
        'summary',
        'attendee',
        'duration',
        'repeat',
        'attach',
        'x-prop',
    ];
    $stddghtProps = [
        'dtstart',
        'tzoffsetto',
        'tzoffsetfrom',
        'rrule',
        'comment',
        'rdate',
        'tzname',
        'x-prop',
    ];
    foreach ($comps as $compName) {
        switch ($compName) {
            case 'vevent':
                $props = &$eventProps;
                $subComps = ['valarm'];
                $subCompProps = &$alarmProps;
                break;
            case 'vtodo':
                $props = &$todoProps;
                $subComps = ['valarm'];
                $subCompProps = &$alarmProps;
                break;
            case 'vjournal':
                $props = &$journalProps;
                $subComps = [];
                $subCompProps = [];
                break;
            case 'vfreebusy':
                $props = &$freebusyProps;
                $subComps = [];
                $subCompProps = [];
                break;
            case 'vtimezone':
                $props = &$timezoneProps;
                $subComps = ['standard', 'daylight'];
                $subCompProps = &$stddghtProps;
                break;
        } // end switch( $compName )
        /** fix component properties */
        while (false !== ($component = $calendar->getComponent($compName))) {
            $child = $components->addChild($compName);
            $properties = $child->addChild('properties');
            $langComp = $component->getConfig('language');
            foreach ($props as $prop) {
                switch ($prop) {
                    case 'attach':          // may occur multiple times, below
                        while (false !== ($content = $component->getProperty($prop, false, true))) {
                            $type = (isset($content['params']['VALUE']) && ('BINARY' == $content['params']['VALUE'])) ? 'binary' : 'uri';
                            unset($content['params']['VALUE']);
                            _addXMLchild($properties, $prop, $type, $content['value'], $content['params']);
                        }
                        break;
                    case 'attendee':
                        while (false !== ($content = $component->getProperty($prop, false, true))) {
                            if (isset($content['params']['CN']) && !isset($content['params']['LANGUAGE'])) {
                                if ($langComp) {
                                    $content['params']['LANGUAGE'] = $langComp;
                                } elseif ($langCal) {
                                    $content['params']['LANGUAGE'] = $langCal;
                                }
                            }
                            _addXMLchild($properties, $prop, 'cal-address', $content['value'], $content['params']);
                        }
                        break;
                    case 'exdate':
                        while (false !== ($content = $component->getProperty($prop, false, true))) {
                            $type = (isset($content['params']['VALUE']) && ('DATE' == $content['params']['VALUE'])) ? 'date' : 'date-time';
                            unset($content['params']['VALUE']);
                            foreach ($content['value'] as & $exDate) {
                                if ((isset($exDate['tz']) &&  // fix UTC-date if offset set
                                        iCalUtilityFunctions::_isOffset($exDate['tz']) &&
                                        ('Z' != $exDate['tz']))
                                    || (isset($content['params']['TZID']) &&
                                        iCalUtilityFunctions::_isOffset($content['params']['TZID']) &&
                                        ('Z' != $content['params']['TZID']))) {
                                    $offset = isset($exDate['tz']) ? $exDate['tz'] : $content['params']['TZID'];
                                    $date = mktime((int)$exDate['hour'],
                                        (int)$exDate['min'],
                                        (int)($exDate['sec'] + iCalUtilityFunctions::_tz2offset($offset)),
                                        (int)$exDate['month'],
                                        (int)$exDate['day'],
                                        (int)$exDate['year']);
                                    unset($exDate['tz']);
                                    $exDate = iCalUtilityFunctions::_date_time_string(date('YmdTHis\Z', $date), 6);
                                    unset($exDate['unparsedtext']);
                                }
                            }
                            _addXMLchild($properties, $prop, $type, $content['value'], $content['params']);
                        }
                        break;
                    case 'freebusy':
                        while (false !== ($content = $component->getProperty($prop, false, true))) {
                            _addXMLchild($properties, $prop, 'period', $content['value'], $content['params']);
                        }
                        break;
                    case 'request-status':
                        while (false !== ($content = $component->getProperty($prop, false, true))) {
                            if (!isset($content['params']['LANGUAGE'])) {
                                if ($langComp) {
                                    $content['params']['LANGUAGE'] = $langComp;
                                } elseif ($langCal) {
                                    $content['params']['LANGUAGE'] = $langCal;
                                }
                            }
                            _addXMLchild($properties, $prop, 'rstatus', $content['value'], $content['params']);
                        }
                        break;
                    case 'rdate':
                        while (false !== ($content = $component->getProperty($prop, false, true))) {
                            $type = 'date-time';
                            if (isset($content['params']['VALUE'])) {
                                if ('DATE' == $content['params']['VALUE']) {
                                    $type = 'date';
                                } elseif ('PERIOD' == $content['params']['VALUE']) {
                                    $type = 'period';
                                }
                            }
                            if ('period' == $type) {
                                foreach ($content['value'] as & $rDates) {
                                    if ((isset($rDates[0]['tz']) &&  // fix UTC-date if offset set
                                            iCalUtilityFunctions::_isOffset($rDates[0]['tz']) &&
                                            ('Z' != $rDates[0]['tz']))
                                        || (isset($content['params']['TZID']) &&
                                            iCalUtilityFunctions::_isOffset($content['params']['TZID']) &&
                                            ('Z' != $content['params']['TZID']))) {
                                        $offset = isset($rDates[0]['tz']) ? $rDates[0]['tz'] : $content['params']['TZID'];
                                        $date = mktime((int)$rDates[0]['hour'],
                                            (int)$rDates[0]['min'],
                                            (int)($rDates[0]['sec'] + iCalUtilityFunctions::_tz2offset($offset)),
                                            (int)$rDates[0]['month'],
                                            (int)$rDates[0]['day'],
                                            (int)$rDates[0]['year']);
                                        unset($rDates[0]['tz']);
                                        $rDates[0] = iCalUtilityFunctions::_date_time_string(date('YmdTHis\Z', $date), 6);
                                        unset($rDates[0]['unparsedtext']);
                                    }
                                    if (isset($rDates[1]['year'])) {
                                        if ((isset($rDates[1]['tz']) &&  // fix UTC-date if offset set
                                                iCalUtilityFunctions::_isOffset($rDates[1]['tz']) &&
                                                ('Z' != $rDates[1]['tz']))
                                            || (isset($content['params']['TZID']) &&
                                                iCalUtilityFunctions::_isOffset($content['params']['TZID']) &&
                                                ('Z' != $content['params']['TZID']))) {
                                            $offset = isset($rDates[1]['tz']) ? $rDates[1]['tz'] : $content['params']['TZID'];
                                            $date = mktime((int)$rDates[1]['hour'],
                                                (int)$rDates[1]['min'],
                                                (int)($rDates[1]['sec'] + iCalUtilityFunctions::_tz2offset($offset)),
                                                (int)$rDates[1]['month'],
                                                (int)$rDates[1]['day'],
                                                (int)$rDates[1]['year']);
                                            unset($rDates[1]['tz']);
                                            $rDates[1] = iCalUtilityFunctions::_date_time_string(date('YmdTHis\Z', $date), 6);
                                            unset($rDates[1]['unparsedtext']);
                                        }
                                    }
                                }
                            } elseif ('date-time' == $type) {
                                foreach ($content['value'] as & $rDate) {
                                    if ((isset($rDate['tz']) &&  // fix UTC-date if offset set
                                            iCalUtilityFunctions::_isOffset($rDate['tz']) &&
                                            ('Z' != $rDate['tz']))
                                        || (isset($content['params']['TZID']) &&
                                            iCalUtilityFunctions::_isOffset($content['params']['TZID']) &&
                                            ('Z' != $content['params']['TZID']))) {
                                        $offset = isset($rDate['tz']) ? $rDate['tz'] : $content['params']['TZID'];
                                        $date = mktime((int)$rDate['hour'],
                                            (int)$rDate['min'],
                                            (int)($rDate['sec'] + iCalUtilityFunctions::_tz2offset($offset)),
                                            (int)$rDate['month'],
                                            (int)$rDate['day'],
                                            (int)$rDate['year']);
                                        unset($rDate['tz']);
                                        $rDate = iCalUtilityFunctions::_date_time_string(date('YmdTHis\Z', $date), 6);
                                        unset($rDate['unparsedtext']);
                                    }
                                }
                            }
                            unset($content['params']['VALUE']);
                            _addXMLchild($properties, $prop, $type, $content['value'], $content['params']);
                        }
                        break;
                    case 'categories':
                    case 'comment':
                    case 'contact':
                    case 'description':
                    case 'related-to':
                    case 'resources':
                        while (false !== ($content = $component->getProperty($prop, false, true))) {
                            if (('related-to' != $prop) && !isset($content['params']['LANGUAGE'])) {
                                if ($langComp) {
                                    $content['params']['LANGUAGE'] = $langComp;
                                } elseif ($langCal) {
                                    $content['params']['LANGUAGE'] = $langCal;
                                }
                            }
                            _addXMLchild($properties, $prop, 'text', $content['value'], $content['params']);
                        }
                        break;
                    case 'x-prop':
                        while (false !== ($content = $component->getProperty($prop, false, true))) {
                            _addXMLchild($properties, $content[0], 'unknown', $content[1]['value'], $content[1]['params']);
                        }
                        break;
                    case 'created':         // single occurence below, if set
                    case 'completed':
                    case 'dtstamp':
                    case 'last-modified':
                        $utcDate = true;
                    case 'dtstart':
                    case 'dtend':
                    case 'due':
                    case 'recurrence-id':
                        if (false !== ($content = $component->getProperty($prop, false, true))) {
                            if (isset($content['params']['VALUE']) && ('DATE' == $content['params']['VALUE'])) {
                                $type = 'date';
                                unset($content['value']['hour'], $content['value']['min'], $content['value']['sec']);
                            } else {
                                $type = 'date-time';
                                if (isset($utcDate) && !isset($content['value']['tz'])) {
                                    $content['value']['tz'] = 'Z';
                                }
                                if ((isset($content['value']['tz']) &&  // fix UTC-date if offset set
                                        iCalUtilityFunctions::_isOffset($content['value']['tz']) &&
                                        ('Z' != $content['value']['tz']))
                                    || (isset($content['params']['TZID']) &&
                                        iCalUtilityFunctions::_isOffset($content['params']['TZID']) &&
                                        ('Z' != $content['params']['TZID']))) {
                                    $offset = isset($content['value']['tz']) ? $content['value']['tz'] : $content['params']['TZID'];
                                    $date = mktime((int)$content['value']['hour'],
                                        (int)$content['value']['min'],
                                        (int)($content['value']['sec'] + iCalUtilityFunctions::_tz2offset($offset)),
                                        (int)$content['value']['month'],
                                        (int)$content['value']['day'],
                                        (int)$content['value']['year']);
                                    unset($content['value']['tz'], $content['params']['TZID']);
                                    $content['value'] = iCalUtilityFunctions::_date_time_string(date('YmdTHis\Z', $date), 6);
                                    unset($content['value']['unparsedtext']);
                                } elseif (isset($content['value']['tz']) && !empty($content['value']['tz']) &&
                                    ('Z' != $content['value']['tz']) && !isset($content['params']['TZID'])) {
                                    $content['params']['TZID'] = $content['value']['tz'];
                                    unset($content['value']['tz']);
                                }
                            }
                            unset($content['params']['VALUE']);
                            if ((isset($content['params']['TZID']) && empty($content['params']['TZID'])) || @is_null($content['params']['TZID'])) {
                                unset($content['params']['TZID']);
                            }
                            _addXMLchild($properties, $prop, $type, $content['value'], $content['params']);
                        }
                        unset($utcDate);
                        break;
                    case 'duration':
                        if (false !== ($content = $component->getProperty($prop, false, true))) {
                            _addXMLchild($properties, $prop, 'duration', $content['value'], $content['params']);
                        }
                        break;
                    case 'rrule':
                        while (false !== ($content = $component->getProperty($prop, false, true))) {
                            _addXMLchild($properties, $prop, 'recur', $content['value'], $content['params']);
                        }
                        break;
                    case 'class':
                    case 'location':
                    case 'status':
                    case 'summary':
                    case 'transp':
                    case 'tzid':
                    case 'uid':
                        if (false !== ($content = $component->getProperty($prop, false, true))) {
                            if ((('location' == $prop) || ('summary' == $prop)) && !isset($content['params']['LANGUAGE'])) {
                                if ($langComp) {
                                    $content['params']['LANGUAGE'] = $langComp;
                                } elseif ($langCal) {
                                    $content['params']['LANGUAGE'] = $langCal;
                                }
                            }
                            _addXMLchild($properties, $prop, 'text', $content['value'], $content['params']);
                        }
                        break;
                    case 'geo':
                        if (false !== ($content = $component->getProperty($prop, false, true))) {
                            _addXMLchild($properties, $prop, 'geo', $content['value'], $content['params']);
                        }
                        break;
                    case 'organizer':
                        if (false !== ($content = $component->getProperty($prop, false, true))) {
                            if (isset($content['params']['CN']) && !isset($content['params']['LANGUAGE'])) {
                                if ($langComp) {
                                    $content['params']['LANGUAGE'] = $langComp;
                                } elseif ($langCal) {
                                    $content['params']['LANGUAGE'] = $langCal;
                                }
                            }
                            _addXMLchild($properties, $prop, 'cal-address', $content['value'], $content['params']);
                        }
                        break;
                    case 'percent-complete':
                    case 'priority':
                    case 'sequence':
                        if (false !== ($content = $component->getProperty($prop, false, true))) {
                            _addXMLchild($properties, $prop, 'integer', $content['value'], $content['params']);
                        }
                        break;
                    case 'tzurl':
                    case 'url':
                        if (false !== ($content = $component->getProperty($prop, false, true))) {
                            _addXMLchild($properties, $prop, 'uri', $content['value'], $content['params']);
                        }
                        break;
                } // end switch( $prop )
            } // end foreach( $props as $prop )
            /** fix subComponent properties, if any */
            foreach ($subComps as $subCompName) {
                while (false !== ($subcomp = $component->getComponent($subCompName))) {
                    $child2 = $child->addChild($subCompName);
                    $properties = $child2->addChild('properties');
                    $langComp = $subcomp->getConfig('language');
                    foreach ($subCompProps as $prop) {
                        switch ($prop) {
                            case 'attach':          // may occur multiple times, below
                                while (false !== ($content = $subcomp->getProperty($prop, false, true))) {
                                    $type = (isset($content['params']['VALUE']) && ('BINARY' == $content['params']['VALUE'])) ? 'binary' : 'uri';
                                    unset($content['params']['VALUE']);
                                    _addXMLchild($properties, $prop, $type, $content['value'], $content['params']);
                                }
                                break;
                            case 'attendee':
                                while (false !== ($content = $subcomp->getProperty($prop, false, true))) {
                                    if (isset($content['params']['CN']) && !isset($content['params']['LANGUAGE'])) {
                                        if ($langComp) {
                                            $content['params']['LANGUAGE'] = $langComp;
                                        } elseif ($langCal) {
                                            $content['params']['LANGUAGE'] = $langCal;
                                        }
                                    }
                                    _addXMLchild($properties, $prop, 'cal-address', $content['value'], $content['params']);
                                }
                                break;
                            case 'comment':
                            case 'tzname':
                                while (false !== ($content = $subcomp->getProperty($prop, false, true))) {
                                    if (!isset($content['params']['LANGUAGE'])) {
                                        if ($langComp) {
                                            $content['params']['LANGUAGE'] = $langComp;
                                        } elseif ($langCal) {
                                            $content['params']['LANGUAGE'] = $langCal;
                                        }
                                    }
                                    _addXMLchild($properties, $prop, 'text', $content['value'], $content['params']);
                                }
                                break;
                            case 'rdate':
                                while (false !== ($content = $subcomp->getProperty($prop, false, true))) {
                                    $type = 'date-time';
                                    if (isset($content['params']['VALUE'])) {
                                        if ('DATE' == $content['params']['VALUE']) {
                                            $type = 'date';
                                        } elseif ('PERIOD' == $content['params']['VALUE']) {
                                            $type = 'period';
                                        }
                                    }
                                    if ('period' == $type) {
                                        foreach ($content['value'] as & $rDates) {
                                            if ((isset($rDates[0]['tz']) &&  // fix UTC-date if offset set
                                                    iCalUtilityFunctions::_isOffset($rDates[0]['tz']) &&
                                                    ('Z' != $rDates[0]['tz']))
                                                || (isset($content['params']['TZID']) &&
                                                    iCalUtilityFunctions::_isOffset($content['params']['TZID']) &&
                                                    ('Z' != $content['params']['TZID']))) {
                                                $offset = isset($rDates[0]['tz']) ? $rDates[0]['tz'] : $content['params']['TZID'];
                                                $date = mktime((int)$rDates[0]['hour'],
                                                    (int)$rDates[0]['min'],
                                                    (int)($rDates[0]['sec'] + iCalUtilityFunctions::_tz2offset($offset)),
                                                    (int)$rDates[0]['month'],
                                                    (int)$rDates[0]['day'],
                                                    (int)$rDates[0]['year']);
                                                unset($rDates[0]['tz']);
                                                $rDates[0] = iCalUtilityFunctions::_date_time_string(date('YmdTHis\Z', $date), 6);
                                                unset($rDates[0]['unparsedtext']);
                                            }
                                            if (isset($rDates[1]['year'])) {
                                                if ((isset($rDates[1]['tz']) &&  // fix UTC-date if offset set
                                                        iCalUtilityFunctions::_isOffset($rDates[1]['tz']) &&
                                                        ('Z' != $rDates[1]['tz']))
                                                    || (isset($content['params']['TZID']) &&
                                                        iCalUtilityFunctions::_isOffset($content['params']['TZID']) &&
                                                        ('Z' != $content['params']['TZID']))) {
                                                    $offset = isset($rDates[1]['tz']) ? $rDates[1]['tz'] : $content['params']['TZID'];
                                                    $date = mktime((int)$rDates[1]['hour'],
                                                        (int)$rDates[1]['min'],
                                                        (int)($rDates[1]['sec'] + iCalUtilityFunctions::_tz2offset($offset)),
                                                        (int)$rDates[1]['month'],
                                                        (int)$rDates[1]['day'],
                                                        (int)$rDates[1]['year']);
                                                    unset($rDates[1]['tz']);
                                                    $rDates[1] = iCalUtilityFunctions::_date_time_string(date('YmdTHis\Z', $date), 6);
                                                    unset($rDates[1]['unparsedtext']);
                                                }
                                            }
                                        }
                                    } elseif ('date-time' == $type) {
                                        foreach ($content['value'] as & $rDate) {
                                            if ((isset($rDate['tz']) &&  // fix UTC-date if offset set
                                                    iCalUtilityFunctions::_isOffset($rDate['tz']) &&
                                                    ('Z' != $rDate['tz']))
                                                || (isset($content['params']['TZID']) &&
                                                    iCalUtilityFunctions::_isOffset($content['params']['TZID']) &&
                                                    ('Z' != $content['params']['TZID']))) {
                                                $offset = isset($rDate['tz']) ? $rDate['tz'] : $content['params']['TZID'];
                                                $date = mktime((int)$rDate['hour'],
                                                    (int)$rDate['min'],
                                                    (int)($rDate['sec'] + iCalUtilityFunctions::_tz2offset($offset)),
                                                    (int)$rDate['month'],
                                                    (int)$rDate['day'],
                                                    (int)$rDate['year']);
                                                unset($rDate['tz']);
                                                $rDate = iCalUtilityFunctions::_date_time_string(date('YmdTHis\Z', $date), 6);
                                                unset($rDate['unparsedtext']);
                                            }
                                        }
                                    }
                                    unset($content['params']['VALUE']);
                                    _addXMLchild($properties, $prop, $type, $content['value'], $content['params']);
                                }
                                break;
                            case 'x-prop':
                                while (false !== ($content = $subcomp->getProperty($prop, false, true))) {
                                    _addXMLchild($properties, $content[0], 'unknown', $content[1]['value'], $content[1]['params']);
                                }
                                break;
                            case 'action':      // single occurence below, if set
                            case 'description':
                            case 'summary':
                                if (false !== ($content = $subcomp->getProperty($prop, false, true))) {
                                    if (('action' != $prop) && !isset($content['params']['LANGUAGE'])) {
                                        if ($langComp) {
                                            $content['params']['LANGUAGE'] = $langComp;
                                        } elseif ($langCal) {
                                            $content['params']['LANGUAGE'] = $langCal;
                                        }
                                    }
                                    _addXMLchild($properties, $prop, 'text', $content['value'], $content['params']);
                                }
                                break;
                            case 'dtstart':
                                if (false !== ($content = $subcomp->getProperty($prop, false, true))) {
                                    unset($content['value']['tz'], $content['params']['VALUE']); // always local time
                                    _addXMLchild($properties, $prop, 'date-time', $content['value'], $content['params']);
                                }
                                break;
                            case 'duration':
                                if (false !== ($content = $subcomp->getProperty($prop, false, true))) {
                                    _addXMLchild($properties, $prop, 'duration', $content['value'], $content['params']);
                                }
                                break;
                            case 'repeat':
                                if (false !== ($content = $subcomp->getProperty($prop, false, true))) {
                                    _addXMLchild($properties, $prop, 'integer', $content['value'], $content['params']);
                                }
                                break;
                            case 'trigger':
                                if (false !== ($content = $subcomp->getProperty($prop, false, true))) {
                                    if (isset($content['value']['year']) &&
                                        isset($content['value']['month']) &&
                                        isset($content['value']['day'])) {
                                        $type = 'date-time';
                                    } else {
                                        $type = 'duration';
                                    }
                                    _addXMLchild($properties, $prop, $type, $content['value'], $content['params']);
                                }
                                break;
                            case 'tzoffsetto':
                            case 'tzoffsetfrom':
                                if (false !== ($content = $subcomp->getProperty($prop, false, true))) {
                                    _addXMLchild($properties, $prop, 'utc-offset', $content['value'], $content['params']);
                                }
                                break;
                            case 'rrule':
                                while (false !== ($content = $subcomp->getProperty($prop, false, true))) {
                                    _addXMLchild($properties, $prop, 'recur', $content['value'], $content['params']);
                                }
                                break;
                        } // switch( $prop )
                    } // end foreach( $subCompProps as $prop )
                } // end while( FALSE !== ( $subcomp = $component->getComponent( subCompName )))
            } // end foreach( $subCombs as $subCompName )
        } // end while( FALSE !== ( $component = $calendar->getComponent( $compName )))
    } // end foreach( $comps as $compName)
    return $xml->asXML();
}

/**
 * Add children to a SimpleXMLelement
 *
 * @param object $parent ,  reference to a SimpleXMLelement node
 * @param string $name ,    new element node name
 * @param string $type ,    content type, subelement(-s) name
 * @param string $content , new subelement content
 * @param array $params ,  new element 'attributes'
 * @return void
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.11.1 - 2012-01-16
 */
function _addXMLchild(&$parent, $name, $type, $content, $params = [])
{
    /** create new child node */
    $child = $parent->addChild(strtolower($name));
    /** fix attributes */
    if (is_array($content) && isset($content['fbtype'])) {
        $params['FBTYPE'] = $content['fbtype'];
        unset($content['fbtype']);
    }
    if (isset($params['VALUE'])) {
        unset($params['VALUE']);
    }
    if (('trigger' == $name) && ('duration' == $type) && (true !== $content['relatedStart'])) {
        $params['RELATED'] = 'END';
    }
    if (!empty($params)) {
        $parameters = $child->addChild('parameters');
        foreach ($params as $param => $parVal) {
            $param = strtolower($param);
            if ('x-' == substr($param, 0, 2)) {
                $p1 = $parameters->addChild($param);
                $p2 = $p1->addChild('unknown', htmlspecialchars($parVal));
            } else {
                $p1 = $parameters->addChild($param);
                switch ($param) {
                    case 'altrep':
                    case 'dir':
                        $ptype = 'uri';
                        break;
                    case 'delegated-from':
                    case 'delegated-to':
                    case 'member':
                    case 'sent-by':
                        $ptype = 'cal-address';
                        break;
                    case 'rsvp':
                        $ptype = 'boolean';
                        break;
                    default:
                        $ptype = 'text';
                        break;
                }
                if (is_array($parVal)) {
                    foreach ($parVal as $pV) {
                        $p2 = $p1->addChild($ptype, htmlspecialchars($pV));
                    }
                } else {
                    $p2 = $p1->addChild($ptype, htmlspecialchars($parVal));
                }
            }
        }
    }
    if (empty($content) && ('0' != $content)) {
        return;
    }
    /** store content */
    switch ($type) {
        case 'binary':
            $v = $child->addChild($type, $content);
            break;
        case 'boolean':
            break;
        case 'cal-address':
            $v = $child->addChild($type, $content);
            break;
        case 'date':
            if (array_key_exists('year', $content)) {
                $content = [$content];
            }
            foreach ($content as $date) {
                $str = sprintf('%04d-%02d-%02d', $date['year'], $date['month'], $date['day']);
                $v = $child->addChild($type, $str);
            }
            break;
        case 'date-time':
            if (array_key_exists('year', $content)) {
                $content = [$content];
            }
            foreach ($content as $dt) {
                if (!isset($dt['hour'])) {
                    $dt['hour'] = 0;
                }
                if (!isset($dt['min'])) {
                    $dt['min'] = 0;
                }
                if (!isset($dt['sec'])) {
                    $dt['sec'] = 0;
                }
                $str = sprintf('%04d-%02d-%02dT%02d:%02d:%02d', $dt['year'], $dt['month'], $dt['day'], $dt['hour'], $dt['min'], $dt['sec']);
                if (isset($dt['tz']) && ('Z' == $dt['tz'])) {
                    $str .= 'Z';
                }
                $v = $child->addChild($type, $str);
            }
            break;
        case 'duration':
            $output = (('trigger' == $name) && (false !== $content['before'])) ? '-' : '';
            $v = $child->addChild($type, $output . iCalUtilityFunctions::_format_duration($content));
            break;
        case 'geo':
            $v1 = $child->addChild('latitude', number_format((float)$content['latitude'], 6, '.', ''));
            $v1 = $child->addChild('longitude', number_format((float)$content['longitude'], 6, '.', ''));
            break;
        case 'integer':
            $v = $child->addChild($type, $content);
            break;
        case 'period':
            if (!is_array($content)) {
                break;
            }
            foreach ($content as $period) {
                $v1 = $child->addChild($type);
                $str = sprintf('%04d-%02d-%02dT%02d:%02d:%02d', $period[0]['year'], $period[0]['month'], $period[0]['day'], $period[0]['hour'], $period[0]['min'], $period[0]['sec']);
                if (isset($period[0]['tz']) && ('Z' == $period[0]['tz'])) {
                    $str .= 'Z';
                }
                $v2 = $v1->addChild('start', $str);
                if (array_key_exists('year', $period[1])) {
                    $str = sprintf('%04d-%02d-%02dT%02d:%02d:%02d', $period[1]['year'], $period[1]['month'], $period[1]['day'], $period[1]['hour'], $period[1]['min'], $period[1]['sec']);
                    if (isset($period[1]['tz']) && ('Z' == $period[1]['tz'])) {
                        $str .= 'Z';
                    }
                    $v2 = $v1->addChild('end', $str);
                } else {
                    $v2 = $v1->addChild('duration', iCalUtilityFunctions::_format_duration($period[1]));
                }
            }
            break;
        case 'recur':
            foreach ($content as $rulelabel => $rulevalue) {
                $rulelabel = strtolower($rulelabel);
                switch ($rulelabel) {
                    case 'until':
                        if (isset($rulevalue['hour'])) {
                            $str = sprintf('%04d-%02d-%02dT%02d:%02d:%02dZ', $rulevalue['year'], $rulevalue['month'], $rulevalue['day'], $rulevalue['hour'], $rulevalue['min'], $rulevalue['sec']);
                        } else {
                            $str = sprintf('%04d-%02d-%02d', $rulevalue['year'], $rulevalue['month'], $rulevalue['day']);
                        }
                        $v = $child->addChild($rulelabel, $str);
                        break;
                    case 'bysecond':
                    case 'byminute':
                    case 'byhour':
                    case 'bymonthday':
                    case 'byyearday':
                    case 'byweekno':
                    case 'bymonth':
                    case 'bysetpos':
                    {
                        if (is_array($rulevalue)) {
                            foreach ($rulevalue as $vix => $valuePart) {
                                $v = $child->addChild($rulelabel, $valuePart);
                            }
                        } else {
                            $v = $child->addChild($rulelabel, $rulevalue);
                        }
                        break;
                    }
                    case 'byday':
                    {
                        if (isset($rulevalue['DAY'])) {
                            $str = (isset($rulevalue[0])) ? $rulevalue[0] : '';
                            $str .= $rulevalue['DAY'];
                            $p = $child->addChild($rulelabel, $str);
                        } else {
                            foreach ($rulevalue as $valuePart) {
                                if (isset($valuePart['DAY'])) {
                                    $str = (isset($valuePart[0])) ? $valuePart[0] : '';
                                    $str .= $valuePart['DAY'];
                                    $p = $child->addChild($rulelabel, $str);
                                } else {
                                    $p = $child->addChild($rulelabel, $valuePart);
                                }
                            }
                        }
                        break;
                    }
                    case 'freq':
                    case 'count':
                    case 'interval':
                    case 'wkst':
                    default:
                        $p = $child->addChild($rulelabel, $rulevalue);
                        break;
                } // end switch( $rulelabel )
            } // end foreach( $content as $rulelabel => $rulevalue )
            break;
        case 'rstatus':
            $v = $child->addChild('code', number_format((float)$content['statcode'], 2, '.', ''));
            $v = $child->addChild('description', htmlspecialchars($content['text']));
            if (isset($content['extdata'])) {
                $v = $child->addChild('data', htmlspecialchars($content['extdata']));
            }
            break;
        case 'text':
            if (!is_array($content)) {
                $content = [$content];
            }
            foreach ($content as $part) {
                $v = $child->addChild($type, htmlspecialchars($part));
            }
            break;
        case 'time':
            break;
        case 'uri':
            $v = $child->addChild($type, $content);
            break;
        case 'utc-offset':
            if (in_array(substr($content, 0, 1), ['-', '+'])) {
                $str = substr($content, 0, 1);
                $content = substr($content, 1);
            } else {
                $str = '+';
            }
            $str .= substr($content, 0, 2) . ':' . substr($content, 2, 2);
            if (4 < strlen($content)) {
                $str .= ':' . substr($content, 4);
            }
            $v = $child->addChild($type, $str);
            break;
        case 'unknown':
        default:
            if (is_array($content)) {
                $content = implode('', $content);
            }
            $v = $child->addChild('unknown', htmlspecialchars($content));
            break;
    }
}

/**
 * parse xml string into iCalcreator instance
 *
 * @param string $xmlstr
 * @param array $iCalcfg iCalcreator config array (opt)
 * @return mixed  iCalcreator instance or FALSE on error
 * @since 2.11.2 - 2012-01-31
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 */
function & XMLstr2iCal($xmlstr, $iCalcfg = [])
{
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xmlstr);
    if (!$xml) {
        $str = '';
        $return = false;
        foreach (libxml_get_errors() as $error) {
            switch ($error->level) {
                case LIBXML_ERR_FATAL:
                    $str .= ' FATAL ';
                    break;
                case LIBXML_ERR_ERROR:
                    $str .= ' ERROR ';
                    break;
                case LIBXML_ERR_WARNING:
                default:
                    $str .= ' WARNING ';
                    break;
            }
            $str .= PHP_EOL . 'Error when loading XML';
            if (!empty($error->file)) {
                $str .= ',  file:' . $error->file . ', ';
            }
            $str .= ', line:' . $error->line;
            $str .= ', (' . $error->code . ') ' . $error->message;
        }
        error_log($str);
        if (LIBXML_ERR_WARNING != $error->level) {
            return $return;
        }
        libxml_clear_errors();
    }
    return xml2iCal($xml, $iCalcfg);
}

/**
 * parse xml file into iCalcreator instance
 *
 * @param string $xmlfile
 * @param array $iCalcfg iCalcreator config array (opt)
 * @return mixediCalcreator instance or FALSE on error
 * @since  2.11.2 - 2012-01-20
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 */
function & XMLfile2iCal($xmlfile, $iCalcfg = [])
{
    libxml_use_internal_errors(true);
    $xml = simplexml_load_file($xmlfile);
    if (!$xml) {
        $str = '';
        foreach (libxml_get_errors() as $error) {
            switch ($error->level) {
                case LIBXML_ERR_FATAL:
                    $str .= 'FATAL ';
                    break;
                case LIBXML_ERR_ERROR:
                    $str .= 'ERROR ';
                    break;
                case LIBXML_ERR_WARNING:
                default:
                    $str .= 'WARNING ';
                    break;
            }
            $str .= 'Failed loading XML' . PHP_EOL;
            if (!empty($error->file)) {
                $str .= ' file:' . $error->file . ', ';
            }
            $str .= 'line:' . $error->line . PHP_EOL;
            $str .= '(' . $error->code . ') ' . $error->message . PHP_EOL;
        }
        error_log($str);
        if (LIBXML_ERR_WARNING != $error->level) {
            return false;
        }
        libxml_clear_errors();
    }
    return xml2iCal($xml, $iCalcfg);
}

/**
 * parse SimpleXMLElement xCal into iCalcreator instance
 *
 * @param object $xmlobj SimpleXMLElement
 * @param array $iCalcfg iCalcreator config array (opt)
 * @return mixed  iCalcreator instance or FALSE on error
 * @since  2.11.2 - 2012-01-27
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 */
function & XML2iCal($xmlobj, $iCalcfg = [])
{
    $iCal = new vcalendar($iCalcfg);
    foreach ($xmlobj->children() as $icalendar) { // vcalendar
        foreach ($icalendar->children() as $calPart) { // calendar properties and components
            if ('components' == $calPart->getName()) {
                foreach ($calPart->children() as $component) { // single components
                    if (0 < $component->count()) {
                        _getXMLComponents($iCal, $component);
                    }
                }
            } elseif (('properties' == $calPart->getName()) && (0 < $calPart->count())) {
                foreach ($calPart->children() as $calProp) { // calendar properties
                    $propName = $calProp->getName();
                    if (('calscale' != $propName) && ('method' != $propName) && ('x-' != substr($propName, 0, 2))) {
                        continue;
                    }
                    $params = [];
                    foreach ($calProp->children() as $calPropElem) { // single calendar property
                        if ('parameters' == $calPropElem->getName()) {
                            $params = _getXMLParams($calPropElem);
                        } else {
                            $iCal->setProperty($propName, reset($calPropElem), $params);
                        }
                    } // end foreach( $calProp->children() as $calPropElem )
                } // end foreach( $calPart->properties->children() as $calProp )
            } // end if( 0 < $calPart->properties->count())
        } // end foreach( $icalendar->children() as $calPart )
    } // end foreach( $xmlobj->children() as $icalendar )
    return $iCal;
}

/**
 * parse SimpleXMLElement xCal property parameters and return iCalcreator property parameter array
 *
 * @param object $parameters SimpleXMLElement
 * @return array  iCalcreator property parameter array
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.11.2 - 2012-01-15
 */
function _getXMLParams(&$parameters)
{
    if (1 > $parameters->count()) {
        return [];
    }
    $params = [];
    foreach ($parameters->children() as $parameter) { // single parameter key
        $key = strtoupper($parameter->getName());
        $value = [];
        foreach ($parameter->children() as $paramValue) // skip parameter value type
        {
            $value[] = reset($paramValue);
        }
        if (2 > count($value)) {
            $params[$key] = html_entity_decode(reset($value));
        } else {
            $params[$key] = $value;
        }
    }
    return $params;
}

/**
 * parse SimpleXMLElement xCal components, create iCalcreator component and update
 *
 * @param array $iCal iCalcreator calendar instance
 * @param object $component SimpleXMLElement
 * @return void
 * @since  2.11.2 - 2012-01-15
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 */
function _getXMLComponents(&$iCal, &$component)
{
    $compName = $component->getName();
    $comp = &$iCal->newComponent($compName);
    $subComponents = ['valarm', 'standard', 'daylight'];
    foreach ($component->children() as $compPart) { // properties and (opt) subComponents
        if (1 > $compPart->count()) {
            continue;
        }
        if (in_array($compPart->getName(), $subComponents)) {
            _getXMLComponents($comp, $compPart);
        } elseif ('properties' == $compPart->getName()) {
            foreach ($compPart->children() as $property) // properties as single property
            {
                _getXMLProperties($comp, $property);
            }
        }
    } // end foreach( $component->children() as $compPart )
}

/**
 * parse SimpleXMLElement xCal property, create iCalcreator component property
 *
 * @param array $iCal iCalcreator calendar instance
 * @param object $component SimpleXMLElement
 * @return void
 * @since  2.11.2 - 2012-01-27
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 */
function _getXMLProperties(&$iCal, &$property)
{
    $propName = $property->getName();
    $value = $params = [];
    $valueType = '';
    foreach ($property->children() as $propPart) { // calendar property parameters (opt) and value(-s)
        $valueType = $propPart->getName();
        if ('parameters' == $valueType) {
            $params = _getXMLParams($propPart);
            continue;
        }
        switch ($valueType) {
            case 'binary':
                $value = reset($propPart);
                break;
            case 'boolean':
                break;
            case 'cal-address':
                $value = reset($propPart);
                break;
            case 'date':
                $params['VALUE'] = 'DATE';
            case 'date-time':
                if (('exdate' == $propName) || ('rdate' == $propName)) {
                    $value[] = reset($propPart);
                } else {
                    $value = reset($propPart);
                }
                break;
            case 'duration':
                $value = reset($propPart);
                break;
            //        case 'geo':
            case 'latitude':
            case 'longitude':
                $value[$valueType] = reset($propPart);
                break;
            case 'integer':
                $value = reset($propPart);
                break;
            case 'period':
                if ('rdate' == $propName) {
                    $params['VALUE'] = 'PERIOD';
                }
                $pData = [];
                foreach ($propPart->children() as $periodPart) {
                    $pData[] = reset($periodPart);
                }
                if (!empty($pData)) {
                    $value[] = $pData;
                }
                break;
            //        case 'rrule':
            case 'freq':
            case 'count':
            case 'until':
            case 'interval':
            case 'wkst':
                $value[$valueType] = reset($propPart);
                break;
            case 'bysecond':
            case 'byminute':
            case 'byhour':
            case 'bymonthday':
            case 'byyearday':
            case 'byweekno':
            case 'bymonth':
            case 'bysetpos':
                $value[$valueType][] = reset($propPart);
                break;
            case 'byday':
                $byday = reset($propPart);
                if (2 == strlen($byday)) {
                    $value[$valueType][] = ['DAY' => $byday];
                } else {
                    $day = substr($byday, -2);
                    $key = substr($byday, 0, (strlen($byday) - 2));
                    $value[$valueType][] = [$key, 'DAY' => $day];
                }
                break;
            //      case 'rstatus':
            case 'code':
                $value[0] = reset($propPart);
                break;
            case 'description':
                $value[1] = reset($propPart);
                break;
            case 'data':
                $value[2] = reset($propPart);
                break;
            case 'text':
                $text = str_replace(["\r\n", "\n\r", "\r", "\n"], '\n', reset($propPart));
                $value['text'][] = html_entity_decode($text);
                break;
            case 'time':
                break;
            case 'uri':
                $value = reset($propPart);
                break;
            case 'utc-offset':
                $value = str_replace(':', '', reset($propPart));
                break;
            case 'unknown':
            default:
                $value = html_entity_decode(reset($propPart));
                break;
        } // end switch( $valueType )
    } // end  foreach( $property->children() as $propPart )
    if ('freebusy' == $propName) {
        $fbtype = $params['FBTYPE'];
        unset($params['FBTYPE']);
        $iCal->setProperty($propName, $fbtype, $value, $params);
    } elseif ('geo' == $propName) {
        $iCal->setProperty($propName, $value['latitude'], $value['longitude'], $params);
    } elseif ('request-status' == $propName) {
        if (!isset($value[2])) {
            $value[2] = false;
        }
        $iCal->setProperty($propName, $value[0], $value[1], $value[2], $params);
    } else {
        if (isset($value['text']) && is_array($value['text'])) {
            if (('categories' == $propName) || ('resources' == $propName)) {
                $value = $value['text'];
            } else {
                $value = reset($value['text']);
            }
        }
        $iCal->setProperty($propName, $value, $params);
    }
}

/**
 * Additional functions to use with vtimezone components
 * For use with
 * iCalcreator (kigkonsult.se/iCalcreator/index.php)
 * copyright (c) 2011 Yitzchok Lavi
 * icalcreator@onebigsystem.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
/**
 * Additional functions to use with vtimezone components
 *
 * Before calling the functions, set time zone 'GMT' ('date_default_timezone_set')!
 *
 * @author Yitzchok Lavi <icalcreator@onebigsystem.com>
 *         adjusted for iCalcreator Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @version 1.0.2 - 2011-02-24
 *
 */
/**
 * Returns array with the offset information from UTC for a (UTC) datetime/timestamp in the
 * timezone, according to the VTIMEZONE information in the input array.
 *
 * $param array  $timezonesarray, output from function getTimezonesAsDateArrays (below)
 * $param string $tzid,           time zone identifier
 * $param mixed  $timestamp,      timestamp or a UTC datetime (in array format)
 * @return array, time zone data with keys for 'offsetHis', 'offsetSec' and 'tzname'
 *
 */
function getTzOffsetForDate($timezonesarray, $tzid, $timestamp)
{
    if (is_array($timestamp)) {
        //$disp = sprintf( '%04d%02d%02d %02d%02d%02d', $timestamp['year'], $timestamp['month'], $timestamp['day'], $timestamp['hour'], $timestamp['min'], $timestamp['sec'] );
        $timestamp = gmmktime(
            $timestamp['hour'],
            $timestamp['min'],
            $timestamp['sec'],
            $timestamp['month'],
            $timestamp['day'],
            $timestamp['year']
        );
        //echo '<td colspan="4">&nbsp;'."\n".'<tr><td>&nbsp;<td class="r">'.$timestamp.'<td class="r">'.$disp.'<td colspan="4">&nbsp;'."\n".'<tr><td colspan="3">&nbsp;'; // test ###
    }
    $tzoffset = [];
    // something to return if all goes wrong (such as if $tzid doesn't find us an array of dates)
    $tzoffset['offsetHis'] = '+0000';
    $tzoffset['offsetSec'] = 0;
    $tzoffset['tzname'] = '?';
    if (!isset($timezonesarray[$tzid])) {
        return $tzoffset;
    }
    $tzdatearray = $timezonesarray[$tzid];
    if (is_array($tzdatearray)) {
        sort($tzdatearray); // just in case
        if ($timestamp < $tzdatearray[0]['timestamp']) {
            // our date is before the first change
            $tzoffset['offsetHis'] = $tzdatearray[0]['tzbefore']['offsetHis'];
            $tzoffset['offsetSec'] = $tzdatearray[0]['tzbefore']['offsetSec'];
            $tzoffset['tzname'] = $tzdatearray[0]['tzbefore']['offsetHis']; // we don't know the tzname in this case
        } elseif ($timestamp >= $tzdatearray[count($tzdatearray) - 1]['timestamp']) {
            // our date is after the last change (we do this so our scan can stop at the last record but one)
            $tzoffset['offsetHis'] = $tzdatearray[count($tzdatearray) - 1]['tzafter']['offsetHis'];
            $tzoffset['offsetSec'] = $tzdatearray[count($tzdatearray) - 1]['tzafter']['offsetSec'];
            $tzoffset['tzname'] = $tzdatearray[count($tzdatearray) - 1]['tzafter']['tzname'];
        } else {
            // our date somewhere in between
            // loop through the list of dates and stop at the one where the timestamp is before our date and the next one is after it
            // we don't include the last date in our loop as there isn't one after it to check
            for ($i = 0; $i <= count($tzdatearray) - 2; $i++) {
                if (($timestamp >= $tzdatearray[$i]['timestamp']) && ($timestamp < $tzdatearray[$i + 1]['timestamp'])) {
                    $tzoffset['offsetHis'] = $tzdatearray[$i]['tzafter']['offsetHis'];
                    $tzoffset['offsetSec'] = $tzdatearray[$i]['tzafter']['offsetSec'];
                    $tzoffset['tzname'] = $tzdatearray[$i]['tzafter']['tzname'];
                    break;
                }
            }
        }
    }
    return $tzoffset;
}

/**
 * Returns an array containing all the timezone data in the vcalendar object
 *
 * @param object $vcalendar , iCalcreator calendar instance
 * @return array, time zone transition timestamp, array before(offsetHis, offsetSec), array after(offsetHis, offsetSec, tzname)
 *                based on the timezone data in the vcalendar object
 *
 */
function getTimezonesAsDateArrays($vcalendar)
{
    $timezonedata = [];
    while ($vtz = $vcalendar->getComponent('vtimezone')) {
        $tzid = $vtz->getProperty('tzid');
        $alltzdates = [];
        while ($vtzc = $vtz->getComponent('standard')) {
            $newtzdates = expandTimezoneDates($vtzc);
            $alltzdates = array_merge($alltzdates, $newtzdates);
        }
        while ($vtzc = $vtz->getComponent('daylight')) {
            $newtzdates = expandTimezoneDates($vtzc);
            $alltzdates = array_merge($alltzdates, $newtzdates);
        }
        sort($alltzdates);
        $timezonedata[$tzid] = $alltzdates;
    }
    return $timezonedata;
}

/**
 * Returns an array containing time zone data from vtimezone standard/daylight instances
 *
 * @param object $vtzc , an iCalcreator calendar standard/daylight instance
 * @return array, time zone data; array before(offsetHis, offsetSec), array after(offsetHis, offsetSec, tzname)
 *
 */
function expandTimezoneDates($vtzc)
{
    $tzdates = [];
    // prepare time zone "description" to attach to each change
    $tzbefore = [];
    $tzbefore['offsetHis'] = $vtzc->getProperty('tzoffsetfrom');
    $tzbefore['offsetSec'] = iCalUtilityFunctions::_tz2offset($tzbefore['offsetHis']);
    if (('-' != substr((string)$tzbefore['offsetSec'], 0, 1)) && ('+' != substr((string)$tzbefore['offsetSec'], 0, 1))) {
        $tzbefore['offsetSec'] = '+' . $tzbefore['offsetSec'];
    }
    $tzafter = [];
    $tzafter['offsetHis'] = $vtzc->getProperty('tzoffsetto');
    $tzafter['offsetSec'] = iCalUtilityFunctions::_tz2offset($tzafter['offsetHis']);
    if (('-' != substr((string)$tzafter['offsetSec'], 0, 1)) && ('+' != substr((string)$tzafter['offsetSec'], 0, 1))) {
        $tzafter['offsetSec'] = '+' . $tzafter['offsetSec'];
    }
    if (false === ($tzafter['tzname'] = $vtzc->getProperty('tzname'))) {
        $tzafter['tzname'] = $tzafter['offsetHis'];
    }
    // find out where to start from
    $dtstart = $vtzc->getProperty('dtstart');
    $dtstarttimestamp = mktime(
        $dtstart['hour'],
        $dtstart['min'],
        $dtstart['sec'],
        $dtstart['month'],
        $dtstart['day'],
        $dtstart['year']
    );
    if (!isset($dtstart['unparsedtext'])) // ??
    {
        $dtstart['unparsedtext'] = sprintf('%04d%02d%02dT%02d%02d%02d', $dtstart['year'], $dtstart['month'], $dtstart['day'], $dtstart['hour'], $dtstart['min'], $dtstart['sec']);
    }
    if ($dtstarttimestamp == 0) {
        // it seems that the dtstart string may not have parsed correctly
        // let's set a timestamp starting from 1902, using the time part of the original string
        // so that the time will change at the right time of day
        // at worst we'll get midnight again
        $origdtstartsplit = explode('T', $dtstart['unparsedtext']);
        $dtstarttimestamp = strtotime("19020101", 0);
        $dtstarttimestamp = strtotime($origdtstartsplit[1], $dtstarttimestamp);
    }
    // the date (in dtstart and opt RDATE/RRULE) is ALWAYS LOCAL (not utc!!), adjust from 'utc' to 'local' timestamp
    $diff = -1 * $tzbefore['offsetSec'];
    $dtstarttimestamp += $diff;
    // add this (start) change to the array of changes
    $tzdates[] = [
        'timestamp' => $dtstarttimestamp,
        'tzbefore'  => $tzbefore,
        'tzafter'   => $tzafter,
    ];
    $datearray = getdate($dtstarttimestamp);
    // save original array to use time parts, because strtotime (used below) apparently loses the time
    $changetime = $datearray;
    // generate dates according to an RRULE line
    $rrule = $vtzc->getProperty('rrule');
    if (is_array($rrule)) {
        if ($rrule['FREQ'] == 'YEARLY') {
            // calculate transition dates starting from DTSTART
            $offsetchangetimestamp = $dtstarttimestamp;
            // calculate transition dates until 10 years in the future
            $stoptimestamp = strtotime("+10 year", time());
            // if UNTIL is set, calculate until then (however far ahead)
            if (isset($rrule['UNTIL']) && ($rrule['UNTIL'] != '')) {
                $stoptimestamp = mktime(
                    $rrule['UNTIL']['hour'],
                    $rrule['UNTIL']['min'],
                    $rrule['UNTIL']['sec'],
                    $rrule['UNTIL']['month'],
                    $rrule['UNTIL']['day'],
                    $rrule['UNTIL']['year']
                );
            }
            $count = 0;
            $stopcount = isset($rrule['COUNT']) ? $rrule['COUNT'] : 0;
            $daynames = [
                'SU' => 'Sunday',
                'MO' => 'Monday',
                'TU' => 'Tuesday',
                'WE' => 'Wednesday',
                'TH' => 'Thursday',
                'FR' => 'Friday',
                'SA' => 'Saturday',
            ];
            // repeat so long as we're between DTSTART and UNTIL, or we haven't prepared COUNT dates
            while ($offsetchangetimestamp < $stoptimestamp && ($stopcount == 0 || $count < $stopcount)) {
                // break up the timestamp into its parts
                $datearray = getdate($offsetchangetimestamp);
                if (isset($rrule['BYMONTH']) && ($rrule['BYMONTH'] != 0)) {
                    // set the month
                    $datearray['mon'] = $rrule['BYMONTH'];
                }
                if (isset($rrule['BYMONTHDAY']) && ($rrule['BYMONTHDAY'] != 0)) {
                    // set specific day of month
                    $datearray['mday'] = $rrule['BYMONTHDAY'];
                } elseif (is_array($rrule['BYDAY'])) {
                    // find the Xth WKDAY in the month
                    // the starting point for this process is the first of the month set above
                    $datearray['mday'] = 1;
                    // turn $datearray as it is now back into a timestamp
                    $offsetchangetimestamp = mktime(
                        $datearray['hours'],
                        $datearray['minutes'],
                        $datearray['seconds'],
                        $datearray['mon'],
                        $datearray['mday'],
                        $datearray['year']
                    );
                    if ($rrule['BYDAY'][0] > 0) {
                        // to find Xth WKDAY in month, we find last WKDAY in month before
                        // we do that by finding first WKDAY in this month and going back one week
                        // then we add X weeks (below)
                        $offsetchangetimestamp = strtotime($daynames[$rrule['BYDAY']['DAY']], $offsetchangetimestamp);
                        $offsetchangetimestamp = strtotime("-1 week", $offsetchangetimestamp);
                    } else {
                        // to find Xth WKDAY before the end of the month, we find the first WKDAY in the following month
                        // we do that by going forward one month and going to WKDAY there
                        // then we subtract X weeks (below)
                        $offsetchangetimestamp = strtotime("+1 month", $offsetchangetimestamp);
                        $offsetchangetimestamp = strtotime($daynames[$rrule['BYDAY']['DAY']], $offsetchangetimestamp);
                    }
                    // now move forward or back the appropriate number of weeks, into the month we want
                    $offsetchangetimestamp = strtotime($rrule['BYDAY'][0] . " week", $offsetchangetimestamp);
                    $datearray = getdate($offsetchangetimestamp);
                }
                // convert the date parts back into a timestamp, setting the time parts according to the
                // original time data which we stored
                $offsetchangetimestamp = mktime(
                    $changetime['hours'],
                    $changetime['minutes'],
                    $changetime['seconds'] + $diff,
                    $datearray['mon'],
                    $datearray['mday'],
                    $datearray['year']
                );
                // add this change to the array of changes
                $tzdates[] = [
                    'timestamp' => $offsetchangetimestamp,
                    'tzbefore'  => $tzbefore,
                    'tzafter'   => $tzafter,
                ];
                // update counters (timestamp and count)
                $offsetchangetimestamp = strtotime("+" . ((isset($rrule['INTERVAL']) && ($rrule['INTERVAL'] != 0)) ? $rrule['INTERVAL'] : 1) . " year", $offsetchangetimestamp);
                $count += 1;
            }
        }
    }
    // generate dates according to RDATE lines
    while ($rdates = $vtzc->getProperty('rdate')) {
        if (is_array($rdates)) {

            foreach ($rdates as $rdate) {
                // convert the explicit change date to a timestamp
                $offsetchangetimestamp = mktime(
                    $rdate['hour'],
                    $rdate['min'],
                    $rdate['sec'] + $diff,
                    $rdate['month'],
                    $rdate['day'],
                    $rdate['year']
                );
                // add this change to the array of changes
                $tzdates[] = [
                    'timestamp' => $offsetchangetimestamp,
                    'tzbefore'  => $tzbefore,
                    'tzafter'   => $tzafter,
                ];
            }
        }
    }
    return $tzdates;
}

?>
