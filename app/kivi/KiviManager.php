<?php

namespace App\kivi;

use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\EndMeetingParameters;
use BigBlueButton\Parameters\HooksCreateParameters;
use Illuminate\Support\Facades\Request;

class KiviManager
{
	private $bbb;
	
	const WEBHOOK_CALLBACK_URL = 'https://www.kidsgo.de/kivi/registerEvent/';
	const LOGOUT_URL = 'https://www.kidsgo.de';
	const ERROR_LOG_PATH = '/logs/kivi_error.log';

	public function __construct()
	{
		$this->moderatorPwd = env('BBB_MODERATOR_PWD');
		$this->participantPwd = env('BBB_PARTICIPANT_PWD');
		$this->bbb = new BigBlueButton();
	}

	public function endMeeting($meetingId)
	{
		$endMeetingParams = new EndMeetingParameters($meetingId, env('BBB_MODERATOR_PWD'));
		$this->bbb->endMeeting($endMeetingParams);
	}

	public function createHook()
	{
		$hookParam = new HooksCreateParameters(KiviManager::WEBHOOK_CALLBACK_URL);

		$hookParam->setGetRaw(false);

		return $this->bbb->hooksCreate($hookParam);
	}

	public function createMeeting($meetingId, $meetingName, $slides = '', $meta = [], $withHook = true)
	{
		$createParams = new CreateMeetingParameters($meetingId, $meetingName);
		$createParams = $createParams->setModeratorPassword(env('BBB_MODERATOR_PWD'))
			->setAttendeePassword(env('BBB_PARTICIPANT_PWD'))
			->setWelcomeMessage('<div><img src="https://www.kidsgo.de/app/images/kivi_logo_s.png" width="290"></div><p style="font-size: 18px; color: #696969">Chat für<br><b>' . $meetingName . '</b></p>')
			->setLogoutUrl(KiviManager::LOGOUT_URL);

		if (!empty($slides)) {
			$createParams->addPresentation('kivi_slide.pdf', $slides);
		}

		if (is_array($meta)) {
			foreach ($meta as $key => $value) {
				$createParams->addMeta($key, $value);
			}
		}

		if ($withHook) {
			$this->createHook($meetingId);
		}

		return $this->bbb->createMeeting($createParams);
	}

	public function getBBB()
	{
		return $this->bbb;
	}

	public function getUniversalHash($event)
	{
		return  md5("quIwI{$event->regDate}-lD7");
	}

	public function getUniversalURL($event)
	{
		return  Request::root() . "/kivi/join/{$this->getUniversalHash($event)}/{$event->id}/";
	}

	public function logError($code, $body = '', $throwException = true)
	{
		error_log(date('d.m.Y H:i:s') . " [$code] " . Request::fullUrl() . " - $body - " . Request::userAgent() . " \n", 3, app_path() . KiviManager::ERROR_LOG_PATH);

		if ($throwException) {
			throw new KiviException("Error $code");
		}
	}

	public function readCallbackEvent()
	{
		$raw = file_get_contents('php://input');

		$arr = [];

		parse_str($raw, $arr);

		$event = json_decode($arr['event']);

		return is_array($event) ? [$event[0], $arr['timestamp']] : null;
	}

	public function renderKiviPage($params)
	{
		// ... trigger presentational layer
		// ....
		
		if (is_array($params)) {
			// ....	
		}

		var_dump($params);
	}
}
