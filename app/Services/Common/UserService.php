<?php


namespace App\Services\Common;

use App\Http\Resources\Admin\UserListingResource;
use App\Models\DeletedUserFeedback;
use App\Services\FirebaseService;
use App\Services\StripeService;
use Carbon\Carbon;
use Exception;
use Hash;
use DB;
use App\Models\User;
use HelperConstants;
use App\Models\PasswordReset;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use App\Notifications\{SignupActivation, PasswordResetRequest};
use App\Notifications\SendAdminNotification;


class UserService extends CrudService
{

    // use UploadAble;


    public function create(object $request, $chatService)
    {
        DB::beginTransaction();
        $collection = collect($request->all());
        $stripeService = new StripeService();
        $customer = $stripeService->createCustomer($request->first_name . " " . $request->last_name, $request->email);
        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->stripe_customer_id = $customer->id;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->email_verified_at = Carbon::now();
        $user->phone = $request->phone;
        $user->device_id = $request->device_id;
        $user->device_token = $request->device_token;
        $user->dialing_code = $request->dialing_code;
        $user->country_code = $request->country_code;
        $image = '';
        if ($request->has('image')) {
            $saveFile = saveFile($request->image, HelperConstants::PROFILE_IMAGE_DIRECTORY, HelperConstants::UPLOAD_DISK);
            $image = $saveFile['fileName'];
            $user->image = $image;
        }
        $user->save();
        $admin = User::where('type', 'admin')->first();
        $chatList = $chatService->createChatWithRequestAccept($user->id, $admin->id);
        //Code to send notification to admin for approve this user
        // $title = $user->name . " just created a new account.";
        // $body = $user->id;
        // $data = [
        //     "user_id" => $user->id,
        //     "title" => $user->name . " just created a new account.",
        //     "body" => $user->id,
        // ];
        // $admin = User::where('type', 'admin')->first();
        // $admin->notify(new SendAdminNotification($title, $body, $data));
        $data = ['user' => new UserListingResource($user)];
        DB::commit();

        return $data;
    }

    public function update($request)
    {
        auth()->user()->name = $request->name;
        auth()->user()->dialing_code = $request->dialing_code;
        auth()->user()->phone = $request->phone;
        auth()->user()->dob = $request->dob;
        auth()->user()->country = $request->country;

        if ($request->has('image')) {
            $saveFile = saveFile($request->image, HelperConstants::PROFILE_image_DIRECTORY, HelperConstants::UPLOAD_DISK);
            auth()->user()->image = $saveFile['fileName'];
        }

        auth()->user()->save();

        return auth()->user();
    }

    public function getAll()
    {
        return User::all();
    }

    public function show($id)
    {
        return User::find($id);
    }

    public function updatePassword($request, $id)
    {
        $user = User::find($id);

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return $user;
    }

    public function updateStatus($request, $id)
    {
        $user = User::find($id);
        $user->status = $request->status;
        $user->save();

        return $user;
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        User::find($id)->delete();
        DB::commit();
        return response()->json(['status' => true, 'message' => 'deleted successfully']);
    }

    public function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function getUserByPhone($phone)
    {
        return User::where('phone', $phone)->first();
    }

