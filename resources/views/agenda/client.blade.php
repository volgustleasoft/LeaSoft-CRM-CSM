<x-layout>
    <x-slot name="app_care_portal"></x-slot>
    <x-slot name="appheader">
        <app-header
            :filter-choices="currentFilterChoices"
            :filter-type="['tags']"
            @datefilter="(filterChoice) => { redirect(filterChoice)}"
        >Client Agenda
        </app-header>
    </x-slot>
    <div class="wrap">
        <alert :message="message"></alert>
    </div>
    <div class="wrap">
        @foreach($appointments as $appointment)
            <div class="card{{!empty($appointment->cardClass) ? $appointment->cardClass : ''}}">
                <div class="header">
                    <span
                        class="date">{{  strftime('%a, %d/%m/%G', strtotime($appointment->DateTimeAppointmentFrom)) }}</span>
                    <span>
                                 @convertTime($appointment->DateTimeAppointmentFrom)
                                @if($appointment->DateTimeAppointmentTo) -
                        @convertTime($appointment->DateTimeAppointmentTo)@endif
                            </span>

                    @if ($appointment->AcceptedByPersonId == $PersonId)
                        @if ($appointment->shouldReport)
                            <span class="tag red">
                                        Pending report
                                    </span>
                        @else
                            <span class="tag green">
                                    Offer help
                                </span>
                        @endif
                    @endif
                    @endif

                    <i>expand_more</i>
                </div>
                <div class="details">
                    <div class="preview icons {{ $appointment->AppointmentType }}"></div>

                    <div class="details-info">
                        <p class="title">
                            @if (!empty($appointment->AcceptedByPerson) && $appointment->AcceptedByPerson->IsClientCareGiver == 1)
                                {{ $appointment->askedByPerson->Firstname }} {{ $appointment->askedByPerson->Lastname }}
                            @elseif ($appointment->AcceptedByPerson)
                                {{ $appointment->AcceptedByPerson->Firstname }} {{ $appointment->AcceptedByPerson->Lastname }}
                            @else
                                Looking for someone who can help...
                            @endif
                        </p>
                        <p>
                            @if ($appointment->AppointmentType == 'call')
                                Appointment
                            @elseif ($appointment->AppointmentType == 'visit')
                                Home visit
                            @endif
                        </p>
                    </div>
                </div>

                <div class="content">
                    <ul class="details-with-labels no-border">
                        <li>
                            <div class="label">Netwerk</div>
                            <div>
                                @if (!empty($appointment->AcceptedByPerson) && $appointment->AcceptedByPerson->role == 'caregiver')
                                    Caregiver
                                @else
                                    {{ $appointment->selectedRoles }}
                                @endif
                            </div>
                        </li>
                        <li>
                            <div class="label">Categories</div>
                            <div>{{ $appointment->Categories }}</div>
                        </li>
                        <li>
                            <div class="label">Question</div>
                            <div class="with-wrap">{{ $appointment->Description }}</div>
                        </li>

                        @if (!empty($appointment->AcceptedByPerson) && $appointment->AcceptedByPerson)
                            <li>
                                <div class="label">Telephone number</div>
                                <div>{{ $appointment->AcceptedByPerson->PhoneNumber }}</div>
                            </li>
                        @endif

                        @if ($appointment->askedByPerson && $appointment->AppointmentType == 'visit')
                            <li>
                                <div class="label">Adress</div>
                                <div>
                                    {{ $appointment->askedByPerson->AddressStreet }}
                                    {{ $appointment->askedByPerson->AddressNumber }},
                                    {{ $appointment->askedByPerson->AddressZipcode }}
                                    {{ $appointment->askedByPerson->AddressCity }}</div>
                            </li>
                        @endif

                        @if ($appointment->shouldReport != true)
                            @if ($appointment->AcceptedByPerson || ( $appointment->AcceptedByPerson == null && $appointment->askedByPerson == null ))
                                <li>
                                    <a @click="cancelQuestion($event, {{$appointment->QuestionId}}, {{$appointment->askedByPerson->Id}}, {{$appointment->AppointmentId}})"
                                       class="button alt cancel">Cancel appointment</a>
                                </li>
                            @else
                                <li>
                                    <a @click="cancelQuestion($event, {{$appointment->Id}}, {{$appointment->askedByPerson->Id}}, {{$appointment->AppointmentId}})"
                                       class="button alt cancel">Cancel appointment </a>
                                </li>
                            @endif
                        @endif
                    </ul>

                </div>

            </div>
            @endif
        @endforeach

        @if(count($appointments) ==0)
            <empty-state
                :asset-url="'{{$assetUrl}}'"
                :button=true
                button-name="Nieuwe vraag"
                link="/question/category"
            >
                You don't have any scheduled appointments at the moment
            </empty-state>
        @endif
    </div>
</x-layout>
<script>
    const CurrentPage = '{{ $currentFilterChoice }}';
    const message = JSON.parse('{!! $message !!}');
    const currentFilterChoices = [{value: 'active', label: 'Planned', selected: false}, {
        value: 'pending',
        label: 'Pending',
        selected: false
    }];
</script>
<script>
    window.onload = function () {
        setTimeout(function () {
            const messageCards = document.querySelectorAll('.card.message.success');

            for (let index = 0; index < messageCards.length; index++) {
                messageCards[index].style.display = 'none';
            }
        }, 3000);
    };
</script>
<script>
    window.addEventListener("DOMContentLoaded", (event) => {
        new Vue({
            delimiters: ['${', '}'],
            el: '#app',
            data() {
                CurrentPage === 'pending' ? currentFilterChoices[1].selected = true : currentFilterChoices[0].selected = true;
                return {
                    message: {},
                    currentFilterChoices
                }
            },
            mounted() {
                window.jQuery('#app').prop('style', '');

                window.jQuery('#app .card:not(.appointment, .person)').click(function (event) {
                    window.toggleCard(event, this);
                });

                if (Object.keys(message).length > 0) {
                    this.message = message
                }
            },
            methods: {
                cancelQuestion(eventClick, questionId, personId, appointmentId) {
                    if (confirm('Are you sure you want to cancel this appointment?')) {
                        window.jQuery(eventClick.target).addClass('disabled');
                        axios.post('/agenda/client/cancelQuestion', {
                            questionId: questionId,
                            personId: personId,
                            appointmentId: appointmentId
                        })
                            .then((response) => {
                                if (response.data.success) {
                                    window.location.href = `${window.location.origin + window.location.pathname + window.location.search}`;
                                }
                            })
                    }
                }
            }
        });
    });
</script>
