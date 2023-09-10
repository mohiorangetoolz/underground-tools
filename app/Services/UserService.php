<?php

namespace App\Services;

use App\Contracts\Repositories\ResetPasswordRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Services\UserContract;
use App\Helpers\Helper;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\DataTables;

class UserService implements UserContract
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUserDataTable(Request $request)
    {
        $response = $this->getAllUser($request);

        return Datatables::of($response)
            ->addColumn('user_info', function ($response) {
                $userID = encrypt($response->id);

                $email = $response->email;
                $status = '<span class="v-chip v-theme--light text-success v-chip--density-default v-chip--size-small v-chip--variant-outlined"
                                                draggable="false"><!----><span class="v-chip__underlay"></span>Active</span>';
                if ($response->status == 0) {
                    $status = '<span class="v-chip v-theme--light text-danger v-chip--density-default v-chip--size-small v-chip--variant-outlined"
                                                draggable="false"><!----><span class="v-chip__underlay"></span>Inactive</span>';
                }

                $type = 'Admin';
                if ($response->type == 1) {
                    $type = "Registration";
                }

                $phone = isset($response->phone) ? $response->phone : "Phone Number Not Found";
                $name = isset($response->first_name) ? $response->first_name . " " : "No Name ";
                $name .= isset($response->last_name) ? $response->last_name : "";
                $profileImage = asset('images/portrait/small/avatar-s-11.jpg"');
                if (!empty($response->avatar)) {
                    $profileImage = $response->avatar;
                }

                return '<div class="card user-card mb-0">
                            <div class="card-body p-1">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-12 d-flex flex-column justify-content-between border-container-lg">
                                        <div class="user-avatar-section">
                                            <div class="d-flex justify-content-start">
                                                <img class="img-fluid rounded" src=" ' . $profileImage . ' " height="60" width="60" alt="User avatar">
                                                <div class="d-flex flex-column ml-1">
                                                    <div class="user-info mb-1">
                                                        <h4 class="mb-0"> ' . $name . ' </h4>
                                                        <span class="card-text"> ' . $email . ' </span>
                                                    </div>
                                                    <div class="d-flex flex-wrap">
                                                        <a href="javascript:void(0)" class="btn btn-sm btn-primary waves-effect waves-float waves-light
                                                        edit-user" id="edit-user" data-id = "' . $userID . '">Edit</a>
                                                        <button href="javascript:void(0)" class="btn  btn-sm btn-outline-danger ml-1 waves-effect" data-id = "' . $userID . '"  id="delete-user">Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center user-total-numbers">
                                            <div class="d-flex align-items-center mr-2">
                                                <div class="color-box bg-light-primary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign text-primary"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-12 mt-2 mt-xl-0">
                                        <div class="user-info-wrapper">

                                            <div class="d-flex flex-wrap my-50">
                                                <div class="user-info-title">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check mr-1"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                    <span class="card-text user-info-title font-weight-bold mb-0">Status</span>
                                                </div>
                                                <p class="card-text mb-0"> ' . $status . ' </p>
                                            </div>

                                             <div class="d-flex flex-wrap my-50">
                                                <div class="user-info-title">
                                                   <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" tag="i" class="v-icon notranslate v-theme--light v-icon--size-default iconify iconify--tabler mr-1" width="1em" height="1em" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M3 19h18"></path><rect width="14" height="10" x="5" y="6" rx="1"></rect></g></svg>
                                                   <span class="card-text user-info-title font-weight-bold mb-0">Create by</span>
                                                </div>
                                                <p class="card-text mb-0"> ' . $type . ' </p>
                                            </div>

                                            <div class="d-flex flex-wrap">
                                                <div class="user-info-title">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-phone mr-1"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                                    <span class="card-text user-info-title font-weight-bold mb-0">Contact</span>
                                                </div>
                                                <p class="card-text mb-0">' . $phone . '</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            })
            ->addColumn('action', function ($response) {
                $userID = encrypt($response->id);

                $status = '<a href="javascript:void(0)" class="btn btn-sm dropdown-item" id="active-user" data-id = "' . $userID . '">
                                    <div class="avatar bg-light-primary">
                                         <i class="fa fa-solid fa-check-circle" class="avatar-icon"></i>
                                    </div>
                                    Active</a>';
                if ($response->status == 1) {// active user constant value
                    $status = '<a href="javascript:void(0)" class="btn btn-sm dropdown-item" id="deactive-user" data-id = "' . $userID . '">
                                    <div class="avatar bg-light-primary">
                                         <i class="fa fa-ban" class="avatar-icon"></i>
                                    </div>
                                    Deactivate</a>';
                }

                $action = '<div class="btn-group">
                            <a href="" class="btn btn-sm     " data-toggle="dropdown">
                                <span class="">
                                    <i class="fa fa-ellipsis-v" class="avatar-icon"></i>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">

                                <a href="javascript:void(0)" class="btn btn-sm dropdown-item edit-user" id="edit-user" data-id = "' . $userID . '">
                                    <div class="avatar bg-light-primary">
                                        <i class="fa fa-edit"></i>
                                    </div>
                                    Edit
                                </a>
                                ' . $status . '
                                <a href="javascript:void(0)" class="btn btn-sm dropdown-item" id="change-user-password" data-id = "' . $userID . '">

                                    <i class="fa fa-solid fa-lock-open p-0" class="avatar-icon"></i>
                                    Reset password</a>
                                <a href="javascript:void(0)" class="btn btn-sm dropdown-item" id="delete-user" data-id = "' . $userID . '">
                                    <div class="avatar bg-light-primary">
                                        <i class="fa fa-user-minus" class="avatar-icon"></i>
                                    </div>
                                    Delete</a>
                            </div>
                        </div>';

                return $action;
            })
            ->rawColumns(['action', 'user_info'])
            ->make(true);
    }

    public function getAllUser(Request $request)
    {
        try {
            return $this->userRepository->getAllUser(['id', 'email', 'first_name', 'last_name', 'phone', 'type', 'status']);
        } catch (Exception  $e) {
            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_EXPECTATION_FAILED, 'Failed to get user data', []);
        }
    }

    /**
     * Create New user
     * @param Request $request
     * @return array
     */
    public function registrationUser(Request $request): array
    {
        try {

            if (!isset($request['password']) && empty($request['password'])) {
                $request['password'] = Helper::generateRandomString(8, $pass = true);
            }

            $userId = $this->createUser(
                $request['first_name'],
                $request['email'],
                $request['type'],
                $request['last_name'],
                $request['password'],
                $request['phone'],
            );

            if ($userId) {
                //todo // Mail send for user
                $name = $request['first_name'];
                $htmlData['password'] = $request['password'];
                $htmlData['name'] = $name;


                $to = $request['email'];
                $subject = 'Welcome To OLK9 push notification ';
                $htmlTemplate = view('mail.registration')->with($htmlData)->render();

                $mail = new MailService();
                $mail->sendMailViaSendGrid($subject, $to, $name, $htmlTemplate);

                return Helper::RETURN_SUCCESS_FORMAT(Response::HTTP_CREATED, 'User created successfully and check your mail address for password');
            }

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'User registration failed');

        } catch (Exception $e) {
            Log::error('User registration failed', [$e->getMessage(), $e->getLine()]);

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Independent method for insert data in user table
     * @param $firstName
     * @param $email
     * @param $type
     * @param $last_name
     * @param $password
     * @param $phone
     * @return mixed
     */
    public function createUser(
        $firstName,
        $email,
        $type,
        $last_name = null,
        $password = null,
        $phone = null
    )
    {
        try {
            $data = [
                'first_name' => !is_null($firstName) ? $firstName : "",
                'last_name' => !is_null($last_name) ? $last_name : null,
                'email' => !is_null($email) ? $email : "",
                'phone' => !is_null($phone) ? $phone : null,
                'type' => ($type == USER_TYPE__DEFAULT) ? USER_TYPE__DEFAULT : USER_TYPE__REGISTRATION,
                'password' => Hash::make($password)
            ];

            $user = $this->userRepository->createRegisterUser($data);
            return $user->id;

        } catch (Exception $e) {
            Log::error('User registration failed', [$e->getMessage(), $e->getLine()]);
            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Delete user from user table
     * @param Request $request
     * @return array
     */
    public function deleteUser(Request $request): array
    {
        try {
            $userId = decrypt($request['user_id']);

            $response = $this->userRepository->deleteUser(['id' => $userId]);

            if ($response) {
                return Helper::RETURN_SUCCESS_FORMAT(Response::HTTP_OK, 'User Deleted Successfully', $response);
            }

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'User Deleted Failed');

        } catch (Exception $e) {
            Log::error("Delete User : ", [$e->getMessage()]);
            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'User Deleted Failed');
        }
    }

    /**
     * Update user status
     * @param Request $request
     * @return array
     */
    public function statusUpdate(Request $request): array
    {
        try {
            $userId = decrypt($request->user_id);
            $response = $this->userRepository->statusUpdate(
                ['id' => $userId],
                ['status' => $request->status]
            );

            if ($response) {
                return Helper::RETURN_SUCCESS_FORMAT(Response::HTTP_OK, 'User Updated Successfully', $response);
            }

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'User Updated Failed');

        } catch (Exception $e) {
            Log::error("Update User Status : ", [$e->getMessage()]);
            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'User Updated Failed .');
        }
    }

    public function getUserCount(Request $request)
    {
        return App::make(UserRepository::class)->getUserCount($request['from_date'], $request['to_date']);
    }

    /**
     * Get User Information for a specific user
     * @param $userId
     * @return array
     */
    public function getUserInformationForSpecificUser(int $userId): array
    {
        try {

            $responseData = $this->getSpecificDataByCondition($userId);

            return Helper::RETURN_SUCCESS_FORMAT(Response::HTTP_OK, 'User data found', $responseData);

        } catch (Exception  $e) {
            Log::info('error in getUserInformationForSpecificUser', [$e->getMessage(), $e->getLine()]);
            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_EXPECTATION_FAILED, 'Failed to get user data', []);
        }
    }

    public function getSpecificDataByCondition(int $userId)
    {
        $where = ['id' => $userId];
        $select = ['id', 'email', 'first_name', 'last_name', 'phone', 'type', 'status', 'avatar'];

        return $this->userRepository->getSpecificDataByCondition($select, $where);
    }

    /**
     * User Profile Information Data Update
     * @param Request $request
     * @return array
     */
    public function updateProfileInformationData(Request $request): array
    {
        try {
            $user = $this->updateProfileDataUpdate($request);

            if ($user) {
                return Helper::RETURN_SUCCESS_FORMAT(Response::HTTP_OK, 'User profile data successfully updated');
            }

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'User profile data update failed');
        } catch (Exception $e) {
            Log::error('User profile data update failed', [$e->getMessage()]);

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Update Profile Data Insert Function
     * @param Request $request
     * @return mixed
     */
    private function updateProfileDataUpdate(Request $request): mixed
    {
        $userId = ($request['user_id']);

        $data = [
            'first_name' => !is_null($request->first_name) ? $request->first_name : "",
            'last_name' => !is_null($request->last_name) ? $request->last_name : "",
            'phone' => !is_null($request->phone) ? $request->phone : ""
        ];
        $where = [
            'id' => $userId
        ];

        return $this->userRepository->updateUserDataByCondition($where, $data);
    }

    public function resetUserPassword(Request $request): array
    {
        try {
            $user = $this->updateUserPassword($request);

            if ($user) {
                //todo // Mail send for user
                $name = $request['first_name'];
                $htmlData['password'] = $request['password'];
                $htmlData['name'] = $name;


                $to = $request['email'];
                $subject = 'User Password Change ';
                $htmlTemplate = view('mail.registration')->with($htmlData)->render();

                $mail = new MailService();
                $mail->sendMailViaSendGrid($subject, $to, $name, $htmlTemplate);

                return Helper::RETURN_SUCCESS_FORMAT(Response::HTTP_OK, 'Password successfully updated');
            }

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'Password update failed');
        } catch (Exception $e) {
            Log::error('User profile data update failed', [$e->getMessage(), $e->getLine()]);

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_EXPECTATION_FAILED);
        }
    }

    private function updateUserPassword(Request $request): mixed
    {
        try {
            $userId = ($request['user_id']);

            $data = [
                'password' => !is_null($request->new_password) ? Hash::make($request->new_password) : "",
            ];

            $where = [
                'id' => $userId
            ];

            return $this->userRepository->updateUserDataByCondition($where, $data);

        } catch (Exception $exception) {
            Log::error('Update user password failed', [$exception->getMessage(), $exception->getLine()]);

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_EXPECTATION_FAILED);
        }
    }


    public function getUserList()
    {
        return $this->userRepository->getUserList();
    }

    /**
     * Image upload form
     * @param Request $request
     * @return array
     */
    public function imageUploadForm(Request $request): array
    {

        if ($request->file('profile_image')) {

            $file = $request->file('profile_image');

            $fileName = Helper::getCustomFileName($file);

            $response = $this->profileImageUpload($request->file('profile_image'), $fileName);

            $this->imageUpdate($request->user_id, $response['data']['url']);

            return Helper::RETURN_SUCCESS_FORMAT(\Illuminate\Http\Response::HTTP_OK, 'Profile image update successfully');
        }

        return Helper::RETURN_ERROR_FORMAT(Response::HTTP_EXPECTATION_FAILED, 'Failed to upload image', []);
    }

    /**
     * Image Upload To S3 Server
     * @param $file
     * @param $fileName
     * @return array
     */
    public function profileImageUpload($file, $fileName): array
    {
        $s3Service = new S3ServiceAWS();

        $response = $s3Service->setCustomUrl('upload/user/profile/')->uploadFileToS3($file, $fileName);
        return Helper::RETURN_SUCCESS_FORMAT(Response::HTTP_OK, 'Profile image successfully upload', $response);
    }

    /**
     * Update admin profile image
     * @param $title
     * @param $imageUrl
     * @return mixed
     */
    public function imageUpdate($userId, $imageUrl)
    {
        $where = ['id' => $userId];
        $data = ['avatar' => $imageUrl];

        return $this->userRepository->updateUserDataByCondition($where, $data);
    }

    public function forgotPasswordEmailSend(Request $request): array
    {
        try {
            // email check
            $user = App::make(UserRepository::class)->getUserInformationByUserEmail($request['email'], ['id', 'email']);

            if (!isset($user)) {
                return Helper::RETURN_ERROR_FORMAT(Response::HTTP_UNPROCESSABLE_ENTITY, 'No associated Account found');
            }

            if ($user) {
                $resetLink = env('APP_URL') . '/reset-password/' . encrypt($user->email);
                // code generate
                $token = Helper::generateNumber(6);
                // insert data in db and date will be utc
                $this->insertResetPassword($user, $token);
                // Mail send with token
                $this->sendMail($user, $token, $resetLink);

                return Helper::RETURN_SUCCESS_FORMAT(Response::HTTP_OK, 'Please check you email');
            }

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'Password reset failed');

        } catch (Exception $e) {
            Log::error('User forgot password Failed: ', [$e->getMessage(), $e->getLine()]);

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_EXPECTATION_FAILED, 'Password reset failed');
        }
    }

    private function insertResetPassword($user, $token)
    {
        App::make(ResetPasswordRepository::class)->insertResetPasswordRequest([
            'email' => $user->email,
            'token' => $token,
            'type' => FORGET_PASSWORD_TYPE_USER,
            'expire_time' => Carbon::now()->addMinutes(10),
            'is_used' => FORGET_PASSWORD_NOT_USED
        ]);
    }

    private function sendMail($user, $token, $resetLink)
    {
        $name = $user->first_name;
        $htmlData['password'] = $token;
        $htmlData['name'] = $name;
        $htmlData['resetLink'] = $resetLink;

        $to = $user->email;
        $subject = 'Forgot password';
        $htmlTemplate = view('mail.forgotPassword')->with($htmlData)->render();

        $mail = new MailService();
        $mail->sendMailViaSendGrid($subject, $to, $name, $htmlTemplate);
    }

    public function forgotPasswordCodeCheck(Request $request)
    {
        try {
            // code and email exist check
            $passwordReset = App::make(ResetPasswordRepository::class)->getDataByTokenAndEmail($request->token_code, $request->email);

            if (is_null($passwordReset)) {
                return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'Invalid request. Please request again !!');
            }

            if ($passwordReset->type != FORGET_PASSWORD_TYPE_ADMIN) {
                return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'Unauthorized request. Please request again !!');
            }

            if (!($passwordReset->expire_time >= Carbon::now())) {
                return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'Token Expired. Please request for new token code');
            }

            if (($passwordReset->is_used) == FORGET_PASSWORD_USED) {

                return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'Your verification code is already used');
            }

            if ($passwordReset->is_used == FORGET_PASSWORD_NOT_USED) {

                $user = App::make(UserRepository::class)->getUserInformationByUserEmail($request['email'], ['id', 'email']);

                $data = [
                    'is_used' => FORGET_PASSWORD_USED
                ];

                App::make(ResetPasswordRepository::class)->updateResetPasswordById($passwordReset->id, $data);

                $request['user_id'] = $user->id;
                $this->updateUserPassword($request);

                return Helper::RETURN_SUCCESS_FORMAT(Response::HTTP_OK, 'You have successfully verified code', [
                    'id' => $passwordReset->id,
                    'email' => $request['email'],
                ]);

            }
        } catch (Exception $exception) {
            Log::error('forgot Password Code Check Failed: ', [$exception->getMessage(), $exception->getLine()]);

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_EXPECTATION_FAILED, 'Forgot password code check failed');
        }
    }

}
