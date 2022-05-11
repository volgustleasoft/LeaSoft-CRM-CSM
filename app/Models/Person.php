<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Person extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'Person';
    protected $primaryKey = 'Id';
    protected $fillable = ['IsClientCareGiver'];
    public $timestamps = false;

    public function AvailablePersonalCareGivers()
    {
        $careGiversInTeam = $this->ClientTeam->CareGroupPersons;

        $careGiversThatCanBePersonalCareGivers = array();
        foreach ($careGiversInTeam as $careGroupPerson) {
            $canAdd = true;
            foreach ($this->PersonalCareGiverCareGroup->CareGroupPersons as $p) {
                if ($p->PersonId == $careGroupPerson->PersonId || $careGroupPerson->PersonId == $this->Id) {
                    $canAdd = false;
                }
            }

            if ($canAdd) {
                array_push($careGiversThatCanBePersonalCareGivers, $careGroupPerson->person);
            }
        }
        return collect($careGiversThatCanBePersonalCareGivers);
    }

    public function PersonalCareGiverCareGroup()
    {
        return $this->hasOne(CareGroup::class, "Id", "PersonalCareGroupId");
    }

    public function ClientTeam()
    {
        return $this->hasOne(CareGroup::class, "Id", "TeamCareGroupId");
    }

    public function CareGroupsCaregiver($PersonalAndRegularCaregiver = false)
    {
        return $this->belongsToMany(CareGroup::class, 'CareGroupPerson', 'PersonId', 'CareGroupId')
            ->whereIn('CareGroupLabelId', $PersonalAndRegularCaregiver ? [1, 2] : [1]);
    }

    public function getContactRequests()
    {
        return $this->hasMany(ContactRequest::class, 'PersonId', 'Id');
    }

    public function getContactLogs()
    {
        return $this->hasMany(ContactLog::class, 'PersonId', 'Id');
    }

    public function IsPersonalClient($Caregiver)
    {
        return CareGroupPerson::
        where('CareGroupId', $this->PersonalCareGroupId)
            ->where('PersonId', $Caregiver->Id)
            ->exists();
    }

    public function getClientsForPerson($caregiverTeams)
    {
        return $this
            ->whereIn("TeamCareGroupId", $caregiverTeams)
            ->where('IsClient', true)
            ->where("IsActive", true)
            ->get()
            ->keyBy('Firstname');
    }

    public function getAppointments($args)
    {
        $query = Appointment::query()
            ->select('Question.*', 'Appointment.*')
            ->join('Question', 'Question.Id', '=', 'Appointment.QuestionId')
            ->join('Person', 'Person.Id', '=', 'Question.PersonId')
            ->when(!empty($args['cross']), function ($query) use ($args) {
                $query->where('Appointment.DateTimeAppointmentFrom', '<', $args['cross']['dateTo'])
                    ->where('Appointment.DateTimeAppointmentTo', '>', $args['cross']['dateFrom']);
            })
            ->when(!empty($args['datetimeMinimum']), function ($query) use ($args) {
                $query->where('Appointment.DateTimeAppointmentFrom', '>', $args['datetimeMinimum']);
            })
            ->when(!empty($args['datetimeMaximum']), function ($query) use ($args) {
                $query->where('Appointment.DateTimeAppointmentTo', '<', $args['datetimeMaximum']);
            })
            ->when(!empty($args['AcceptedByPersonId']), function ($query) use ($args) {
                $query->where('Appointment.AcceptedByPersonId', '=', $args['AcceptedByPersonId']);
            })
            ->when(!empty($args['teamIds']), function ($query) use ($args) {
                $query->whereIn('Person.TeamCareGroupId', $args['teamIds']);
            });
        if (!empty($args['teamId']) && !empty($args['appointmentsType'])) {
            $query->when(!empty($args['teamId']), function ($query) use ($args) {
                $query->where('Person.TeamCareGroupId', '=', $args['teamId']);
            });
        }
        $query->when(!empty($args['appointmentsType']), function ($query) use ($args) {
            $query->whereIn('Question.AppointmentType', $args['appointmentsType']);
        })
            ->whereNotNull('AcceptedByPersonId')
            ->whereIn('Appointment.State', !empty($args['State']) ? $args['State'] : ['new', 'completed']);

        return $query->orderBy('Appointment.DateTimeAppointmentFrom', 'asc')->get()->all();
    }

    public function getAppointmentsForDate($date, $args = [])
    {
        return $this->getAppointments([
            "datetimeMinimum" => $date . " 00:00:00",
            "datetimeMaximum" => $date . " 23:59:59",
            "AcceptedByPersonId" => $args['AcceptedByPersonId'] ?? null,
            "State" => ['completed', 'created', 'failedNoShow', 'failedNoOther'],
            "teamId" => $args['teamId'] ?? null,
            "appointmentsType" => ['call', 'visit']
        ]);
    }

    public function getAppointmentsForMonth($date, $args = [])
    {
        return $this->getAppointments([
            "datetimeMinimum" => date('Y-m-01 00:00:00', strtotime($date)),
            "datetimeMaximum" => date('Y-m-t 23:59:59', strtotime($date)),
            "AcceptedByPersonId" => $args['AcceptedByPersonId'] ?? null,
            "State" => ['completed', 'created', 'failedNoShow', 'failedNoOther'],
            "teamId" => $args['teamId'] ?? null,
            "appointmentsType" => ['call', 'visit']
        ]);
    }

    public function getAppointmentsForCaregiver($args = [])
    {
        return $this->getAppointments([
            "datetimeMaximum" => Carbon::now(),
            "AcceptedByPersonId" => $args['AcceptedByPersonId'] ?? null,
            "teamIds" => $args['teamIds'] ?? null,
            "State" => ['created'],
            "appointmentsType" => ['call', 'visit']
        ]);
    }

    public function getQuestions($args, $request = false)
    {
        $query = Question::query()
            ->select('Appointment.*', 'Question.*', 'Appointment.Id AS AppointmentId')
            ->join('Appointment', 'Question.Id', '=', 'Appointment.QuestionId')
            ->whereNotIn('Appointment.State', ['canceledByCaregiver'])
            ->when(!empty($args['AppointmentType']), function ($query) use ($args) {
                return $query->whereIn('Question.AppointmentType', $args['AppointmentType']);
            })
            ->when(!empty($args['States']), function ($query) use ($args) {
                return $query->whereIn('Question.State', $args['States']);
            });

        if (!empty($request)) {
            $query->when($request->has('date') and $request->date == 'pending', function ($query) {
                return $query->whereNull('Appointment.AcceptedByPersonId');
            });
        }

        $query->where('Question.PersonId', $this->Id);

        if (isset($args['NotPastQuestion'])) {
            $query->when($args['NotPastQuestion'], function ($query) {
                return $query->where('Appointment.DateTimeAppointmentTo', '>', Carbon::now()->toDateTimeString());
            });
        }

        return $query->get();
    }

    public function getExistsAuthCode($code)
    {
        return AuthenticationCode::query()
            ->where('PersonId', '=', $this->Id)
            ->where('DateTimeCreated', '>', date("Y-m-d H:i:s", (time() - 60 * intval(getenv("authentication_code_expiration_time_in_minutes")))))
            ->where('IsUsed', '=', 0)
            ->where('Code', '=', $code)
            ->where('FailedAttemptsCount', '<', 3)
            ->get()->first();
    }

    public function addContactLog($contactType, $reference, $dateTime = null)
    {
        $log = ContactLog::create([
            'ContactType' => $contactType,
            'PersonId' => $this->Id,
            'Reference' => $reference,
        ]);

        if (!empty($dateTime)) {
            $log->update(['DateTime' => $dateTime]);
        }
    }

    public function addFailedAttemptToOpenAuthenticationCodes()
    {
        $codes = AuthenticationCode::query()
            ->where('PersonId', '=', $this->Id)
            ->where('DateTimeCreated', '>', date("Y-m-d H:i:s", (time() - 60 * intval(getenv("authentication_code_expiration_time_in_minutes")))))
            ->where('IsUsed', '=', 0)
            ->where('FailedAttemptsCount', '<', 3)
            ->get()->all();

        foreach ($codes as $code) {
            $code->update(["FailedAttemptsCount" => $code->FailedAttemptsCount + 1]);
        }
    }

    public function getUrgentQuestionNotification($questionId)
    {
        return UrgentQuestionNotification::query()
            ->where('PersonId', '=', $this->Id)
            ->where('QuestionId', '=', $questionId)
            ->get()->first();
    }
}
