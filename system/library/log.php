<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
 * Log class
 */

if (!defined('DIR_LOGS')) {
    define('DIR_LOGS', __DIR__ . '/logs/');
}

class Log {
    private $handle;
    private $filename;

    public function __construct($filename) {
        $this->filename = $filename;
        $this->open();
    }

    private function open() {
        if (!is_dir(DIR_LOGS)) {
            mkdir(DIR_LOGS, 0777, true);
        }

        $this->handle = fopen(DIR_LOGS . $this->filename, 'a');

        if (!$this->handle) {
            throw new Exception("Erro ao abrir o arquivo de log: " . DIR_LOGS . $this->filename);
        }
    }

    public function write($message) {
        if (!$this->handle) {
            $this->open();
        }

        if ($this->handle) {
            fwrite($this->handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");
        }
    }

    public function __destruct() {
        if ($this->handle) {
            fclose($this->handle);
        }
    }
}





// class Log {
// 	private $handle;
	
// 	/**
// 	 * Constructor
// 	 *
// 	 * @param	string	$filename
//  	*/
// 	public function __construct($filename) {
// 		$this->handle = fopen(DIR_LOGS . $filename, 'a');
// 	}
	
// 	/**
//      * 
//      *
//      * @param	string	$message
//      */
// 	public function write($message) {
// 		fwrite($this->handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");
// 	}
	
// 	/**
//      * 
//      *
//      */
// 	public function __destruct() {
// 		fclose($this->handle);
// 	}
// }