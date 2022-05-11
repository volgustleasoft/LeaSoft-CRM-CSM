<x-layout>
    <x-slot name="progressbar">
        <div class="progress-bar">
            <div class="bar" style="width: @if($isDCCall) 66% @else 40% @endif;"></div>
        </div>
    </x-slot>
    <x-slot name="titleContent">
        <h6>New Question · Step 2 of 4</h6>
        <h2><em>Describe your question</em></h2>
    </x-slot>
    <form method="post">
        @csrf
        <div class="wrap">
            <div class="input-group">
                <label>Jouw uitleg</label>
                <textarea placeholder="" aria-label="description" id="userDescription" name="Description">{{ $currentValue }}</textarea>
                <span class="notice" id="errormessage" style="display: none;"></span>
                @if(! empty($error))
                    <span class="error-message" style="display: inline;">{{ $error }}</span>
                @endif
            </div>
        </div>
        <section id="main-footer" class="new-question">
            <div class="wrap">
                <div class="buttons-group">
                    <a href="/question/cancel" class="button alt">Cancel</a>
                    <a href="/question/category/edit" class="button alt">Back</a>
                </div>

                <button id="next" class="button @if(empty($currentValue)) disabled @endif">Next one</button>
            </div>
        </section>
    </form>
</x-layout>
