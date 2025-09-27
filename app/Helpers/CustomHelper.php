<?php

use App\Models\User;
use App\Notifications\SendAdminNotification;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Image;

function getMaxDate($arr, $key = 'date')
{

    $maxDate = $arr[0][$key];
    foreach ($arr as $val) {
        if ($maxDate < $val[$key]) {
            $maxDate = $val[$key];
        }
    }

    return $maxDate;
}


function getMinDate($arr, $key = 'date')
{

    $minDate = $arr[0][$key];
    foreach ($arr as $val) {
        if ($minDate > $val[$key]) {
            $minDate = $val[$key];
        }
    }

    return $minDate;
}

function differenceInMinutes($first, $second)
{
    $first = Carbon::createFromDate($first);
    $second = Carbon::createFromDate($second);

    return $first->diff($second)->format('%H:%I');
}

function getCurrentTimeDate()
{
    return Carbon::now();
}
function sendNotificationToAdmin($title, $body, $data)
{
    $admin = User::where('type', 'admin')->first();
    $admin->notify(new SendAdminNotification($title, $body, $data));
}

function sendNotificationToUser($title, $body, $data, $user)
{
    $user->notify(new SendAdminNotification($title, $body, $data));
}

function storeImage($request, $fileName, $directory, $model)
{
    try {
        $existingImage = $model->image;
        if ($existingImage) {
            \Storage::delete($existingImage->path);
            $existingImage->delete();
        }
        $path = $request->file($fileName)->store($directory);
        return $model->image()->create([
            'path' => $path,
        ]);
    } catch (\Exception $e) {
        // Log the error
        \Log::error('Error storing image: ' . $e->getMessage());
        // Optionally, throw the exception or return false to indicate failure
        throw $e;
    }
}

function deleteImage($image)
{
    $existingImage = $image;
    if ($existingImage) {
        \Storage::delete($existingImage->path);
        $existingImage->delete();
    }
    return true;
}

function storeArrayOfImage($request, $fileName, $directory, $model)
{
    try {
        $images = [];

        // Loop through each image in the request
        foreach ($request->file($fileName) as $image) {
            // Store each image and get its path
            $path = $image->store($directory);

            // Create an image model and associate it with the provided model
            $imageModel = $model->images()->create([
                'path' => $path,
            ]);

            // Add the created image model to the array
            $images[] = $imageModel;
        }

        // Return the array of created image models
        return $images;
    } catch (\Exception $e) {
        // Log the error
        \Log::error('Error storing image: ' . $e->getMessage());
        // Optionally, throw the exception or return false to indicate failure
        throw $e;
    }
}

function getCurrentDateString()
{
    $ds = Carbon::now();
    return $ds->toDateString();
}

function checkParticipantAction($guard, $action)
{
    $statuses = [
        'admin' => [
            'REQUEST_DECLINE_STATUS' => 'decline',
            'INVITE_STATUS' => 'invite',
            'ACTIVE_STATUS' => 'accept'
        ],
        'organization' => [
            'REQUEST_DECLINE_STATUS' => 'decline',
            'INVITE_STATUS' => 'invite',
            'ACTIVE_STATUS' => 'accept'
        ],
        'volunteer' => [
            'REQUEST_STATUS' => 'request',
            'INVITATION_DECLINE_STATUS' => 'decline',
            'ACTIVE_STATUS' => 'accept',
            'REQUEST_DECLINE_STATUS' => 'cancel',
        ]
    ];

    return isset($statuses[$guard]) ? (array_search($action, $statuses[$guard]) ? true : false) : false;

}

/**
 * Return the active user type,
 * e.g (admin, volunteer, organization)
 *
 * @return String|null
 */
function activeGuardType()
{
    $activeGuard = ['admin', 'volunteer', 'volunteer_app', 'organization', 'organization_app'];

    foreach ($activeGuard as $guard) {

        if (auth()->guard($guard)->check())
            return !startsWith($guard, 'admin') ?
                (!startsWith($guard, 'volunteer') ?
                    (!startsWith($guard, 'organiz') ? null : 'organization')
                    : 'volunteer')
                : 'admin';

    }
    return null;
}

