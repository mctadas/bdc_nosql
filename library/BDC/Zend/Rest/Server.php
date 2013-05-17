<?php

namespace BDC\Zend\Rest;

/**
 * @package Kompro 
 */
class Server extends \Zend_Rest_Server 
{

	/**
	 * Extends Zend_Rest_Server::handle()
	 *
	 * @param  array $request
	 * @param  bool  $plain
	 * @throws Zend_Rest_Server_Exception
	 * @return string|void
	 */
	public function handle($request = false, $plain = false) 
	{
		$this->_headers = array('Content-Type: text/xml');
		if (!$request) {
			$request = $_REQUEST;
		}

		foreach ($request as $key => $value) {

			if ($value == "") {

				unset($request[$key]);
			}
		}

		if (isset($request['method'])) {
			$this->_method = $request['method'];
			if (isset($this->_functions[$this->_method])) {
				if ($this->_functions[$this->_method] instanceof \Zend_Server_Reflection_Function || $this->_functions[$this->_method] instanceof \Zend_Server_Reflection_Method && $this->_functions[$this->_method]->isPublic()) {
					$request_keys = array_keys($request);
					array_walk($request_keys, array(__CLASS__, "lowerCase"));
					$request = array_combine($request_keys, $request);

					$func_args = $this->_functions[$this->_method]->getParameters();

					$calling_args = array();
					$missing_args = array();
					foreach ($func_args as $arg) {
						if (isset($request[strtolower($arg->getName())])) {
							$calling_args[] = $request[strtolower($arg->getName())];
						} elseif ($arg->isOptional()) {
							$calling_args[] = $arg->getDefaultValue();
						} else {
							$missing_args[] = $arg->getName();
						}
					}

					foreach ($request as $key => $value) {
						if (substr($key, 0, 3) == 'arg') {
							$key = str_replace('arg', '', $key);
							$calling_args[$key] = $value;
							if (($index = array_search($key, $missing_args)) !== false) {
								unset($missing_args[$index]);
							}
						}
					}

					// Sort arguments by key -- @see ZF-2279
					ksort($calling_args);

					$result = false;
					if (count($calling_args) < count($func_args)) {
						$result = $this->fault(new \Zend_Rest_Server_Exception('Invalid Method Call to ' . $this->_method . '. Missing argument(s): ' . implode(', ', $missing_args) . '.'), 400);
					}

					if (!$result && $this->_functions[$this->_method] instanceof \Zend_Server_Reflection_Method) {
						// Get class
						$class = $this->_functions[$this->_method]->getDeclaringClass()->getName();

						if ($this->_functions[$this->_method]->isStatic()) {
							// for some reason, invokeArgs() does not work the same as
							// invoke(), and expects the first argument to be an object.
							// So, using a callback if the method is static.
							$result = $this->_callStaticMethod($class, $calling_args);
						} else {
							// Object method
							$result = $this->_callObjectMethod($class, $calling_args);
						}
					} elseif (!$result) {
						try {
							$result = call_user_func_array($this->_functions[$this->_method]->getName(), $calling_args); //$this->_functions[$this->_method]->invokeArgs($calling_args);
						} catch (Exception $e) {
							$result = $this->fault($e);
						}
					}
				} else {
					$result = $this->fault(
							new \Zend_Rest_Server_Exception("Unknown Method '$this->_method'."), 404
					);
				}
			} else {
				$result = $this->fault(
						new \Zend_Rest_Server_Exception("Unknown Method '$this->_method'."), 404
				);
			}
		} else {
			$result = $this->fault(
					new \Zend_Rest_Server_Exception("No Method Specified."), 404
			);
		}

		if ($plain) {

			$response = $result;
		} else {

			if ($result instanceof \SimpleXMLElement) {
				$response = $result->asXML();
			} elseif ($result instanceof \DOMDocument) {
				$response = $result->saveXML();
			} elseif ($result instanceof \DOMNode) {
				$response = $result->ownerDocument->saveXML($result);
			} elseif (is_array($result) || is_object($result)) {
				$response = $this->_handleStruct($result);
			} else {
				$response = $this->_handleScalar($result);
			}
		}
		if (!$this->returnResponse()) {
			if (!headers_sent()) {
				foreach ($this->_headers as $header) {
					header($header);
				}
			}

			echo $response;
			return;
		}

		return $response;
	}

}