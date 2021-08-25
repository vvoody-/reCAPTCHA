<?php declare(strict_types = 1);

namespace Contributte\ReCaptcha;

final class ReCaptchaResponse
{

	// Error code list
	public const ERROR_CODE_MISSING_INPUT_SECRET = 'missing-input-secret';
	public const ERROR_CODE_INVALID_INPUT_SECRET = 'invalid-input-secret';
	public const ERROR_CODE_MISSING_INPUT_RESPONSE = 'missing-input-response';
	public const ERROR_CODE_INVALID_INPUT_RESPONSE = 'invalid-input-response';
	public const ERROR_CODE_UNKNOWN = 'unknow';

	/** @var bool */
	private $success;

	/** @var string[]|string|null */
	private $error;

	/** @var int|null */
	private $score;

	/**
	 * @param string[]|string|null $error
	 */
	public function __construct(bool $success, ?int $score = null, $error = null)
	{
		$this->success = $success;
		$this->score = $score;
		$this->error = $error;
	}

	public function isSuccess(): bool
	{
		return $this->success;
	}

	public function getScore(): ?int
	{
		return $this->score;
	}

	/**
	 * @return string[]|string|null
	 */
	public function getError()
	{
		return $this->error;
	}

	public function __toString(): string
	{
		return (string) $this->isSuccess();
	}

}
