<?php

namespace app\kivi;

class KiviException extends \Exception
{
	private $data = [];
	public function __construct($message, $data = [])
	{
		parent::__construct($message);
		$this->data = $data;
		$this->log();
	}
	private function log()
	{
		error_log(date('[d.m.Y H:i:s] ') . $this->getKiviMessage() . "\n---\n", 0);
	}
	public function getKiviMessage()
	{
		return $this->getMessage() . ' +++ ' . implode(' ### ', $this->data);
	}
}
