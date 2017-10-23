<?php
declare(strict_types=1);

namespace SRAG\Lernplaces\persistence\dto;

use DateTime;

/**
 * Class VisitJournal
 *
 * @package SRAG\Lernplaces\persistence\dto
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class VisitJournal {

	/**
	 * @var int $id
	 */
	private $id;
	/**
	 * @var int $userId
	 */
	private $userId;
	/**
	 * @var DateTime $time
	 */
	private $time;


	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}


	/**
	 * @param int $id
	 *
	 * @return VisitJournal
	 */
	public function setId(int $id): VisitJournal {
		$this->id = $id;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->userId;
	}


	/**
	 * @param int $userId
	 *
	 * @return VisitJournal
	 */
	public function setUserId(int $userId): VisitJournal {
		$this->userId = $userId;

		return $this;
	}


	/**
	 * @return DateTime
	 */
	public function getTime(): DateTime {
		return $this->time;
	}


	/**
	 * @param DateTime $time
	 *
	 * @return VisitJournal
	 */
	public function setTime(DateTime $time): VisitJournal {
		$this->time = $time;

		return $this;
	}
}