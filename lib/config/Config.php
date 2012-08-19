<?php

/**
 * Copyright (c) 2010 Matthias Kerstner <matthias@kerstner.at>
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
     * @var <array> configuration loaded from INI file
     */
    private static $config_ = null;

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
        if (!date_default_timezone_set(self::get('timezone')))
            throw new ConfigurationException('Failed to set timezone.');
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
     * Initializes class by loading settings from configuration file.
     * @param <string?> $configIniFilePath
     */
    public static function init($configIniFilePath=null) {

        self::setEncoding();

        if ($configIniFilePath === null)
            throw new ConfigurationException('No configuration file specified.');
        if (!file_exists($configIniFilePath))
            throw new ConfigurationException('No config file exists.');

        if ($configIniFilePath !== null) // load settings from INI file
            self::$config_ = parse_ini_file($configIniFilePath);
        else
            throw new ConfigurationException('Invalid config file specified.');

        if (self::$config_ === false)
            throw new ConfigurationException('Failed to load configuration file.');

        // finally set configuration that depends on settings in INI file
        self::setTimezone();
        self::setDisplayErrors();
    }

    /**
     * Returns configuration label specified if it exists, throws Exception
     * otherwise.
     * @param <string> $configLabel
     * @return <mixed>
     */
    public static function get($configLabel) {
        if (self::$config_ === null)
            throw new ConfigurationException('Call init() before.');

        if (array_key_exists($configLabel, self::$config_))
            return self::$config_[$configLabel];

        throw new ConfigurationException('No such configuration label ' .
                $configLabel);
    }

}

?>