/**
 * Checks for the all guards, give authenticated guard name
 *
 * @return String|null
 */
function activeGuardName()
{

    foreach (array_keys(config('auth.guards')) as $guard) {

        if (auth()->guard($guard)->check())
            return $guard;

    }
    return null;
}

function startsWith($string, $startString)
{
    $len = strlen($startString);
    return (substr($string, 0, $len) === $startString);
}

function saveFile(Illuminate\Http\UploadedFile $file, string $subFolder, string $disk = 'local')
{
    $extension = $file->getClientOriginalExtension();
    $fileName = time() . rand(1000, 9999) . '.' . $extension;
    $fileSize = $file->getSize();
    $fileType = $file->getMimeType();

    $sanitizedName = str_replace($extension, '', Illuminate\Support\Str::slug($file->getClientOriginalName()));

    if ($disk == "s3") {
        $path = $subFolder . '/' . $fileName . '.' . $extension;
        \Illuminate\Support\Facades\Storage::disk('s3')->put($path, file_get_contents($file));
    } else {
        $file->move(public_path($subFolder), $fileName);
        $path = "{$subFolder}/{$fileName}";
    }


    return [
        "fileName" => $fileName,
        "fileSize" => $fileSize,
        "fileType" => $fileType,
        "path" => $path,
        "disk" => $disk
    ];
}

function saveFiles(array $files, string $subFolder)
{
    $savedFiles = [];

    foreach ($files as $file) {
        $extension = $file->getClientOriginalExtension();
        $sanitizedName = str_replace($extension, '', Illuminate\Support\Str::slug($file->getClientOriginalName()));
        $fileName = md5(time() . $file->getClientOriginalName());
        $fileName .= $fileName . "." . $extension;
        $file->move(public_path($subFolder), $fileName);
        $savedFiles[] = [
            "name" => $sanitizedName,
            "extension" => $extension,
            "path" => "{$subFolder}/{$fileName}",
        ];
    }

    return $savedFiles;
}

function deleteFile(string $path, $disk = "local")
{
    if ($disk == "s3") {
        return \Illuminate\Support\Facades\Storage::disk('s3')->delete($path);
    }

    return Illuminate\Support\Facades\File::delete(public_path($path));
}

function deleteFiles(array $files)
{
    foreach ($files as $file) {
        Illuminate\Support\Facades\File::delete(public_path($file));
    }
}

function slugInverse($slug)
{
    return ucfirst(str_replace('-', ' ', $slug));
}

function nonNumericSlugInverse($slug)
{
    $title = slugInverse($slug);
    $tilteChunks = explode(" ", $title);
    $lastChunk = (int) end($tilteChunks);

    if ($lastChunk) {
        $lastKey = array_key_last($tilteChunks);
        unset($tilteChunks[$lastKey]);
        $title = implode(' ', $tilteChunks);
    }
    return $title;
}

function public_asset($path = null)
{
    return Illuminate\Support\Facades\URL::to('public/' . $path);
}

function comingFridayDate($timezone = 'UTC')
{
    $currentDay = Carbon::now()->setTimezone($timezone)->format('N');
    return ($currentDay == Carbon::FRIDAY) ? now()->setTimezone($timezone)->format('Y-m-d') : Carbon::now()->next(Carbon::FRIDAY)->setTimezone($timezone)->format('Y-m-d');
}

function currentWeekMondayDate($timezone = 'UTC')
{
    return Carbon::now()->startOfWeek(Carbon::MONDAY)->setTimezone($timezone)->format('Y-m-d');
}

function commingSundayDate($timezone = 'UTC')
{
    $currentDay = Carbon::now()->setTimezone($timezone)->format('N');
    return ($currentDay == Carbon::SUNDAY) ? now()->setTimezone($timezone)->format('Y-m-d') : Carbon::now()->next(Carbon::SUNDAY)->setTimezone($timezone)->format('Y-m-d');
}

function nextWeekMondayDate($timezone = 'UTC')
{
    return Carbon::now()->next(Carbon::MONDAY)->setTimezone($timezone)->format('Y-m-d');
}

function nextWeekFridayDate($timezone = 'UTC')
{
    return Carbon::now()->next(Carbon::MONDAY)->addDays(4)->setTimezone($timezone)->format('Y-m-d');
}

