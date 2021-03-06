<?php

namespace App\Http\Controllers\API;

use App\Models\ShortListedCandidate;
use App\Repositories\ShortListedCandidateRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;
use Symfony\Component\HttpFoundation\Response as FResponse;
use App\Http\Resources\UserReportResource;

use App\Services\AdminService;
use App\Services\SubscriptionService;
use App\Repositories\UserRepository;
use App\Models\User;

/**
 * Class ShortListedCandidateController
 * @package App\Http\Controllers\API\V1
 */
class AdminDashboardController extends AppBaseController
{
    /**
     * @var  ShortListedCandidateRepository
     */
    private $shortListedCandidateRepository;

    /**
     * @var  AdminService
     */
    private $adminService;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var SubscriptionService
     */
    protected $subscriptionService;

    public function __construct(
        ShortListedCandidateRepository $shortListedCandidateRepo,
        AdminService $adminService,
        UserRepository $UserRepository,
        SubscriptionService $subscriptionService

    )
    {
        $this->shortListedCandidateRepository = $shortListedCandidateRepo;
        $this->adminService = $adminService;
        $this->userRepository = $UserRepository;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display a listing of the ShortListedCandidate.
     * GET|HEAD /shortListedCandidates
     *
     * @param Request $request
     * @return Response
     */
    public function dashboard(Request $request)
    {
        $userId = $this->getUserId();
        $personalList = ShortListedCandidate::whereNull('shortlisted_for')->count();
        $personalListTeam = ShortListedCandidate::whereNotNull('shortlisted_for')->count();
        $result['short_list']['total'] = $personalList + $personalListTeam;
        $result['short_list']['personal'] = $personalList;
        $result['short_list']['team'] = $personalListTeam;
        $result['profile_view'] = 0;
        return $this->sendResponse($result, 'Dashboard information patch successfully');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userReport(Request $request)
    {

        $search = [];
        $page = $request['page'] ?: 1;
        $parpage = $request['parpage'] ?: 10;
        $userList = $this->userRepository->getModel()->newQuery();
        if ($request->has('account_type')) {
            $search['account_type'] = $request->input('account_type');
        }

        if ($page) {
            $skip = $parpage * ($page - 1);
            $queryData = $this->userRepository->all($search, $skip, $parpage);
        } else {
            $queryData = $this->userRepository->all($search, 0, $parpage);
        }

        $PaginationCalculation = $userList->paginate($parpage);
        $team_info = UserReportResource::collection($queryData);
        $result['result'] = $team_info;
        $result['pagination'] = self::pagination($PaginationCalculation);

        return $this->sendResponse($result, 'Data retrieved successfully');

    }

    /**
     * @param Request $request
     * @return string
     */
    public function pendingUserList(Request $request)
    {
        $parpage = 10;
        $page = 1;
        if ($request->has('parpage')): $parpage = $request->input('parpage'); endif;
        if ($request->has('page')): $page = $request->input('page'); endif;

        $search = $this->userRepository->getModel()->newQuery();
        if ($page) {
            $skip = $parpage * ($page - 1);
            $userList = $search->where('status','=','0')->limit($parpage)->offset($skip)->get();
        } else {
            $userList = $search->where('status','=','0')->limit($parpage)->offset(0)->get();
        }
//        $userList=User::where('status','=',0)->paginate($parpage);
        $formatted_data = UserReportResource::collection($userList);
        return $this->sendResponse($formatted_data, 'Data retrieved successfully');

    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveUaser(Request $request, $id)
    {
        if (!empty($id)) {
            $userId = $id;
        } else {
            return $this->sendError('User Id is required ', FResponse::HTTP_BAD_REQUEST);
        }
        $userInfo = $this->userRepository->findOneByProperties(['id' => $userId]);
        if (!$userInfo) {
            throw (new ModelNotFoundException)->setModel(get_class($this->userRepository->getModel()), $userId);
        }
        $userInfo->status = 1;
        if ($userInfo->save()) {
            return $this->sendSuccess($userInfo, 'User successfully Approved', [], FResponse::HTTP_OK);
        } else {
            return $this->sendError('Something went wrong please try again later', FResponse::HTTP_NOT_MODIFIED);
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function subscription(Request $request)
    {
        return $this->subscriptionService->subscriptionReport($request->all());
    }


    /**
     * @param $queryData
     * @return array
     */
    protected function pagination($queryData)
    {
        $data = [
            'total_items' => $queryData->total(),
            'current_items' => $queryData->count(),
            'first_item' => $queryData->firstItem(),
            'last_item' => $queryData->lastItem(),
            'current_page' => $queryData->currentPage(),
            'last_page' => $queryData->lastPage(),
            'has_more_pages' => $queryData->hasMorePages(),
        ];
        return $data;
    }


}