    public function checkLogin($request, $type = 'user')
    {

        if ($request->exists('phone') && $request->filled('phone')) {
            $user = $this->getUserByPhone($request->phone);

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'phone' => ['The provided credentials are incorrect.'],
                ]);
            }
        } else {
            $user = $this->getUserByEmail($request->email);

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }
        }
        if ($user->type !== $type) {
            throw new AuthenticationException(
                "You're not valid user to access this route"
            );
        }
        if ($user->email_verified_at == null) {
            throw new AuthenticationException(
                "Your account is not verified"
            );
        }
        if ($user->status == 0) {
            throw new AuthenticationException(
                "Your account has been deactivated"
            );
        }
        if ($request->device_token) {
            $this->updateDeviceToken($user->email, $request->device_token);
        }

        return ['token' => $user->createToken('123', ['account:verified'])->plainTextToken, 'user' => new UserListingResource($user)];
    }

    public function deleteUser($request)
    {
        DB::beginTransaction();
        $user = $this->getUserByEmail($request->email);

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        if ($user->currentAccessToken()) {
            $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        }
        $deletedUserFeedback = new DeletedUserFeedback();
        $deletedUserFeedback->reason_type = $request->reason;
        $deletedUserFeedback->reason = $request->customReason;
        $deletedUserFeedback->user_id = $user->id;
        $deletedUserFeedback->save();
        User::find($user->id)->delete();
        DB::commit();
        return "Account deleted";
    }

    public function updateDeviceToken($email, $deviceToken)
    {
        $user = $this->getUserByEmail($email);
        $user->device_token = $deviceToken;
        $user->save();
    }

    public function updateDeviceTokenByPhone($phone, $deviceToken)
    {
        $user = $this->getUserByPhone($phone);
        $user->device_token = $deviceToken;
        $user->save();
    }

    public function reset($request)
    {
        $user = $this->getUserByEmail($request->email);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Email is not registered'],
            ]);
        }

        $passwordReset = $this->generateToken($user->email);

        if ($user && $passwordReset)
            $user->notify(
                new PasswordResetRequest($user, $passwordReset->token)
            );
        return true;

    }

    public function verifyAndUpdatePassword($request)
    {
        $user = $this->getUserByEmail($request->email);
        return $this->updatePassword($request, $user->id);
    }

    public function sendAccountVerification($user)
    {
        $verification = $this->generateToken($user->email);
        if ($user && $verification)
            $user->notify(
                new SignupActivation($user, $verification->token)
            );
    }

    public function generateToken($email)
    {
        return PasswordReset::updateOrCreate(
            ['email' => $email],
            [
                'email' => $email,
                'token' => strtolower(rand(1000, 9999)),
                'created_at' => now()
            ]
        );
    }

    public function markVerify($request, $email)
    {
        $code = $this->getToken($request);

        if (!$code || $code->email != $email) {
            return false;
        }
        $user = $this->getUserByEmail($email);

        $user->verified = 1;

        // Remove after development of admin
        $user->status = 1;

        $user->save();

        $this->deleteToken($request->token);

        return true;
    }

    public function getToken($request)
    {
        return PasswordReset::where('token', $request->token)->first();
    }

    public function deleteToken($token)
    {
        PasswordReset::where('token', $token)->delete();
    }

    public function setLocation($request, $userId)
    {
        $user = User::where('id', $userId)->first();

        if ($user->location) {

            $location['latitude'] = $request->latitude;
            $location['longitude'] = $request->longitude;
            $location['text'] = $request->location;
            $user->location()->update($location);
        } else {

            $location = new \App\Models\Location;
            $location->latitude = $request->latitude;
            $location->longitude = $request->longitude;
            $location->text = $request->location;
            $user->location()->save($location);
        }

        return $user;
    }

    public function pushNotificationStatus($userId, $status)
    {
        $user = User::find($userId);
        $user->push_notification = $status;
        $user->save();

        return $user;
    }

    public function eventRatingStatus($userId, $status)
    {
        $user = User::find($userId);
        $user->event_rating = $status;
        $user->save();

        return $user;
    }

    public function logout($request)
    {
        return $request->user()->currentAccessToken()->delete();
    }

    public function listing($request, $pagination)
    {
        return User
            ->whereNotIn('status', [User::PENDING_STATUS, User::REJECT_STATUS])
            ->when(request()->filled('status'), function ($q) {
                $q->whereStatus(request('status'));
            })
            ->when(request()->filled('search'), function ($q) {
                $q->where('name', 'like', '%' . request("search") . '%');
            })
            ->when(request()->filled('start'), function ($q) {
                $q->whereDate('created_at', '>=', request('start'))->whereDate('created_at', '<=', request('end'));
            })
            ->orderBy('id', 'DESC')
            ->paginate($pagination);
    }

    public function requestListing($request, $pagination)
    {
        return User::
            where(function ($q) {
                $q->where('status', User::PENDING_STATUS)->orWhere('status', User::REJECT_STATUS);
            })
            ->when(request()->filled('status'), function ($q) {
                $q->whereStatus(request('status'));
            })
            ->when(request()->filled('search'), function ($q) {
                $q->where('name', 'like', '%' . request("search") . '%');
            })
            ->when(request()->filled('start'), function ($q) {
                $q->whereDate('created_at', '>=', request('start'))->whereDate('created_at', '<=', request('end'));
            })
            ->orderBy('id', 'DESC')
            ->paginate($pagination);
    }

    public function accountStatus($userId, $status)
    {
        $user = User::find($userId);
        $user->status = $status;
        $user->save();

        return $user;
    }
}
