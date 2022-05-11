<x-layout>
    <x-slot name="progressbar">
        <div class="progress-bar">
            <div class="bar" style="width: 80%"></div>
        </div>
    </x-slot>
    <x-slot name="titleContent">
        <h6>New Question · Step 4 of 4</h6>
        <h2><em>Choose the date and time of your appointment</em></h2>
    </x-slot>
    <form id="dateTimePicker" class="do-check" method="post">
        @csrf
        <div class="wrap">
            <div class="split-col">
                <div class="col">
                    @if(! empty($message))
                        <div class="error-message" style="display: flex">
                            <p>{{ $message }}</p>
                        </div>
                    @endif
                    <section class="calendar">

                        <div class="switcher">
                            <div class="days">
                                <i>{{ substr($selectedDateShort, 6) }}</i> {{ substr($selectedDateShort, 0, 6) }}
                                <a href="/question/dateTime?selectedDate={{ $firstDateOfPreviousMonth->format("Y-m-d") }}" class="navigate prev">chevron_left</a>
                                <a href="/question/dateTime?selectedDate={{ $firstDateOfNextMonth->format("Y-m-d") }}" class="navigate next">chevron_right</a>
                            </div>
                            <div class="months">
                                {{ $translatedMonth }} <i>{{ $year }}</i>
                                <a href="/question/dateTime?selectedDate={{ $firstDateOfPreviousMonth->format("Y-m-d") }}" class="navigate prev">chevron_left</a>
                                <a href="/question/dateTime?selectedDate={{ $firstDateOfNextMonth->format("Y-m-d") }}" class="navigate next">chevron_right</a>
                            </div>

                            <a href="/question/dateTime?selectedDate={{ $todayDate }}" class="today">
                                Vandaag
                            </a>
                        </div>

                        <div class="selector">
                            <ul class="days-of-week">
                                <li>Mo</li>
                                <li>Tu</li>
                                <li>We</li>
                                <li>Th</li>
                                <li>Fr</li>
                                <li>Sa</li>
                                <li>Su</li>
                            </ul>
                            <ul class="days">
                                @if($firstDateOfMonth->format('N') != 1)
                                    @for ($i = 1; $i <= $firstDateOfMonth->format('N')-1; $i++)
                                        <li></li>
                                    @endfor
                                @endif
                                @for ($i = 1; $i <= $lastDateInMonth; $i++)
                                    <li class="@if( $selectedDate->format('Y-m-d') == "$year-$month-$i" or $selectedDate->format('Y-m-d') == "$year-$month-0$i"){{'active'}}@endif @if(! empty($dates["$year-$month-$i"]) or ! empty($dates["$year-$month-0$i"])){{'full'}}@endif">
                                        <a href="/question/dateTime?selectedDate={{ $year }}-{{ $month }}-@if($i<10){{0}}@endif{{$i}}">{{ $i }}</a>
                                    </li>
                                @endfor
                            </ul>
                        </div>

                    </section>
                </div>
                <div class="col">
                    <ul class="details-with-slots" id="timeSelection">
                        @foreach($dates[$selectedDate->format("Y-m-d")] as $timeblock)
                            @if(is_array($timeblock))
                                <li class="time-slot @if($timeblock['active']==true)active @endif">
                                    <input type="radio" name="timeslot" id="timeslot_{{ $timeblock['time'] }}" value="{{ $timeblock['time'] }}" @if($timeblock['available']==false) disabled @endif/>
                                    <label for="timeslot_{{ $timeblock['time'] }}">{{ $timeblock['time'] }}</label>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                    @if( empty($dates[$selectedDate->format('Y-m-d')]))
                        <div class="empty-list">
                            <img src="{{$assetUrl}}/empty.svg" alt="empty" class="art" />
                            <h6> There are no more available time slots on this day. Click on another day in the agenda</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <section id="main-footer" class="new-question">
            <div class="wrap">
                <div class="buttons-group">
                    <a href="/question/cancel" class="button alt">Cancel</a>
                    <a href="/question/appointmentType" class="button alt">Back</a>
                </div>

                <button id="next" class="button disabled">Next one</button>
            </div>
        </section>
    </form>
</x-layout>
<script>
    jQuery(document).ready(function(){
        jQuery('.card.person').on('click', function(ev) {
            if (ev.target === this) {
                jQuery('.card.person').each(function(){
                    if(jQuery(this).find('.content').is(jQuery(ev.target).find('.content')) === false) {
                        jQuery(this).find('.content').slideUp();
                        jQuery(this).find('select').val(0);
                        jQuery(this).find('select').prop('disabled', true);
                        jQuery(this).find('.header > i').html('expand_more');
                    }
                });
                jQuery(ev.target).find('.content select').prop('disabled', false);
                jQuery(ev.target).find('.content').slideToggle(200);
                $('#next').addClass('disabled');

                const expanderIcon = jQuery(this).find('.header > i, .details > i');
                if (expanderIcon.html() === 'expand_more') {
                    expanderIcon.html('expand_less');
                } else {
                    expanderIcon.html('expand_more');
                }
            }
        });

        if(jQuery('.details-with-slots .time-slot input:checked')) {
            $('#next').toggleClass('disabled');
        }

        jQuery('.card.person').find('select').on('change', function(ev){
            if($(ev.target).val() != 0) {
                $('#next').removeClass('disabled');
            } else {console.log($(ev.target).val());
                $('#next').addClass('disabled');
            }
        });
    });
</script>
