<?php

namespace App\Services;

use App\Contracts\Services\AdminDashboardContract;
use App\Helpers\Helper;
use App\Traits\GenerateGraphReport;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminDashboardService implements AdminDashboardContract
{
    use GenerateGraphReport;

    /**
     * Get Dashborad State Count Data
     * @param Request $request
     * @return array
     */
    public function getDashboardStateDataCountReport(Request $request)
    {
        try {

            $selectArray = [
                'total_project', 'total_user', 'total_notification'
            ];

            $selectRawQueryString = "SUM(" . $selectArray[0] . ") as " . $selectArray[0] . ",
                 SUM(" . $selectArray[1] . ") as " . $selectArray[1] . ",
                 SUM(" . $selectArray[2] . ") as " . $selectArray[2];

            $where = [];
            return Helper::RETURN_SUCCESS_FORMAT(Response::HTTP_OK, 'Count report data found',);

        } catch (Exception $e) {
            Log::error('Error in getUserDataGraphReport', [$e->getMessage(), $e->getLine()]);

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_UNPROCESSABLE_ENTITY, 'Count report data not found');
        }
    }
}
