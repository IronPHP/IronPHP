<?php
/**
 * IronPHP : PHP Development Framework
 * Copyright (c) IronPHP (https://github.com/IronPHP/IronPHP).
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) IronPHP
 *
 * @link		  https://github.com/IronPHP/IronPHP
 * @since         1.0.1
 *
 * @license       MIT License (https://opensource.org/licenses/mit-license.php)
 * @auther        GaurangKumar Parmar <gaurangkumarp@gmail.com>
 */

namespace Friday\Foundation\Exceptions;

use ErrorException;
use Friday\Foundation\Errors\Error;
use Friday\Foundation\Errors\Fatal;
use Friday\Foundation\Errors\Notice;
use Friday\Foundation\Errors\Warning;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Handler implements HandlerInterface
{
    /**
     * @var string
     */
    private $notifier;

    /**
     * @var array
     */
    private $lastError;

    /**
     * @var bool
     */
    private $isRegistered;

    /**
     * @var bool
     */
    private $allowQuit = true;

    /**
     * @var bool
     */
    private $sendOutput = true;

    /**
     * @var array
     */
    public static $LIST = [];

    /**
     * @var int|false
     */
    private $sendHttpCode = 500;

    /**
     * @var HandlerInterface[]
     */
    private $handlerStack = [];

    /**
     * @var array
     */
    private $silencedPatterns = [];

    /**
     * @var System
     */
    private $system;

    /**
     * @var bool
     */
    private $canThrowExceptions;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Create a new exception handler instance.
     *
     * @param System|null $system
     *
     * @return void
     */
    public function __construct(System $system = null)
    {
        // Create the logger
        $this->logger = new Logger('iron_logger');

        // Now add some handlers
        $this->logger->pushHandler(new StreamHandler(LOGS.'/app.log', Logger::DEBUG));
        $this->logger->pushHandler(new FirePHPHandler());

        // You can now use your logger
        $this->logger->info('My logger is now ready');

        $this->system = $system ?: new System();
        if (env('APP_DEBUG') === true) {
            ini_set('display_errors', 'on');
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 'off');
            error_reporting(0);
        }
    }

    /**
     * Set PHP internal logging file.
     *
     * @param bool|string $log
     *
     * @return void
     */
    public function logging($log = false)
    {
        if ($log !== false) {
            if (!ini_get('log_errors')) {
                ini_set('log_errors', true);
            }
            if (!ini_get('error_log')) {
                ini_set('error_log', $log);
            }
        }
    }

    /**
     * Registers this instance as an error handler.
     *
     * @return $this
     */
    public function register()
    {
        if (!$this->isRegistered) {
            $this->system->setErrorHandler([$this, self::ERROR_HANDLER]);
            $this->system->setExceptionHandler([$this, self::EXCEPTION_HANDLER]);
            $this->system->registerShutdownFunction([$this, self::SHUTDOWN_HANDLER]);

            $this->isRegistered = true;
        }

        return $this;
    }

    /**
     * Unregisters all handlers registered by this Whoops\Run instance.
     *
     * @return $this
     */
    public function unregister()
    {
        if ($this->isRegistered) {
            $this->system->restoreExceptionHandler();
            $this->system->restoreErrorHandler();

            $this->isRegistered = false;
        }

        return $this;
    }

    /**
     * Should Whoops allow Handlers to force the script to quit?
     *
     * @param bool|int $exit
     *
     * @return bool
     */
    public function allowQuit($exit = null)
    {
        if (func_num_args() == 0) {
            return $this->allowQuit;
        }

        return $this->allowQuit = (bool) $exit;
    }

    /**
     * Handles an exception, ultimately generating a Whoops error
     * page.
     *
     * @param \Throwable $exception
     *
     * @return string Output generated by handlers
     */
    public function handleException($exception)
    {
        $severity = 0;
        //$this->system->startOutputBuffering();
        //$willQuit = $handlerResponse == Handler::QUIT && $this->allowQuit();
        //$output = $this->system->cleanOutputBuffer();
        /*
        if ($this->writeToOutput()) {
            if ($willQuit) {
                while ($this->system->getOutputBufferLevel() > 0) {
                    $this->system->endOutputBuffering();
                }
                if (Misc::canSendHeaders() && $handlerContentType) {
                    header("Content-Type: {$handlerContentType}");
                }
            }
            $this->writeToOutputNow($output);
        }
        if ($willQuit) {
            $this->system->flushOutputBuffer();
            $this->system->stopExecution(1);
        }
        */
        $log = $exception->getMessage()."\n".$exception->getTraceAsString().LINEBREAK;
        if (ini_get('log_errors')) {
            error_log($log, 0);
        }
        $output = get_class($exception).':'.$log;
        //return $output;
        if (method_exists($exception, 'getSeverity')) {
            $severityCode = $exception->getSeverity();
            $severity = $this->getSeverity($severityCode);
        }

        if (env('APP_DEBUG') === true) {
            if ( $_SERVER['SESSIONNAME'] != 'Console' ) {
                $output = '
				<!DOCTYPE html>
				<html lang="en">
					<head>
						<meta charset="utf-8">
						<meta http-equiv="X-UA-Compatible" content="IE=edge">
						<meta name="viewport" content="width=device-width, initial-scale=1">
						<title>Error</title>
						<!-- Fonts -->
						<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
						<style>html, body {background-color: #fff;color: #636b6f;font-family: \'Nunito\', sans-serif;font-weight: 100;height: 100vh;margin: 0;}.full-height {height: 90vh;}.flex-center {align-items: center;display: flex;justify-content: center;}.content {text-align: center;}.title {font-size: 36px;padding: 20px;}</style>
					</head>
					<body>
						<div class="flex-center position-ref full-height">
							<div class="content">
								<h2 class="title" style="color: rgb(190, 50, 50)">Exception Occured</h2>
        						<table style="width: 800px; display: inline-block;text-align:left">
        							<tr style="background-color:rgb(230,230,230)">
										<th style="width: 80px">Type</th>
										<td>'.get_class($exception).'</td>
									</tr>
        							<tr style="background-color:rgb(240,240,240)">
										<th>Code</th>
										<td>'.$exception->getCode().'</td>
									</tr>
        							<tr style="background-color:rgb(240,240,240)">
										<th>Trace</th>
										<td>'.trim(str_replace('#', '<br>#', str_replace(ROOT, '', $exception->getTraceAsString())), '<br>').'</td>
									</tr>'.
                                        (isset($severityCode) ? '
        							<tr style="background-color:rgb(240,240,240)">
										<th>Severity</th>
										<td>'.$severityCode.' - '.$severity.'</td>
									</tr>.'
                                          : '').'
        							<tr style="background-color:rgb(240,240,240)">
										<th>Message</th>
										<td>'.$exception->getMessage().'</td>
									</tr>
        							<tr style="background-color:rgb(230,230,230)">
										<th>File</th>
										<td>'.$exception->getFile().'</td>
									</tr>
        							<tr style="background-color:rgb(240,240,240)">
										<th>Line</th>
										<td>'.$exception->getLine().'</td>
									</tr>
        						</table>
							</div>
						</div>
					</body>
				</html>
			';
            } else {
                $output = "
+-----------------------------------------------+
| Type\t\t|".get_class($exception)."\t\t\t|
+-----------------------------------------------+
| Code\t\t| ".$exception->getCode()."\t\t\t\t|
+-----------------------------------------------+".(isset($severityCode) ? "
| Severity\t| ".$severityCode.' - '.$severity."\t\t|" : '')."
+-----------------------------------------------+
| Message\t| ".$exception->getMessage()."\t\t\t\t|
+-----------------------------------------------+
| File\t\t| ".$exception->getFile()." |
+-----------------------------------------------+
| Line\t\t| ".$exception->getLine()."\t\t\t\t|
+-----------------------------------------------+
";
            }
            echo $output;
        } else {
            $message = 'Type: '.get_class($exception)."; Message: {$exception->getMessage()}; File: {$exception->getFile()}; Line: {$exception->getLine()};";
            if (!file_exists(LOGS.'/debug.log') || !is_file(LOGS.'/debug.log')) {
                file_put_contents(LOGS.'/debug.log', '');
            }
            error_log('['.date('Y-y-d H:i:s').'] '.$message.PHP_EOL.PHP_EOL, 3, LOGS.'/debug.log');
            //header( "Location: {$config["error_page"]}" );
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Error</title><!-- Fonts --><link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
<style>html, body {background-color: #fff;color: #636b6f;font-family: \'Nunito\', sans-serif;font-weight: 100;height: 100vh;margin: 0;}.full-height {height: 90vh;}.flex-center {align-items: center;display: flex;justify-content: center;}.content {text-align: center;}.title {font-size: 36px;padding: 20px;}</style><body><div class="flex-center position-ref full-height"><div class="content"><div class="title">It looks like something went wrong.</div></div></div></body></html>';
        }
        exit();
    }

    /**
     * Converts generic PHP errors to \ErrorException
     * instances, before passing them off to be handled.
     *
     * This method MUST be compatible with set_error_handler.
     *
     * @param int    $level
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @throws ErrorException
     *
     * @return bool
     */
    public function handleError($level, $message, $file = null, $line = null)
    {
        $this->lastError = [
            'message' => $message,
            'file'    => $file,
            'line'    => $line,
        ];

        $trace = debug_backtrace();
        if (count($trace) > 0 && !isset($trace[0]['file'])) {
            array_shift($trace);
        }

        switch ($level) {
            case E_NOTICE:
            case E_USER_NOTICE:
            //case @E_STRICT:
                $exc = new Notice($message, $trace);
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $exc = new Warning($message, $trace);
                break;
            case E_ERROR:
            case E_CORE_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_USER_ERROR:
                $exc = new Fatal($message, $trace);
                break;
            //case @E_RECOVERABLE_ERROR:
                //'Catchable';
            default:
                $exc = new Error($message, $trace);
                break;
        }
        $notice = $this->buildNotice($exc);

        if ($level & $this->system->getErrorReportingLevel()) {
            $exception = new ErrorException($message, /*code*/ $level, /*severity*/ $level, $file, $line);
            if ($this->canThrowExceptions) {
                throw $exception;
            } else {
                $this->handleException($exception);
            }
            // Do not propagate errors which were already handled.
            return true;
        }

        // Propagate error to the next handler, allows error_get_last() to
        // work on silenced errors.
        return false;
    }

    /**
     * Special case to deal with Fatal errors and the like.
     *
     * @return void
     */
    public function handleShutdown()
    {
        $error = $this->system->getLastError();
        if ($error === null) {
            return;
        }
        if (($error['type'] & error_reporting()) === 0) {
            return;
        }
        if ($this->lastError !== null &&
            $error['message'] === $this->lastError['message'] &&
            $error['file'] === $this->lastError['file'] &&
            $error['line'] === $this->lastError['line']) {
            return;
        }
        $trace = [[
            'file' => $error['file'],
            'line' => $error['line'],
        ]];
        $exc = new Fatal($error['message'], $trace);
        $notice = $this->buildNotice($exc);

        // If we reached this step, we are in shutdown handler.
        // An exception thrown in a shutdown handler will not be propagated
        // to the exception handler. Pass that information along.
        $this->canThrowExceptions = false;

        if ($error && $this->isLevelFatal($error['type'])) {
            // If there was a fatal error,
            // it was not handled in handleError yet.
            $this->handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }

    /**
     * Builds Airbrake notice from exception.
     *
     * @param \Throwable|\Exception $exc Exception or class that implements similar interface.
     *
     * @return array Airbrake notice
     */
    public function buildNotice($exc)
    {
        $error = [
            'type'      => get_class($exc),
            'message'   => $exc->getMessage(),
            'backtrace' => $exc->getTrace(),
        ];

        $notice = [
            'errors' => [$error],
        ];
        if (!empty($_REQUEST)) {
            $notice['params'] = $_REQUEST;
        }
        if (!empty($_SESSION)) {
            $notice['session'] = $_SESSION;
        }

        return $notice;
    }

    /**
     * Determine if an error level is fatal (halts execution).
     *
     * @param int $level
     *
     * @return bool
     */
    public static function isLevelFatal($level)
    {
        $errors = E_ERROR;
        $errors |= E_PARSE;
        $errors |= E_CORE_ERROR;
        $errors |= E_CORE_WARNING;
        $errors |= E_COMPILE_ERROR;
        $errors |= E_COMPILE_WARNING;

        return ($level & $errors) > 0;
    }

    /**
     * Get severity name by severity code.
     *
     * @param int $severityCode
     *
     * @return string
     *
     * @since 1.0.6
     */
    public function getSeverity($severityCode)
    {
        switch ($severityCode) {
            case E_ERROR: // 1 //
                return 'E_ERROR';
                break;
            case E_WARNING: // 2 //
                return 'E_WARNING';
                break;
            case E_PARSE: // 4 //
                return 'E_PARSE';
                break;
            case E_NOTICE: // 8 //
                return 'E_NOTICE';
                break;
            case E_CORE_ERROR: // 16 //
                return 'E_CORE_ERROR';
                break;
            case E_CORE_WARNING: // 32 //
                return 'E_CORE_WARNING';
                break;
            case E_COMPILE_ERROR: // 64 //
                return 'E_COMPILE_ERROR';
                break;
            case E_COMPILE_WARNING: // 128 //
                return 'E_COMPILE_WARNING';
                break;
            case E_USER_ERROR: // 256 //
                return 'E_USER_ERROR';
                break;
            case E_USER_WARNING: // 512 //
                return 'E_USER_WARNING';
                break;
            case E_USER_NOTICE: // 1024 //
                return 'E_USER_NOTICE';
                break;
            case E_STRICT: // 2048 //
                return 'E_STRICT';
                break;
            case E_RECOVERABLE_ERROR: // 4096 //
                return 'E_RECOVERABLE_ERROR';
                break;
            case E_DEPRECATED: // 8192 //
                return 'E_DEPRECATED';
                break;
            case E_USER_DEPRECATED: // 16384 //
                return 'E_USER_DEPRECATED';
                break;
            case E_ALL: // 32767 //
                return 'E_ALL';
                break;
            default:
                return 'UNKOWN';
                break;
        }
    }
}
