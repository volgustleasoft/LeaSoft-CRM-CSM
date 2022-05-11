<x-layout>
    <x-slot name="progressbar">
        <div class="progress-bar">
            <div class="bar" style="width: 60%"></div>
        </div>
    </x-slot>
    <x-slot name="titleContent">
        <h6>New Question · Step 3 of 4</h6>
        <h2><em>What kind of appointment would you like to make to answer your question?</em></h2>
    </x-slot>
    <form id="appointmentSelection" class="do-check" method="post">
        @csrf
        <div class="wrap">
            @if(! empty($error))
                <div class="error-message" style="display: block; color:red; margin:0 0 20px">{{ $error }}</div>
            @endif
            <ul>
                <li class="appointment-type">
                    <input type="radio" name="AppointmentType" id="AppointmentType2" value="call" @if($currentTypeValue=='call') checked @endif />

                    <label class="card appointment" for="AppointmentType2">
                        <div class="details">
                            <div class="preview icons call"></div>

                            <div class="details-info">
                                <p class="title">Call Appointment</p>
                                <p>Often available in 24 hours</p>
                            </div>
                        </div>

                        <div class="content">
                            <h5>Who can handle your request for help?</h5>
                            <p>Select at least one group of people:</p>

                            <ul class="details-with-options">
                                @foreach( $availablePersonGroups as $availablePersonGroup)
                                    <li class="checkbox">
                                        <input type="checkbox" name="availablePersonGroup[]" id="checks_call_{{ $availablePersonGroup['Name'] }}" value="{{ $availablePersonGroup['Id'] }}" @if($availablePersonGroup['Name']=='ClientCaregiverTeam') onchange='checkC2C("call")' @endif @if( $currentTypeValue=='call' and in_array($availablePersonGroup['Id'], $currentGroupValue)) checked @elseif($currentTypeValue!='call' and $availablePersonGroup['Name']=='CareGiverTeam') checked @endif />

                                        <label for="checks_call_{{ $availablePersonGroup['Name'] }}">
                                            @if($availablePersonGroup['Name'] =='CareGiverTeam')
                                                Caregiver
                                            @elseif($availablePersonGroup['Name'] =='PersonalCareGivers')
                                                Personal Caregiver
                                            @endif
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </label>
                </li>

                <li class="appointment-type">
                    <input type="radio" name="AppointmentType" id="AppointmentType33" value="visit" @if($currentTypeValue =='visit') checked @endif  />

                    <label class="card appointment" for="AppointmentType33">
                        <div class="details">
                            <div class="preview icons visit"></div>

                            <div class="details-info">
                                <p class="title">Home visit</p>
                                <p>Often available in 36 hours</p>
                            </div>
                        </div>

                        <div class="content">
                            <h5>Who can handle your request for help?</h5>
                            <p>Select at least one group of people:</p>

                            <ul class="details-with-options">
                                @foreach( $availablePersonGroups as $availablePersonGroup)
                                    <li class="checkbox">
                                        <input type="checkbox" name="availablePersonGroup[]" id="checks_visit_{{ $availablePersonGroup['Name'] }}" value="{{ $availablePersonGroup['Id'] }}" @if($availablePersonGroup['Name']=='ClientCaregiverTeam') onchange='checkC2C("visit")' @endif @if( $currentTypeValue=='visit' and in_array($availablePersonGroup['Id'], $currentGroupValue)) checked @elseif($currentTypeValue!='visit' and $availablePersonGroup['Name']=='CareGiverTeam') checked @endif />

                                        <label for="checks_visit_{{ $availablePersonGroup['Name'] }}">
                                            @if($availablePersonGroup['Name'] =='CareGiverTeam')
                                                Caregiver
                                            @elseif($availablePersonGroup['Name'] =='PersonalCareGivers')
                                               Personal Caregiver
                                            @endif
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </label>
                </li>
            </ul>
        </div>
        <section id="main-footer" class="new-question">
            <div class="wrap">
                <div class="buttons-group">
                    <a href="/question/cancel" class="button alt">Cancel</a>
                    <a href="/question/description" class="button alt">Back</a>
                </div>
                <button id="next" onclick="beforeSubmit(event)" class="button disabled">Next one</button>
            </div>
        </section>
    </form>
</x-layout>
<script>
    function checkC2C(type){
        if(type=='visit'){
            var checkbox = document.getElementById('checks_visit_ClientCaregiverTeam');
        }else if(type=='call'){
            var checkbox = document.getElementById('checks_call_ClientCaregiverTeam');
        }

        if(checkbox.checked){
            setTimeout(function(){
                alert("Note that with this choice, the client can see your address and telephone number after accepting the question, to help you with your question.\n\nIf you only select the co-client as network, it is possible to receive a notification 24 hours in advance that the appointment cannot take place.");            },200);
        }
    }

    function beforeSubmit(ev) {
        ev.preventDefault();

        jQuery('.card.appointment').find('.content').each(function(index){
            if(jQuery(this).is(":hidden")) {
                jQuery(this).find('input[name="availablePersonGroup[]"]').each(function( index ) {
                    jQuery(this).prop('checked', false);
                });
            }
        });

        jQuery('#appointmentSelection').submit();
    }
</script>
