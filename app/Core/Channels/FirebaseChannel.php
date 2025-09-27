<?php

namespace App\Core\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

/**
 *
 */
class FirebaseChannel
{
	public function send($notifiable, Notification $notification){

            $payload = $notification->toFirebase($notifiable);
            $fields = $this->prepareFields($notifiable,$payload);

            if($fields) {
                $response = Http::withHeaders([
                    'Authorization' => 'key=AAAAZ4_4TQQ:APA91bG-WmzWvKGTgcCzaT7ouutURbW6BAtl9aUb395hp0LbrB_m8N47jb9wVoAjIx05eXgYKJriby_dBhHwehbBjaCJpaCSnAxhc8FZZ8SgiTsfJ_xK3dK9uHjfrKxox-Um3iyNvRRc',
                    'Content-Type' => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send',$fields);
                return $response->json();
            }
	}

	private function prepareFields($notifiable,$payload){

        if($notifiable['device_token']) {
            
            $fields = [
                "priority" => "high",
                "notification" => [
                    "title"=> $payload['title'],
                    "body"=> $payload['body']
                ],
                'content_available' => true,
                "data"=>[
                    "type" => $payload['data'] ? $payload['data']['type'] : 'general',
                    "id" => $payload['data'] ? $payload['data']['content_id'] : 0     
                ],
                'to' => $notifiable['device_token'],
            ];
            return $fields;
        } else {
            return false;
        }


	}
}
?>
