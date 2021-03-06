<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\BlockList\CreateBlockListAPIRequest;
use App\Http\Resources\BlockListResource;
use App\Repositories\BlockListRepository;
use App\Services\BlockListService;
use Illuminate\Http\Request;
use Response;
use Symfony\Component\HttpFoundation\Response as FResponse;

/**
 * Class block_listController
 * @package App\Http\Controllers\API
 */
class BlockListAPIController extends AppBaseController
{
    /**
     * @var  BlockListRepository
     */
    private $blockListRepository;

    /**
     * @var  BlockListService
     */
    private $blockListService;

    public function __construct(BlockListRepository $blockListRepo, BlockListService $blockListService)
    {
        $this->blockListRepository = $blockListRepo;
        $this->blockListService = $blockListService;
    }

    /**
     * Display a listing of the block_list.
     * GET|HEAD /blockLists
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {        
        $userId = $this->getUserId();
        $blockLists = $this->blockListRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        )->where('block_by', '=', $userId);
        $formatted_data = BlockListResource::collection($blockLists);
        return $this->sendResponse($formatted_data, 'Block Lists retrieved successfully');
    }

    /**
     * Store a newly created block_list in storage.
     * POST /blockLists
     *
     * @param CreateBlockListAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateBlockListAPIRequest $request)
    {
        return $blockList = $this->blockListService->store($request->all());
    }

    /**
     * Display the specified block_list.
     * GET|HEAD /blockLists/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var block_list $blockList */
        $blockList = $this->blockListRepository->find($id);

        if (empty($blockList)) {
            return $this->sendError('Block List not found');
        }

        return $this->sendResponse($blockList->toArray(), 'Block List retrieved successfully');
    }


    /**
     * Remove the specified block_list from storage.
     * DELETE /blockLists/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        /** @var block_list $blockList */
        $blockList = $this->blockListRepository->findOrFail($id);

        if (empty($blockList)) {
            return $this->sendError('Block List not found');
        }
        $blockList->delete();

        return $this->sendResponse([], 'Candidate Un-Block successful', FResponse::HTTP_OK);
    }
}
