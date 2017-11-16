<?php
declare(strict_types=1);

namespace SRAG\Learnplaces\persistence\dto;

use SRAG\Lernplaces\persistence\mapping\ConfigurationModelMappingAware;

/**
 * Class Configuration
 *
 * @package SRAG\Lernplaces\persistence\dto
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class Configuration {

	use ConfigurationModelMappingAware;

	/**
	 * @var int $id
	 */
	private $id;
	/**
	 * @var bool $online
	 */
	private $online;
	/**
	 * @var string $defaultVisibility
	 */
	private $defaultVisibility;


	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}


	/**
	 * @param int $id
	 *
	 * @return Configuration
	 */
	public function setId(int $id): Configuration {
		$this->id = $id;

		return $this;
	}


	/**
	 * @return bool
	 */
	public function isOnline(): bool {
		return $this->online;
	}


	/**
	 * @param bool $online
	 *
	 * @return Configuration
	 */
	public function setOnline(bool $online): Configuration {
		$this->online = $online;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getDefaultVisibility(): string {
		return $this->defaultVisibility;
	}


	/**
	 * @param string $defaultVisibility
	 *
	 * @return Configuration
	 */
	public function setDefaultVisibility(string $defaultVisibility): Configuration {
		$this->defaultVisibility = $defaultVisibility;

		return $this;
	}

}