<div class="select-group">
    <div class="select-wrapper">
        <select name="day" @if(! $isAvailable()) disabled="disabled" @endif>
            @for ($i = 1; $i <= 31; $i++)
                <option {{ $i == $getParsedStart()['day'] ? 'selected' : '' }} value="{{$i}}" >{{ $i }}</option>
            @endfor
        </select>
    </div>
    <div class="select-wrapper">
        <select name="month" @if(! $isAvailable()) disabled="disabled" @endif>
            @foreach (range(1, 12) as $j)
                <option  {{ $j == $getParsedStart()['month'] ? 'selected' : '' }} value="{{ $j }}">
                    {{ $getMonthLabelByNumber($j) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="select-wrapper">
        <select name="year" @if(! $isAvailable()) disabled="disabled" @endif>
            @php($year = date('Y'))
            <option {{ $year == $getParsedStart()['year'] ? 'selected' : '' }}>{{ $year }}</option>
            <option {{ intVal($year) + 1 == $getParsedStart()['year'] ? 'selected' : '' }}>{{ intVal($year)+1 }}</option>
        </select>
    </div>
</div>
