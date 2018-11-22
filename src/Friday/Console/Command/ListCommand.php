<?php
/**
 * IronPHP : PHP Development Framework
 * Copyright (c) IronPHP (https://github.com/IronPHP/IronPHP)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       IronPHP
 * @copyright     Copyright (c) IronPHP (https://github.com/IronPHP/IronPHP)
 * @link          
 * @since         0.0.1
 * @license       MIT License (https://opensource.org/licenses/mit-license.php)
 * @auther        Gaurang Parmar <gaurangkumarp@gmail.com>
 */

namespace Friday\Console\Command;

use Friday\Console\Colors;
use Friday\Console\Command;
use Friday\Foundation\Application;

class ListCommand extends Command
{
    /**
     * Command options.
     *
     * @var array
     */
    private $option;

    /**
     * Create a List Commands instance.
     *
     * @param array  $option
     * @return void
     */
    public function __construct($option = [])
    {
        $this->option = $option;
    }

    /**
     * Execute Commands.
     *
     * @return void
     */
    public function run()
    {
        $output = Colors::LIGHT_BLUE.str_repeat("-", 63).PHP_EOL.
        Colors::GREEN."IronPHP".Colors::WHITE." Framework ".Colors::YELLOW."".Application::VERSION.Colors::WHITE." (kernel: src, env: ".env('APP_ENV').", debug: ".env('APP_DEBUG').")".PHP_EOL.
        Colors::LIGHT_BLUE.str_repeat("-", 63).PHP_EOL.
        Colors::YELLOW."Usage:".PHP_EOL.
        Colors::WHITE."  command [options] [arguments]".PHP_EOL.PHP_EOL.
        Colors::YELLOW."Options:".PHP_EOL.
        Colors::GREEN."  -h, --help".Colors::WHITE."\t\tDisplay this help message".PHP_EOL.
        Colors::GREEN."  -V, --version".Colors::WHITE."\t\tDisplay this application version".PHP_EOL.PHP_EOL.
        Colors::YELLOW."Available commands:".PHP_EOL.
        Colors::GREEN."  list".Colors::WHITE."\t\t- Lists commands".PHP_EOL.
        Colors::GREEN."  serve".Colors::WHITE."\t\t- Serve the application on the PHP development server".PHP_EOL.
        Colors::GREEN."  key".Colors::WHITE."\t\t- Set the application key".PHP_EOL.
        Colors::GREEN."  version".Colors::WHITE."\t- Display Version".PHP_EOL.
        Colors::GREEN."  help".Colors::WHITE."\t\t- Displays help for a command".PHP_EOL.
        Colors::BG_BLACK.Colors::WHITE;
        return $output;
    }
}