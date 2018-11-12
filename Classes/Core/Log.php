<?php

namespace Stem\Core;

use Monolog\Logger;
use Monolog\Handler\SlackHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\BrowserConsoleHandler;

/**
 * Class View.
 *
 * Base class for rendering views
 */
final class Log
{
    private static $logger = null;

    public static function initialize($config = null)
    {
        self::$logger = new Logger('stem');

        if ($config == null) {
            self::$logger->pushHandler(new ErrorLogHandler());

            return;
        }

        foreach ($config as $level => $handlers) {
            switch ($level) {
                case 'info': $logLevel = Logger::INFO; break;
                case 'notice': $logLevel = Logger::NOTICE; break;
                case 'warning': $logLevel = Logger::WARNING; break;
                case 'error': $logLevel = Logger::ERROR; break;
                case 'critical': $logLevel = Logger::CRITICAL; break;
                case 'alert': $logLevel = Logger::ALERT; break;
                case 'emergency': $logLevel = Logger::EMERGENCY; break;
                default: $logLevel = Logger::DEBUG; break;
            }

            foreach ($handlers as $handler => $handlerConfig) {
                $outputHandler = null;
                if ($handler == 'phperror') {
                    $outputHandler = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $logLevel);
                } elseif ($handler == 'browser') {
                    $outputHandler = new BrowserConsoleHandler($logLevel);
                } elseif (($handler == 'syslog') && isset($handlerConfig['ident'])) {
                    $outputHandler = new SyslogHandler($handlerConfig['ident'], LOG_USER, $logLevel);
                } elseif (($handler == 'syslog_udp') && isset($handlerConfig['host'])) {
                    $port = (isset($handlerConfig['port'])) ? $handlerConfig['port'] : 514;
                    $outputHandler = new SyslogUdpHandler($handlerConfig['host'], $port, LOG_USER, $logLevel);
                } elseif ($handler == 'mail') {
                    $to = (isset($handlerConfig['to'])) ? $handlerConfig['to'] : null;
                    $subject = (isset($handlerConfig['subject'])) ? $handlerConfig['subject'] : 'Log';
                    $from = (isset($handlerConfig['from'])) ? $handlerConfig['from'] : null;

                    if ($to && $from) {
                        $outputHandler = new NativeMailerHandler($to, $subject, $from, $logLevel);
                    }
                } elseif ($handler == 'slack') {
                    $token = (isset($handlerConfig['token'])) ? $handlerConfig['to'] : null;
                    $channel = (isset($handlerConfig['channel'])) ? $handlerConfig['channel'] : null;
                    $username = (isset($handlerConfig['username'])) ? $handlerConfig['username'] : 'Stem';
                    $useAttachment = (isset($handlerConfig['useAttachment'])) ? $handlerConfig['useAttachment'] : true;
                    $iconEmoji = (isset($handlerConfig['iconEmoji'])) ? $handlerConfig['iconEmoji'] : null;

                    if ($token && $channel) {
                        $outputHandler = new SlackHandler($token, $channel, $username, $useAttachment, $iconEmoji, $logLevel);
                    }
                }

                if ($outputHandler) {
                    $formatter = null;
                    if (isset($handlerConfig['format']) && isset($handlerConfig['format']['output'])) {
                        $date = (isset($handlerConfig['format']['date'])) ? $handlerConfig['format']['date'] : null;
                        $allowInlineLineBreaks = (isset($handlerConfig['format']['line-breaks'])) ? $handlerConfig['format']['line-breaks'] : false;
                        $output = $handlerConfig['format']['output'];

                        $formatter = new LineFormatter($output, $date.$allowInlineLineBreaks);
                    }

                    if ($formatter) {
                        $outputHandler->setFormatter($formatter);
                    }

                    self::$logger->pushHandler($outputHandler);
                }
            }
        }
    }

    public static function instance()
    {
        return self::$logger;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public static function emergency($message, array $context = [])
    {
        self::log(Logger::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public static function alert($message, array $context = [])
    {
        self::log(Logger::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public static function critical($message, array $context = [])
    {
        self::log(Logger::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public static function error($message, array $context = [])
    {
        self::log(Logger::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public static function warning($message, array $context = [])
    {
        self::log(Logger::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public static function notice($message, array $context = [])
    {
        self::log(Logger::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public static function info($message, array $context = [])
    {
        self::log(Logger::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public static function debug($message, array $context = [])
    {
        self::log(Logger::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public static function log($level, $message, array $context = [])
    {
        if (self::$logger) {
            self::$logger->addRecord($level, $message, $context);
        }
    }

    public static function flush()
    {
        if (self::$logger) {
            foreach (self::$logger->getHandlers() as $handler) {
                if ($handler instanceof BrowserConsoleHandler) {
                    $handler->send();
                }
            }
        }
    }
}