function currentMonthFirstDate($timezone = 'UTC')
{
    return now()->firstOfMonth()->setTimezone($timezone)->format('Y-m-d');
}

function currentMonthLastDate($timezone = 'UTC')
{
    return now()->lastOfMonth()->setTimezone($timezone)->format('Y-m-d');
}

function currentYearFirstDate($timezone = 'UTC')
{
    return now()->startOfYear()->setTimezone($timezone)->toDateString();
}

function nextMonthFirstDate($timezone = 'UTC')
{
    return now()->addMonth()->firstOfMonth()->toDateString();
}

function nextYearFirstDate($timezone = 'UTC')
{
    return now()->addYear()->startOfYear()->setTimezone($timezone)->toDateString();
}


function formatSeconds($seconds)
{
    $hours = floor($seconds / 3600);
    $mins = floor($seconds / 60 % 60);
    return ($hours < 10 ? "0{$hours}" : $hours) . ':' . ($mins < 10 ? "0{$mins}" : $mins);
}

function generateCarbonDateByDatepickerRange($range)
{
    $range = explode(' - ', $range);
    return CarbonPeriod::create($range[0], $range[1]);
}

function wrapAnchorTag($string)
{
    return preg_replace('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', '<a href="${0}" target="_blank">${0}</a>', $string);
}

function getTimeZoneAndRegion($timezone)
{
    return ($timezone) ? $timezone->zone . ' ' . ($timezone->region) : config('app.timezone') . ' ' . config('app.timezone_region');
}

function navActive($string)
{
    return request()->is($string) ? 'active' : '';
}

function mergeCollections(...$params)
{
    $collectionBag = collect([]);

    foreach ($params as $param) {
        $rawParam = is_array($param) ? $param : [$param];
        $collection = $rawParam instanceof \Illuminate\Support\Collection ? $rawParam : collect($rawParam);
        $collectionBag = $collectionBag->merge($collection);
    }

    return $collectionBag;
}

function extractEmailName($email)
{
    $chunks = explode('@', $email);
    return $chunks[0] ?? null;
}

function getNameInitials($firstName, $lastName = null)
{
    $firstInitial = $firstName[0] ?? null;

    $firstNameChunks = explode(' ', $firstName);

    $lastNameExists = count($firstNameChunks) > 1 ? true : false;

    $secondInitial = isset($lastName[0]) ? $lastName[0] : ($lastNameExists ? end($firstNameChunks)[0] : null);

    return Str::upper($firstInitial . $secondInitial);
}

function getBrowser($userAgent)
{
    $u_agent = $userAgent;
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version = "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    } elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }

    // Next get the name of the useragent yes seperately and for good reason
    if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    } elseif (preg_match('/Firefox/i', $u_agent)) {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    } elseif (preg_match('/OPR/i', $u_agent)) {
        $bname = 'Opera';
        $ub = "Opera";
    } elseif (preg_match('/Chrome/i', $u_agent) && !preg_match('/Edge/i', $u_agent)) {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    } elseif (preg_match('/Safari/i', $u_agent) && !preg_match('/Edge/i', $u_agent)) {
        $bname = 'Apple Safari';
        $ub = "Safari";
    } elseif (preg_match('/Netscape/i', $u_agent)) {
        $bname = 'Netscape';
        $ub = "Netscape";
    } elseif (preg_match('/Edge/i', $u_agent)) {
        $bname = 'Edge';
        $ub = "Edge";
    } elseif (preg_match('/Trident/i', $u_agent)) {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    }

    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
            $version = $matches['version'][0];
        } else {
            $version = $matches['version'][1];
        }
    } else {
        $version = $matches['version'][0];
    }

    // check if we have a number
    if ($version == null || $version == "") {
        $version = "?";
    }

    return array(
        'userAgent' => $u_agent,
        'name' => $bname,
        'version' => $version,
        'platform' => $platform,
        'pattern' => $pattern
    );
}

function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidDate($date, $format = "Y-m-d")
{
    try {
        Carbon::createFromFormat($format, $date);
        return true;
    } catch (\Throwable $ex) {
        return false;
    }
}

