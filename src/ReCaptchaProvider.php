<?php declare(strict_types = 1);

namespace Contributte\ReCaptcha;

use Nette\Forms\Controls\BaseControl;
use Nette\SmartObject;
use Tracy\Debugger;

/**
 * @method onValidateControl(ReCaptchaProvider $provider, BaseControl $control)
 * @method onValidate(ReCaptchaProvider $provider, mixed $response)
 */
class ReCaptchaProvider
{

	use SmartObject;

	// ReCaptcha FTW!
	public const FORM_PARAMETER = 'g-recaptcha-response';
	public const VERIFICATION_URL = 'https://www.google.com/recaptcha/api/siteverify';

	/** @var callable[] */
	public $onValidate = [];

	/** @var callable[] */
	public $onValidateControl = [];

	/** @var string */
	private $siteKey;

	/** @var string */
	private $secretKey;

	/** @var int */
	private $minScorePercentage;

	public function __construct(string $siteKey, string $secretKey, int $minScorePercentage)
	{
		$this->siteKey = $siteKey;
		$this->secretKey = $secretKey;
		$this->minScorePercentage = $minScorePercentage;
	}

	public function getSiteKey(): string
	{
		return $this->siteKey;
	}

	/**
	 * @param mixed $response
	 */
	public function validate($response): ReCaptchaResponse
	{
		// Fire events!
		$this->onValidate($this, $response);

		// Response is empty
		if (empty($response)) {
			return new ReCaptchaResponse(false, null, 'Your ReCaptcha hidden input is empty. Did you linked javascript files \'invisibleRecaptcha.js\' to website?');
		}

		// Load response
		$response = $this->makeRequest($response);

		// Response failed..
		if (empty($response)) {
			return new ReCaptchaResponse(false, null, 'ReCaptcha request failed');
		}

		// Decode server answer (with key assoc reserved)
		$answer = json_decode($response, true);

		// invisible reCaptcha v3
		if (array_key_exists('score', $answer)) {
			$score = (int) round($answer['score']*100);

			if ($answer['success'] !== true || $score < $this->minScorePercentage) {
				return new ReCaptchaResponse(false, $score, $answer['error-codes'] ?? null);
			} else {
				return new ReCaptchaResponse(true, $score);
			}

			// invisible reCaptcha v2
		} else {
			return $answer['success'] === true ? new ReCaptchaResponse(true) : new ReCaptchaResponse(false, null, $answer['error-codes'] ?? null);
		}
	}

	public function validateControl(BaseControl $control): ReCaptchaResponse
	{
		// Fire events!
		$this->onValidateControl($this, $control);

		// Get response
		return $this->validate($control->getValue());
	}


	/**
	 * @param mixed $response
	 * @return mixed
	 */
	protected function makeRequest($response, ?string $remoteIp = null)
	{
		$params = [
			'secret' => $this->secretKey,
			'response' => $response,
		];

		if ($remoteIp !== null) {
			$params['remoteip'] = $remoteIp;
		}

		return @file_get_contents($this->buildUrl($params));
	}

	/**
	 * @param mixed[] $parameters
	 */
	protected function buildUrl(array $parameters = []): string
	{
		return self::VERIFICATION_URL . '?' . http_build_query($parameters);
	}

}
