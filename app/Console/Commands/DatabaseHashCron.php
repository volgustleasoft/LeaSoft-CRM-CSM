<?php

namespace App\Console\Commands;

use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DatabaseHashCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DbHash:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hashing question that was created 90 days ago and more';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $result = true;
        date_default_timezone_set('UTC');

        $questionsForHash = $this->getQuestionThatNeedHashed();

        if(empty($questionsForHash)){
            Log::info("There are no questions that need be hashed");
            exit;
        }

        foreach ($questionsForHash as $question) {
            $options = ['cost' => 12];
            $question->Description = password_hash($question->Description, PASSWORD_BCRYPT, $options);
            $question->Report = password_hash($question->tReport, PASSWORD_BCRYPT, $options);
            $question->IsHashed = true;
            $question->save();
            postSlackMessage("Hashed question *" . $question->Id . "*", ":card_file_box:");

            if($result) {
                Log::info("Question hashed successfully.");
            } else {
                Log::info("Question hashing has NOT been completed successfully.");
            }
        }
    }

    /**
     * Get Appointments that need VideoCall URL
     * @return array
     */
    public function getQuestionThatNeedHashed(){
        return Question::query()
            ->where('Question.IsHashed', '=', false)
            ->where('Question.State', '=', 'completed')
            ->where('Question.DateTimeCreated', '<', Carbon::now()->subDays(90))
            ->get();
    }
}
