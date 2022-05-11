<x-layout>
    <x-slot name="app_care_portal"></x-slot>
    <div class="wrap">
        <x-slot name="title">
            Directors of {{ $client['Firstname'] }} {{ $client['Lastname'] }}
        </x-slot>
    </div>
    <div class="wrap">
        <div id="wrap">
            <section class="directory">
                @foreach($personalCareGivers as $careGiver)
                    <div class="person card" type="client">
                        <div class="details">
                            <div class="preview">{{ substr($careGiver->Firstname, 0, 1)}}{{substr($careGiver->Lastname , 0, 1) }}</div>
                            <div class="details-info">
                                <p class="title">{{ $careGiver->Firstname }} {{ $careGiver->Lastname }}</p>
                                <p><a href @click="deleteCaregiver($event, {{$careGiver->ClientPersonTeam}})">Remove</a></p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </section>
        </div>

        @if($personalCareGivers->isEmpty())
            <empty-state :asset-url="'{{$assetUrl}}'">
                This client has no directors yet
            </empty-state>
        @endif

        <div id="wrap">
            <div class="caregiver-form">
                <h2>Add caregiver</h2>
                <div class="input-group">
                    <label>Caregiver</label>
                    <div class="select-wrapper">
                        <div class="select-wrapper">
                            <select id="careGiverId">
                                <option></option>
                                @foreach($availableCareGivers as $careGiver)
                                    <option
                                        value="{{ $careGiver->Id }}">{{ $careGiver->Firstname }} {{ $careGiver->Lastname }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="buttons-group">
                    <button @click="addCaregiver($event)" class="button">Add</button>
                </div>
            </div>
        </div>
    </div>
</x-layout>

<script>
    window.addEventListener("load", function (event) {
        new Vue({
            delimiters: ['${', '}'],
            el: '#app',
            data() {
                return {
                    WithoutHeader: false
                }
            },
            mounted() {
                window.jQuery('#app').prop('style', '');
            },
            methods: {
                deleteCaregiver(eventClick, caregiverId) {
                    window.jQuery(eventClick.target).addClass('disabled');
                    axios.post('/personalCaregiver/removeCG', {
                        params: {
                            id: caregiverId,
                        }
                    })
                        .then((response) => {
                            if (response.data.success) {
                                window.location.reload()
                            }
                        })
                },
                addCaregiver(eventClick) {
                    let caregiverSelector = document.getElementById("careGiverId");
                    let caregiverId = caregiverSelector.value;
                    if (caregiverId !== ""){

                        window.jQuery(eventClick.target).addClass('disabled')
                        const queryString = window.location.search;
                        const urlParams = new URLSearchParams(queryString);
                        const clientId = urlParams.get('Id')
                        axios.post('/personalCaregiver/addCG', {
                            params: {
                                caregiverId: caregiverId,
                                clientId: clientId

                            }
                        })
                        .then((response) => {
                            if(response.data.success) {
                                window.location.reload()
                            }
                        })
                    }
                },
            }
        })
    });
</script>

