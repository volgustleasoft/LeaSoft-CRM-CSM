<x-layout>
    <x-slot name="progressbar">
        <div class="progress-bar">
            <div class="bar" style="width: 100%"></div>
        </div>
    </x-slot>
    <x-slot name="titleContent">
        <h6> New Question Overview</h6>
        <h2><em>Check if all information is correct</em></h2>
    </x-slot>
    <form id="dateTimePicker" class="do-check" method="post">
        @csrf
        <div class="wrap" id="summary">
            <h5 class="section">Question</h5>
            <ul class="details-with-labels with-edit">
                <li>
                    <div class="label">Categories</div>
                    <div>{{ $category }}: {{ $subcategory }}</div>
                    <a href="/question/category/edit">edit</a>
                </li>
                <li>
                    <div class="label">Question</div>
                    <div class="with-wrap">{{ $description }}</div>
                    <a href="/question/description">edit</a>
                </li>
            </ul>
            <h5 class="section">Appointment</h5>
            <ul class="details-with-labels with-edit">
                <li>
                    <div class="label">Type</div>
                    <div>@if($type=='call')Appointment @elseif($type=='visit')Home visit @endif</div>
                </li>
                <li>
                    <div class="label">Date</div>
                    <div>{{ $datePretty }}</div>
                        <a href="/question/dateTime?selectedDate={{$datetime->format("Y-m-d")}}">edit</a>
                </li>
                <li>
                    <div class="label">Time</div>
                        <div>{{ $datetime->format("H:i") }} - {{ $datetimeEnd->format("H:i") }}</div>
                        <a href="/question/dateTime?selectedDate={{$datetime->format("Y-m-d")}}">edit</a>
                </li>
            </ul>
        </div>
        <section id="main-footer" class="new-question">
            <div class="wrap">
                <div class="buttons-group">
                    <a href="/question/cancel" class="button alt">Cancel</a>
                        <a href="/question/dateTime?selectedDate={{$datetime->format('Y-m-d')}}" class="button alt">Back</a>
                </div>

                <button onclick="this.disabled=true;this.form.submit();" id="next" class="button">Confirm & add</button>
            </div>
        </section>
    </form>
</x-layout>
