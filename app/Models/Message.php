<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\StatisticsController;

class Message extends Model
{
    use HasFactory;

    protected $table = 'Message';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['MessageTemplateId', 'PersonId', 'Variables', 'Medium'];

    public function MessageTemplate()
    {
        return $this->hasOne(MessageTemplate::class, "Id", "MessageTemplateId");
    }

    public function Person()
    {
        return $this->hasOne(Person::class, "Id", "PersonId");
    }

    public function send() {
        $message = MessageTemplate::find($this->MessageTemplateId)->SmsText;
        $variables = json_decode($this->Variables,true);

        foreach($variables as $key=>$value){
            $message = str_replace("%".$key."%", $value, $message);
        }

        $person = Person::find($this->PersonId);

        $json_data = json_encode(array("phonenumber"=>$person->PhoneNumber,"message"=>$message));

        $post = file_get_contents(getenv("SMS_ENDPOINT"),null,stream_context_create(array(
            'http' => array(
                'protocol_version' => 1.1,
                'user_agent'       => 'portal_care',
                'method'           => 'POST',
                'header'           => "Content-type: application/json\r\n".
                    "Connection: close\r\n" .
                    "Content-length: " . strlen($json_data) . "\r\n",
                'content'          => $json_data,
            ),
        )));

        app('App\Http\Controllers\StatisticsController')->log("message_sms", $this->Person, ["SubEvent"=>$this->MessageTemplate->Name]);


    }
}
