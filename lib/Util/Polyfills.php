<?php

use MailPoetVendor\Symfony\Polyfill\Mbstring\Mbstring as MbstringPolyfill;

if (!defined('MB_CASE_UPPER')) {
  define('MB_CASE_UPPER', 0);
}
if (!defined('MB_CASE_LOWER')) {
  define('MB_CASE_LOWER', 1);
}
if (!defined('MB_CASE_TITLE')) {
  define('MB_CASE_TITLE', 2);
}

if (!function_exists('mb_detect_encoding')) {
  function mb_detect_encoding($str, $encodingList = null, $strict = false) {
    return MbstringPolyfill::mb_detect_encoding($str, $encodingList, $strict);
  }
}

if (!function_exists('mb_convert_encoding')) {
  function mb_convert_encoding($s, $to, $from = null) {
    return MbstringPolyfill::mb_convert_encoding($s, $to, $from);
  }
}

if (!function_exists('mb_strtoupper')) {
  function mb_strtoupper($s, $encoding = 'UTF-8') {
    return MbstringPolyfill::mb_strtoupper($s, $encoding);
  }
}

if (!function_exists('mb_detect_order')) {
  function mb_detect_order($encodingList = null) {
    return MbstringPolyfill::mb_detect_order($encodingList);
  }
}
