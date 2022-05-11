<div class="input-group time-select">
    <label>{{$title}}</label>
    <div class="select-group">
        <div class="select-wrapper">
            @if($attributes['type'] == 'start')
                <select name="hourStart" class="timeInterval" @if(! $isAvailable()) disabled="disabled" @endif>
                    @foreach (range(6, 23) as $hourStart)
                        <option {{ $hourStart == $getParsedStart()['hours'] ? 'selected' : '' }} value="{{ date('H', strtotime($hourStart . ':00')) }}">
                            {{ date('H', strtotime($hourStart . ':00')) }}
                        </option>
                    @endforeach
                </select>
            @else
                <select name="hourEnd" class="timeInterval" @if(! $isAvailable()) disabled="disabled" @endif>
                    @foreach (range(6, 23) as $hourEnd)
                        <option {{ $hourEnd == $getParsedEnd()['hours'] ? 'selected' : '' }} value="{{ date('H', strtotime($hourEnd . ':00')) }}">
                            {{ date('H', strtotime($hourEnd . ':00')) }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>
        <div class="select-wrapper">
            @if($attributes['type'] == 'start')
                <select name="minuteStart" class="timeInterval" @if(! $isAvailable()) disabled="disabled" @endif>
                    @foreach (range(0, 3) as $minuteStart)
                        <option
                            value="{{ date('i', strtotime('00:' . $minuteStart*15)) }}"
                            {{ $minuteStart*15 == $getParsedStart()['minutes'] ? 'selected' : '' }}
                        >
                            {{ date('i', strtotime('00:' . $minuteStart*15)) }}
                        </option>
                    @endforeach
                </select>
            @else
                <select name="minuteEnd" class="timeInterval" @if(! $isAvailable()) disabled="disabled" @endif>
                    @foreach (range(0, 3) as $minuteEnd)
                        <option
                            value="{{ date('i', strtotime('00:' . $minuteEnd*15)) }}"
                            {{ $minuteEnd*15 == $getParsedEnd()['minutes'] ? 'selected' : '' }}
                        >
                            {{ date('i', strtotime('00:' . $minuteEnd*15)) }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>
</div>
