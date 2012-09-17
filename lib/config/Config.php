<?php

/**
 * Copyright (c) 2012 Matthias Kerstner <matthias@kerstner.at>
 *
 * This file is part of phpMailingList.
 * @link http://www.kerstner.at/phpmailinglist
 *
 * @filesource Config.php
 * @author Matthias Kerstner <matthias@kerstner.at>
 */
require_once 'lib/config/ConfigurationException.php';

/**
 * Config
 *
 * @author Matthias Kerstner <matthias@kerstner.at>
 */
abstract class Config {

    /**
     * @var <array> configuration
     */
    private static $config_ = null;

    /**
     * @var <array> translations
     */
    private static $translation_ = null;

    /**
     * Sets PHP's level for displaying errors based on current AVW_STAGE.
     */
    private static function setDisplayErrors() {
        ini_set('display_errors', (int) self::get('debug'));
    }

    /**
     * sets the application's timezone which will be used by any of PHP's date()
     * functions.
     */
    private static function setTimezone() {
        if (!date_default_timezone_set(self::get('timezone'))) {
            throw new ConfigurationException('Failed to set timezone.');
        }
    }

    /**
     * sets the internal encoding.
     */
    private static function setEncoding() {
        mb_language('Neutral');
        mb_regex_encoding('UTF-8');
        mb_internal_encoding('UTF-8');
    }

    /**
     * sets translation labels 
     */
    private static function setLocale() {
        $_locale = isset($_GET['locale']) ? $_GET['locale'] : self::get('default_locale');
        $localeFileBasePath = PHPMAILINGLIST_BASEPATH . PHPMAILINGLIST_DIRSEPERATOR
                . 'locale' . PHPMAILINGLIST_DIRSEPERATOR;
        $localeFilePath = realpath($localeFileBasePath
                . basename(mb_strtolower($_locale)));

        if (!$localeFilePath) {
            $defaultLocale = realpath($localeFileBasePath
                    . basename(mb_strtolower(Config::get('default_locale'))));
            if ($defaultLocale) {
                $localeFilePath = $defaultLocale;
            }
        }

        if (!file_exists($localeFilePath)) {
            throw new ConfigurationException('No translation file exists.');
        }

        if ($localeFilePath !== null) {
            self::$translation_ = parse_ini_file($localeFilePath);
        } else {
            throw new ConfigurationException('Invalid translation file specified.');
        }

        if (self::$translation_ === false) {
            throw new ConfigurationException('Failed to load translation file.');
        }
    }

    /**
     * Initializes class by loading settings from configuration file.
     * @param <string?> $configIniFilePath
     */
    public static function init($configIniFilePath = null) {

        self::setEncoding();

        if ($configIniFilePath === null) {
            throw new ConfigurationException('No configuration file specified.');
        }
        if (!file_exists($configIniFilePath)) {
            throw new ConfigurationException('No config file exists.');
        }

        if ($configIniFilePath !== null) {// load settings from INI file
            self::$config_ = parse_ini_file($configIniFilePath);
        } else {
            throw new ConfigurationException('Invalid config file specified.');
        }

        if (self::$config_ === false) {
            throw new ConfigurationException('Failed to load configuration file.');
        }

        // finally set configuration that depends on settings in INI file
        self::setTimezone();
        self::setDisplayErrors();
        self::setLocale();
    }

    /**
     * Returns configuration label specified if it exists, throws Exception
     * otherwise.
     * @param <string> $configLabel
     * @return <mixed>
     */
    public static function get($configLabel) {
        if (self::$config_ === null) {
            throw new ConfigurationException('Call init() before.');
        }

        if (array_key_exists($configLabel, self::$config_)) {
            return self::$config_[$configLabel];
        }

        throw new ConfigurationException('No such configuration label ' .
                $configLabel);
    }

    /**
     *
     * @param string $translationLabel
     * @return string
     * @throws ConfigurationException 
     */
    public static function __($translationLabel) {
        if (self::$translation_ === null) {
            throw new ConfigurationException('Call setLocale() before.');
        }

        if (array_key_exists($translationLabel, self::$translation_)) {
            return self::$translation_[$translationLabel];
        }

        return $translationLabel; //just return label
    }

}

?>