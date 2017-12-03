<?php

namespace SRAG\Learnplaces\service\publicapi\block\util;

use LogicException;
use SRAG\Learnplaces\service\media\PictureService;
use SRAG\Learnplaces\service\publicapi\block\AccordionBlockService;
use SRAG\Learnplaces\service\publicapi\block\ILIASLinkBlockService;
use SRAG\Learnplaces\service\publicapi\block\MapBlockService;
use SRAG\Learnplaces\service\publicapi\block\PictureBlockService;
use SRAG\Learnplaces\service\publicapi\block\PictureUploadBlockService;
use SRAG\Learnplaces\service\publicapi\block\RichTextBlockService;
use SRAG\Learnplaces\service\publicapi\model\AccordionBlockModel;
use SRAG\Learnplaces\service\publicapi\model\BlockModel;
use SRAG\Learnplaces\service\publicapi\model\ILIASLinkBlockModel;
use SRAG\Learnplaces\service\publicapi\model\MapBlockModel;
use SRAG\Learnplaces\service\publicapi\model\PictureBlockModel;
use SRAG\Learnplaces\service\publicapi\model\PictureUploadBlockModel;
use SRAG\Learnplaces\service\publicapi\model\RichTextBlockModel;

/**
 * Class DefaultBlockOperationDispatcher
 *
 * @package SRAG\Learnplaces\service\publicapi\block\util
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class DefaultBlockOperationDispatcher implements BlockOperationDispatcher {

	/**
	 * @var AccordionBlockService $accordionBlockService
	 */
	private $accordionBlockService;
	/**
	 * @var ILIASLinkBlockService $iliasLinkBlockService
	 */
	private $iliasLinkBlockService;
	/**
	 * @var PictureBlockService $pictureBlockService
	 */
	private $pictureBlockService;
	/**
	 * @var PictureUploadBlockService $pictureUploadBlockService
	 */
	private $pictureUploadBlockService;
	/**
	 * @var MapBlockService $mapBlockService
	 */
	private $mapBlockService;
	/**
	 * @var RichTextBlockService $richTextBlockService
	 */
	private $richTextBlockService;


	/**
	 * DefaultBlockOperationDispatcher constructor.
	 *
	 * @param AccordionBlockService     $accordionBlockService
	 * @param ILIASLinkBlockService     $iliasLinkBlockService
	 * @param PictureBlockService       $pictureBlockService
	 * @param PictureUploadBlockService $pictureUploadBlockService
	 * @param MapBlockService           $mapBlockService
	 * @param RichTextBlockService      $richTextBlockService
	 */
	public function __construct(AccordionBlockService $accordionBlockService, ILIASLinkBlockService $iliasLinkBlockService, PictureBlockService $pictureBlockService, PictureUploadBlockService $pictureUploadBlockService, MapBlockService $mapBlockService, RichTextBlockService $richTextBlockService) {
		$this->accordionBlockService = $accordionBlockService;
		$this->iliasLinkBlockService = $iliasLinkBlockService;
		$this->pictureBlockService = $pictureBlockService;
		$this->pictureUploadBlockService = $pictureUploadBlockService;
		$this->mapBlockService = $mapBlockService;
		$this->richTextBlockService = $richTextBlockService;
	}


	/**
	 * @inheritDoc
	 */
	public function deleteAll(array $blocks) {
		foreach ($blocks as $block) {
			$this->deleteBlockByType($block);
		}
	}

	private function deleteBlockByType(BlockModel $block) {
		switch (true) {
			case $block instanceof AccordionBlockModel:
				$this->accordionBlockService->delete($block->getId());
				return;
			case $block instanceof PictureBlockModel:
				$this->pictureBlockService->delete($block->getId());
				return;
			case $block instanceof ILIASLinkBlockModel:
				$this->iliasLinkBlockService->delete($block->getId());
				return;
			case $block instanceof PictureUploadBlockModel:
				$this->pictureUploadBlockService->delete($block->getId());
				return;
			case $block instanceof MapBlockModel:
				$this->mapBlockService->delete($block->getId());
				return;
			case $block instanceof RichTextBlockModel:
				$this->richTextBlockService->delete($block->getId());
				return;
			default:
				throw new LogicException('Unable to dispatch block delete operation');
		}
	}
}