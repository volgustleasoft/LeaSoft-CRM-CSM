<x-layout>
    <x-slot name="progressbar">
        <div class="progress-bar">
            <div class="bar" style="width: @if($isDCCall) 33% @else 20% @endif;"></div>
        </div>
    </x-slot>
    <x-slot name="titleContent">
        <h6>New Question · Step 1 of 4</h6>
        <h2><em>What is your question about?</em></h2>
    </x-slot>
    <form id="categorySelection" class="do-check" method="post" action="/question/category">
        @csrf
        <div class="wrap" id="category-card">
            @foreach($categories as $category)
                <div class="card category">
                    <div class="details">
                        <div class="preview">{!! $category['Icon'] !!}</div>

                        <div class="details-info">
                            <p class="title">{{ $category['Name'] }}</p>
                        </div>

                        <i>expand_more</i>
                    </div>
                    <ul class="content">
                        @foreach($category['Childs'] as $subcategory)
                        <li class="sub-category">
                            <input type="radio" name="QuestionCategoryId" id="cat_{{ $subcategory['Id'] }}" value="{{ $subcategory['Id'] }}" @if($currentValue == $subcategory['Id']) checked @endif />
                            <label for="cat_{{ $subcategory['Id'] }}">{{ $subcategory['Name'] }}</label>
                        </li>
                        @endforeach

                        <li class="sub-category">
                            <input type="radio" name="QuestionCategoryId" id="cat_{{ $category['Id'] }}" value="{{ $category['Id'] }}" @if($currentValue == $category['Id']) checked @endif />
                            <label for="cat_{{ $category['Id'] }}">Other</label>
                        </li>
                    </ul>
                </div>
            @endforeach
        </div>
        <section id="main-footer" class="new-question">
            <div class="wrap">
                <div class="buttons-group">
                    <a href="/question/cancel" class="button alt">Cancel</a>
                </div>
                <div class="selected-category">
                    <p class="title">Selected category:</p>
                    <p class="info">
                        <span id="category" style="display: none;">Categories : </span>
                        <span id="sub-category">Geen</span>
                    </p>
                </div>
                <button id="next" class="button disabled">Next one</button>
            </div>
        </section>
    </form>
</x-layout>
