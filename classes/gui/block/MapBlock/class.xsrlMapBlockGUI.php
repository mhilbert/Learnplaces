<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use SRAG\Learnplaces\container\PluginContainer;
use SRAG\Learnplaces\gui\block\MapBlock\MapBlockPresentationView;
use SRAG\Learnplaces\gui\block\PictureUploadBlock\MapBlockEditFormView;
use SRAG\Learnplaces\gui\block\util\BlockIdReferenceValidationAware;
use SRAG\Learnplaces\gui\block\util\ReferenceIdAware;
use SRAG\Learnplaces\gui\component\PlusView;
use SRAG\Learnplaces\gui\exception\ValidationException;
use SRAG\Learnplaces\gui\helper\CommonControllerAction;
use SRAG\Learnplaces\service\publicapi\block\ConfigurationService;
use SRAG\Learnplaces\service\publicapi\block\LearnplaceService;
use SRAG\Learnplaces\service\publicapi\block\MapBlockService;
use SRAG\Learnplaces\service\publicapi\model\LearnplaceModel;
use SRAG\Learnplaces\service\publicapi\model\MapBlockModel;
use SRAG\Learnplaces\service\security\AccessGuard;

/**
 * Class xsrlMapBlockGUI
 *
 * @package SRAG\Learnplaces\gui\block\MapBlock
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class xsrlMapBlockGUI {

	use BlockIdReferenceValidationAware;
	use ReferenceIdAware;

	const TAB_ID = 'Map';
	const BLOCK_ID_QUERY_KEY = 'block';

	/**
	 * @var ilTabsGUI $tabs
	 */
	private $tabs;
	/**
	 * @var ilTemplate $template
	 */
	private $template;
	/**
	 * @var ilCtrl $controlFlow
	 */
	private $controlFlow;
	/**
	 * @var ilLearnplacesPlugin $plugin
	 */
	private $plugin;
	/**
	 * @var MapBlockService $mapBlockService
	 */
	private $mapBlockService;
	/**
	 * @var LearnplaceService $learnplaceService
	 */
	private $learnplaceService;
	/**
	 * @var ConfigurationService $configService
	 */
	private $configService;
	/**
	 * @var ServerRequestInterface $request
	 */
	private $request;
	/**
	 * @var AccessGuard $blockAccessGuard
	 */
	private $blockAccessGuard;


	/**
	 * xsrlMapBlockGUI constructor.
	 *
	 * @param ilTabsGUI              $tabs
	 * @param ilTemplate             $template
	 * @param ilCtrl                 $controlFlow
	 * @param ilLearnplacesPlugin    $plugin
	 * @param MapBlockService        $mapBlockService
	 * @param LearnplaceService      $learnplaceService
	 * @param ConfigurationService   $configService
	 * @param ServerRequestInterface $request
	 * @param AccessGuard            $blockAccessGuard
	 */
	public function __construct(ilTabsGUI $tabs, ilTemplate $template, ilCtrl $controlFlow, ilLearnplacesPlugin $plugin, MapBlockService $mapBlockService, LearnplaceService $learnplaceService, ConfigurationService $configService, ServerRequestInterface $request, AccessGuard $blockAccessGuard) {
		$this->tabs = $tabs;
		$this->template = $template;
		$this->controlFlow = $controlFlow;
		$this->plugin = $plugin;
		$this->mapBlockService = $mapBlockService;
		$this->learnplaceService = $learnplaceService;
		$this->configService = $configService;
		$this->request = $request;
		$this->blockAccessGuard = $blockAccessGuard;
	}


	public function executeCommand() {

		$this->template->getStandardTemplate();
		$cmd = $this->controlFlow->getCmd(CommonControllerAction::CMD_INDEX);
		$this->tabs->activateTab(self::TAB_ID);

		switch ($cmd) {
			case CommonControllerAction::CMD_INDEX:
				if ($this->blockAccessGuard->hasReadPermission()) {
					$this->index();
					$this->template->show();
					return true;
				}
				break;
			case CommonControllerAction::CMD_ADD:
			case CommonControllerAction::CMD_CANCEL:
			case CommonControllerAction::CMD_CONFIRM:
			case CommonControllerAction::CMD_CREATE:
			case CommonControllerAction::CMD_DELETE:
			case CommonControllerAction::CMD_EDIT:
			case CommonControllerAction::CMD_UPDATE:
				if ($this->blockAccessGuard->hasWritePermission()) {
					$this->{$cmd}();
					$this->template->show();
					return true;
				}
				break;
		}
		ilUtil::sendFailure($this->plugin->txt('common_access_denied'), true);
		$this->controlFlow->redirectByClass(ilRepositoryGUI::class);

		return false;
	}

	private function index() {

		try {
			/**
			 * @var MapBlockPresentationView $view
			 */
			$view = PluginContainer::resolve(MapBlockPresentationView::class);
			$learnplace = $this->learnplaceService->findByObjectId(ilObject::_lookupObjectId($this->getCurrentRefId()));
			$mapModel = $this->fetchMapModelFromLearnplace($learnplace);
			$this->redirectInvalidRequests($mapModel->getId());
			$view->setModels($mapModel, $learnplace->getLocation(), $learnplace->getConfiguration());
			$writePermission = $this->blockAccessGuard->hasWritePermission();
			$view->setReadonly(!$writePermission);
			$this->template->setContent($view->getHTML());
		}
		catch (LogicException $ex) {
			ilUtil::sendFailure($this->plugin->txt('error_message_no_map_found'), true);
			$this->controlFlow->redirectByClass(xsrlContentGUI::class, CommonControllerAction::CMD_INDEX);
		}
	}

	private function add() {
		$this->controlFlow->saveParameter($this, PlusView::POSITION_QUERY_PARAM);

		$config = $this->configService->findByObjectId(ilObject::_lookupObjectId($this->getCurrentRefId()));
		$block = new MapBlockModel();

		$block->setVisibility($config->getDefaultVisibility());
		$form = new MapBlockEditFormView($block);
		$form->fillForm();
		$this->template->setContent($form->getHTML());
	}

	private function create() {
		$form = new MapBlockEditFormView(new MapBlockModel());
		try {
			$queries = $this->request->getQueryParams();

			//store block
			$block = $form->getBlockModel();
			$block->setId(0); //mitigate block id injection
			$block = $this->mapBlockService->store($block);

			//fetch learnplace
			$learnplace = $this->learnplaceService->findByObjectId(ilObject::_lookupObjectId($this->getCurrentRefId()));

			//store relation learnplace <-> block
			$blocks = $learnplace->getBlocks();
			$blocks[] = $block;
			$learnplace->setBlocks($blocks);
			$this->learnplaceService->store($learnplace);

			ilUtil::sendSuccess($this->plugin->txt('message_changes_save_success'), true);
			$this->controlFlow->redirectByClass(xsrlContentGUI::class, CommonControllerAction::CMD_INDEX);
		}
		catch (ValidationException $ex) {
			$form->setValuesByPost();
			$this->template->setContent($form->getHTML());
		}
		catch (InvalidArgumentException $ex) {
			$form->setValuesByPost();
			$this->template->setContent($form->getHTML());
		}
	}

	private function edit() {
		$blockId = $this->getBlockId();
		$block = $this->mapBlockService->find($blockId);
		$form = new MapBlockEditFormView($block);
		$form->fillForm();
		$this->template->setContent($form->getHTML());
	}

	private function update() {

		$form = new MapBlockEditFormView(new MapBlockModel());
		try {
			//store block
			/**
			 * @var MapBlockModel $block
			 */
			$block = $form->getBlockModel();
			$this->redirectInvalidRequests($block->getId());
			$mapBlock = $this->mapBlockService->find($block->getId());
			$mapBlock->setVisibility($block->getVisibility());
			$this->mapBlockService->store($mapBlock);

			ilUtil::sendSuccess($this->plugin->txt('message_changes_save_success'), true);
			$this->controlFlow->redirectByClass(xsrlMapBlockGUI::class, CommonControllerAction::CMD_INDEX);
		}
		catch (ValidationException $ex) {
			$form->setValuesByPost();
			$this->template->setContent($form->getHTML());
		}
	}

	private function delete() {
		$blockId = $this->getBlockId();
		$this->redirectInvalidRequests($blockId);
		$this->mapBlockService->delete($blockId);
		ilUtil::sendSuccess($this->plugin->txt('message_delete_success'), true);
		$this->controlFlow->redirectByClass(xsrlContentGUI::class, CommonControllerAction::CMD_INDEX);
	}

	private function confirm() {
		$queries = $this->request->getQueryParams();
		$confirm = new ilConfirmationGUI();
		$confirm->setHeaderText($this->plugin->txt('confirm_delete_header'));
		$confirm->setFormAction(
			$this->controlFlow->getFormAction($this) .
			'&' .
			self::BLOCK_ID_QUERY_KEY .
			'=' .
			$queries[self::BLOCK_ID_QUERY_KEY]
		);
		$confirm->setConfirm($this->plugin->txt('common_delete'), CommonControllerAction::CMD_DELETE);
		$confirm->setCancel($this->plugin->txt('common_cancel'), CommonControllerAction::CMD_CANCEL);
		$this->template->setContent($confirm->getHTML());
	}

	private function cancel() {
		$this->controlFlow->redirectByClass(xsrlContentGUI::class, CommonControllerAction::CMD_INDEX);
	}

	private function getBlockId(): int {
		$queries = $this->request->getQueryParams();
		return intval($queries[self::BLOCK_ID_QUERY_KEY]);
	}

	private function fetchMapModelFromLearnplace(LearnplaceModel $learnplace): MapBlockModel {
		foreach ($learnplace->getBlocks() as $block) {
			if($block instanceof MapBlockModel)
				return $block;
		}
		throw new LogicException('No map found for the current learnpalce!');
	}
}